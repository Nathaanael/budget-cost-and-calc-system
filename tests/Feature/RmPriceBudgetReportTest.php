<?php

use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use App\Models\Reference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('superadmin can open the RM price budget report', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $reference = createReportReference();
    $material = createReportMaterial('RM0001', 'Wheat Flour');

    RawMaterialPrice::create([
        'raw_material_id' => $material->id,
        'period' => 'current',
        'usd_amount' => 10.50,
        'rupiah_amount' => 168000,
        'source_kind' => 'manual',
    ]);

    $this->actingAs($superadmin)
        ->get(route('admin.reporting.rm-price-budget.index', ['reference_id' => $reference->id]))
        ->assertOk()
        ->assertSee('Anggaran Harga RM')
        ->assertSee('Current')
        ->assertDontSee('Saat Ini')
        ->assertSee('Daftar Raw Material')
        ->assertSee('Unduh Excel')
        ->assertDontSee('window.print')
        ->assertSee('Wheat Flour')
        ->assertSee('RM0001');
});

test('RM price budget report filters the Raw Material code range', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    createReportReference();
    createReportMaterial('RM0001', 'First Material');
    createReportMaterial('RM0009', 'Last Material');

    $this->actingAs($superadmin)
        ->get(route('admin.reporting.rm-price-budget.index', [
            'code_from' => 'RM0009',
            'code_to' => 'RM0009',
        ]))
        ->assertOk()
        ->assertSee('Last Material')
        ->assertDontSee('First Material');
});

test('RM price budget report can be downloaded as an editable Excel file using active filters', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $reference = createReportReference();
    $included = createReportMaterial('RM0002', 'Included Material');
    createReportMaterial('RM0009', 'Excluded Material');

    RawMaterialPrice::create([
        'raw_material_id' => $included->id,
        'period' => 'current',
        'usd_amount' => 12.5,
        'rupiah_amount' => 200000,
        'source_kind' => 'manual',
    ]);

    $response = $this->actingAs($superadmin)
        ->get(route('admin.reporting.rm-price-budget.excel', [
            'reference_id' => $reference->id,
            'code_from' => 'RM0002',
            'code_to' => 'RM0002',
        ]))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->assertDownload('anggaran-harga-rm-january-2027.xlsx');

    expect(substr($response->getContent(), 0, 2))->toBe('PK');

    $temporaryPath = tempnam(sys_get_temp_dir(), 'rm-price-budget-test-');
    file_put_contents($temporaryPath, $response->getContent());

    try {
        $archive = new ZipArchive;
        expect($archive->open($temporaryPath))->toBeTrue();
        $worksheet = $archive->getFromName('xl/worksheets/sheet1.xml');
        $archive->close();

        expect($worksheet)
            ->toContain('Included Material')
            ->toContain('Current')
            ->toContain('<v>12.5</v>')
            ->not->toContain('Excluded Material');
    } finally {
        @unlink($temporaryPath);
    }
});

function createReportReference(): Reference
{
    return Reference::create([
        'code' => '00',
        'description_1' => 'Budget Division',
        'description_2' => 'National',
        'period' => '01012027',
        'period_description' => 'JANUARY 2027',
        'rate_current' => 16000,
        'rate_le' => 16100,
        'rate_1' => 16200,
        'rate_2' => 16300,
        'rate_3' => 16400,
        'rate_4' => 16500,
    ]);
}

function createReportMaterial(string $code, string $description): RawMaterial
{
    return RawMaterial::create([
        'code' => $code,
        'material_id' => $code,
        'description' => $description,
        'unit' => 'KG',
        'wastage_all' => 0,
        'currency_type' => 'USD',
        'type_rm' => 'LOCAL',
    ]);
}
