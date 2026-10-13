<?php

use App\Models\FinishedGood;
use App\Models\FinishedGoodFormula;
use App\Models\RawMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

const TEMPLATE_RM_CODES = ['219271', '301578', '301579', '301580', '208433', '205400', '226854'];
const TEMPLATE_PACKAGING_CODES = ['232256', '228864', '337447', '340559', '333580', '340212', '333581', '334043', '333092'];

function templateUpload(): UploadedFile
{
    $copy = tempnam(sys_get_temp_dir(), 'fgtpl');
    copy(base_path('docs/tools/Contoh Template.ods'), $copy);

    return new UploadedFile($copy, 'Contoh Template.ods', 'application/vnd.oasis.opendocument.spreadsheet', null, true);
}

function createTemplateMaterial(string $code, bool $packaging): RawMaterial
{
    return RawMaterial::create([
        'code' => $code,
        'material_id' => $packaging ? "MAT-{$code}" : null,
        'description' => "Material {$code}",
        'unit' => 'Kg',
        'currency_type' => 'Rp',
        'type_rm' => 'LOCAL',
    ]);
}

/** @param array<int, array<int, string|float|null>> $rows */
function xlsxUpload(array $rows): UploadedFile
{
    $sheetRows = '';
    foreach ($rows as $index => $row) {
        $cells = '';
        foreach ($row as $column => $value) {
            if ($value === null) {
                continue;
            }
            $reference = chr(65 + $column).($index + 1);
            $cells .= is_string($value)
                ? "<c r=\"{$reference}\" t=\"inlineStr\"><is><t>".htmlspecialchars($value)."</t></is></c>"
                : "<c r=\"{$reference}\"><v>{$value}</v></c>";
        }
        $sheetRows .= '<row r="'.($index + 1).'">'.$cells.'</row>';
    }

    $path = tempnam(sys_get_temp_dir(), 'fgxlsx');
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="FORMULA" sheetId="1" r:id="rId1"/></sheets></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
    $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>');
    $zip->close();

    return new UploadedFile($path, 'formula.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
}

test('formula FG excel import lists unregistered raw materials without creating them', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $finishedGood = FinishedGood::create(['code' => '232282', 'description' => 'Bumbu RGSN-HS', 'batch' => 20000, 'active' => 'Y']);

    foreach (array_slice(TEMPLATE_RM_CODES, 1) as $code) {
        createTemplateMaterial($code, false);
    }
    foreach (array_slice(TEMPLATE_PACKAGING_CODES, 1) as $code) {
        createTemplateMaterial($code, true);
    }

    $this->actingAs($superadmin)
        ->post(route('admin.maintenance.formula.fg.import', $finishedGood), ['file' => templateUpload()], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonCount(2, 'missing_raw_materials')
        ->assertJsonPath('missing_raw_materials.0.no', '1')
        ->assertJsonPath('missing_raw_materials.0.code', '219271')
        ->assertJsonPath('missing_raw_materials.0.name', 'Solid Ingredient D296030')
        ->assertJsonPath('missing_raw_materials.1.code', '232256');

    expect(RawMaterial::whereIn('code', ['219271', '232256'])->count())->toBe(0);
});

test('formula FG excel import previews template rows with calculated standard unit', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $finishedGood = FinishedGood::create(['code' => '232282', 'description' => 'Bumbu RGSN-HS', 'batch' => 20000, 'active' => 'Y']);

    foreach (TEMPLATE_RM_CODES as $code) {
        createTemplateMaterial($code, false);
    }
    foreach (TEMPLATE_PACKAGING_CODES as $code) {
        createTemplateMaterial($code, true);
    }

    $response = $this->actingAs($superadmin)
        ->post(route('admin.maintenance.formula.fg.import', $finishedGood), ['file' => templateUpload()], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonCount(16, 'data.items')
        ->assertJsonPath('data.items.0.code', '219271')
        ->assertJsonPath('data.items.0.is_packaging', false)
        ->assertJsonPath('data.items.1.code', '232256')
        ->assertJsonPath('data.items.1.is_packaging', true);

    $items = collect($response->json('data.items'))->keyBy('code');

    // =ROUND(H3/20000*1000;5) dan =ROUND(H4/20000;8)
    expect($items['219271']['standard_batch'])->toEqual(80)
        ->and($items['219271']['standard'])->toEqual(4)
        ->and($items['232256']['standard'])->toEqual(0.000015)
        ->and($items['337447']['standard'])->toEqual(0.00003335)
        ->and($items->has('60246371'))->toBeFalse()
        ->and(implode(' ', $response->json('warnings')))->toContain('60246371')
        ->and($response->json('data.notes'))->toStartWith('PRODUKSI SEMARANG  Blend. & Pack-Semarang')
        ->and($response->json('data.notes'))->toEndWith('BUMBU RGSN-HS 00  03')
        ->and(FinishedGoodFormula::count())->toBe(0);
});

test('formula FG excel import rejects a file for another finished good', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $finishedGood = FinishedGood::create(['code' => '232310', 'description' => 'Bumbu SKSJ', 'batch' => 20000, 'active' => 'Y']);

    $this->actingAs($superadmin)
        ->post(route('admin.maintenance.formula.fg.import', $finishedGood), ['file' => templateUpload()], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('file')
        ->assertJsonPath('errors.file.0', 'Kode FG pada Excel (232282) tidak sesuai dengan FG terpilih (232310). Cek kembali file Excel Anda.');
});

test('formula FG excel import reads xlsx files and rejects duplicate codes', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $finishedGood = FinishedGood::create(['code' => '765432', 'description' => 'FG Xlsx', 'batch' => 245, 'active' => 'Y']);
    createTemplateMaterial('RM1', false);
    createTemplateMaterial('PK1', true);
    $header = ['FGCODE', 'FGNAME', 'OUPUT', 'UM', 'NO', 'RMCODE', 'RMNAME', 'USG/BATCH', 'UM', 'USG/UNIT', 'ID'];

    $this->actingAs($superadmin)
        ->post(route('admin.maintenance.formula.fg.import', $finishedGood), ['file' => xlsxUpload([
            $header,
            [765432.0, 'FG Xlsx', 245.0, 'PC', 1.0, 'rm1', 'Bahan', '24,5'],
            [null, null, null, null, 2.0, 'PK1', 'Karton', 49.0],
            [null, null, null, null, null, null, 'TOTAL'],
        ])], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('data.items.0.code', 'RM1')
        ->assertJsonPath('data.items.0.standard', 100)
        ->assertJsonPath('data.items.1.standard', 0.2)
        ->assertJsonPath('warnings', []);

    $this->post(route('admin.maintenance.formula.fg.import', $finishedGood), ['file' => xlsxUpload([
        $header,
        ['765432', 'FG Xlsx', 245.0, 'PC', 1.0, 'RM1', 'Bahan', 1.0],
        [null, null, null, null, 2.0, 'RM1', 'Bahan', 2.0],
    ])], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('file');
});

function exportedRows($response): array
{
    $path = tempnam(sys_get_temp_dir(), 'fgexp');
    file_put_contents($path, $response->getContent());

    return app(\App\Support\SpreadsheetReader::class)->rows($path, 'xlsx', 'FORMULA');
}

test('formula FG excel export follows the template and can be uploaded back', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $finishedGood = FinishedGood::create(['code' => '200939', 'description' => 'Bumbu Ekspor', 'batch' => 20000, 'active' => 'Y']);
    createTemplateMaterial('219271', false);
    createTemplateMaterial('232256', true);
    createTemplateMaterial('228864', true);

    $this->actingAs($superadmin)->putJson(route('admin.maintenance.formula.fg.update', $finishedGood), [
        'rows' => [
            ['code' => '219271', 'standard_batch' => 80, 'deleted' => false],
            ['code' => '232256', 'standard_batch' => 0.3, 'deleted' => false],
            ['code' => '228864', 'standard_batch' => 20, 'deleted' => true],
        ],
        'notes' => 'PRODUKSI SEMARANG',
    ])->assertOk();

    $response = $this->get(route('admin.maintenance.formula.fg.export', ['code' => '200939']))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename="formula-fg-200939.xlsx"');
    $rows = exportedRows($response);

    expect(array_slice($rows[0], 0, 12))->toBe(['FGCODE', 'FGNAME', 'OUPUT', 'UM', 'NO', 'RMCODE', 'RMNAME', 'USG/BATCH', 'UM', 'USG/UNIT', 'ID', 'KETERANGAN'])
        ->and($rows[1][0])->toBe('200939')
        ->and($rows[1][2])->toEqual(20000)
        ->and($rows[1][5])->toBe('219271')
        ->and($rows[1][7])->toEqual(80)
        ->and($rows[1][9])->toEqual(4)
        ->and($rows[1][11])->toBe('PRODUKSI SEMARANG')
        ->and($rows[2][5])->toBe('232256')
        ->and($rows[2][9])->toEqual(0.000015)
        ->and($rows[2][10])->toBe('MAT-232256')
        // Baris soft delete tidak ikut diexport.
        ->and($rows[3][6])->toBe('TOTAL');

    $exported = tempnam(sys_get_temp_dir(), 'fgexp');
    file_put_contents($exported, $response->getContent());

    $this->post(route('admin.maintenance.formula.fg.import', $finishedGood), [
        'file' => new UploadedFile($exported, 'formula-fg-200939.xlsx', null, null, true),
    ], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonCount(2, 'data.items')
        ->assertJsonPath('data.notes', 'PRODUKSI SEMARANG')
        ->assertJsonPath('warnings', []);
});

test('formula FG excel export all contains every finished good with a formula', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $first = FinishedGood::create(['code' => '200939', 'description' => 'FG Satu', 'batch' => 10, 'active' => 'Y']);
    $second = FinishedGood::create(['code' => '200940', 'description' => 'FG Dua', 'batch' => 20, 'active' => 'Y']);
    FinishedGood::create(['code' => '200941', 'description' => 'FG Tanpa Formula', 'batch' => 30, 'active' => 'Y']);
    createTemplateMaterial('219271', false);

    foreach ([$first, $second] as $finishedGood) {
        $this->actingAs($superadmin)->putJson(route('admin.maintenance.formula.fg.update', $finishedGood), [
            'rows' => [['code' => '219271', 'standard_batch' => 5, 'deleted' => false]],
        ])->assertOk();
    }

    $rows = exportedRows($this->get(route('admin.maintenance.formula.fg.export'))->assertOk());
    $codes = collect($rows)->filter(fn ($row) => isset($row[0]) && $row[0] !== 'FGCODE')->pluck(0)->values()->all();

    expect($codes)->toBe(['200939', '200940'])
        ->and(collect($rows)->where(0, 'FGCODE')->count())->toBe(2);
});
