<?php

use App\Models\FinishedGood;
use App\Models\FinishedGoodFormula;
use App\Models\FinishedGoodFormulaItem;
use App\Models\RawMaterial;
use App\Models\RawMaterialPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function rmUcStandardFinishedGood(string $code, string $description, array $items): FinishedGood
{
    $finishedGood = FinishedGood::create(['code' => $code, 'description' => $description, 'multi_level' => 'N', 'active' => 'Y']);
    $formula = FinishedGoodFormula::create(['finished_good_id' => $finishedGood->id]);

    foreach ($items as $position => [$rawMaterial, $standard]) {
        FinishedGoodFormulaItem::create([
            'finished_good_formula_id' => $formula->id,
            'raw_material_id' => $rawMaterial->id,
            'standard' => $standard,
            'position' => $position + 1,
        ]);
    }

    return $finishedGood;
}

function rmUcStandardMaterial(string $code, ?string $materialId, float $waste, float $price): RawMaterial
{
    $material = RawMaterial::create([
        'code' => $code, 'material_id' => $materialId, 'description' => "Material {$code}", 'unit' => 'KG',
        'wastage_all' => $waste, 'currency_type' => 'Rp', 'type_rm' => 'LOCAL',
    ]);
    RawMaterialPrice::create(['raw_material_id' => $material->id, 'period' => 'current', 'rupiah_amount' => $price]);

    return $material;
}

test('RM unit cost standard report follows the legacy Report2 calculation', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    // RM tanpa ID: C = 50 + (50 * 0.02) = 51; Unit Cost = 51 * 12.000 / 1000 = 612.
    $flour = rmUcStandardMaterial('RM-FLOUR', null, 0.02, 12000);
    // Packaging dengan ID: C = 1 + (1 * 0.5) = 1,5; Unit Cost = 1,5 * 300 = 450.
    $pouch = rmUcStandardMaterial('PK-POUCH', 'MAT-POUCH', 0.5, 300);
    rmUcStandardFinishedGood('FG-0001', 'Mi Goreng', [[$flour, 50], [$pouch, 1]]);

    $this->actingAs($superadmin)
        ->get(route('admin.reporting.rm-uc-standard.index'))
        ->assertOk()
        ->assertSee('Unit Cost Standar Raw Material')
        ->assertSee('Mi Goreng')
        ->assertSee('51,000000')
        ->assertSee('612,00')
        ->assertSee('1,500000')
        ->assertSee('450,00')
        ->assertSeeInOrder(['Unit Cost per Pack', '1.062,00']);
});

test('RM unit cost standard Excel has one colorless table per FG in the selected range with live formulas', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $flour = rmUcStandardMaterial('RM-FLOUR', null, 0.02, 12000);
    $pouch = rmUcStandardMaterial('PK-POUCH', 'MAT-POUCH', 0.5, 300);
    rmUcStandardFinishedGood('FG-0001', 'Mi Goreng', [[$flour, 50], [$pouch, 1]]);
    rmUcStandardFinishedGood('FG-0002', 'Mi Kuah', [[$flour, 40]]);
    rmUcStandardFinishedGood('FG-0009', 'Out Of Range', [[$flour, 10]]);

    $filters = ['code_from' => 'FG-0001', 'code_to' => 'FG-0002'];
    $plan = $this->actingAs($superadmin)
        ->getJson(route('admin.reporting.rm-uc-standard.excel.plan', $filters))
        ->assertOk()
        ->assertJsonCount(2, 'finished_good_ids')
        ->json();

    // Satu FG per batch untuk memastikan urutan antar batch tetap terjaga.
    foreach ($plan['finished_good_ids'] as $index => $id) {
        $this->postJson(route('admin.reporting.rm-uc-standard.excel.batch'), [
            'token' => $plan['token'], 'index' => $index, 'finished_good_ids' => [$id],
        ])->assertOk()->assertJsonPath('processed', 1);
    }

    $response = $this->get(route('admin.reporting.rm-uc-standard.excel', $filters + ['token' => $plan['token'], 'batches' => 2]))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $temporaryPath = tempnam(sys_get_temp_dir(), 'rm-uc-standard-test-');
    file_put_contents($temporaryPath, $response->getContent());

    try {
        $archive = new ZipArchive;
        expect($archive->open($temporaryPath))->toBeTrue();
        $worksheet = $archive->getFromName('xl/worksheets/sheet1.xml');
        $styles = $archive->getFromName('xl/styles.xml');
        $archive->close();

        expect(substr_count($worksheet, 'Unit Cost Per Pack'))->toBe(2);
        expect(strpos($worksheet, 'Mi Goreng'))->toBeLessThan(strpos($worksheet, 'Mi Kuah'));
        expect($worksheet)
            ->toContain('Product : FG-0001 - Mi Goreng')
            ->toContain('Product : FG-0002 - Mi Kuah')
            ->not->toContain('Out Of Range')
            ->toContain('FG-0001 s/d FG-0002')
            // RM tanpa ID dibagi 1000, Packaging tidak.
            ->toMatch('/<f>E(\d+)\+\(E\1\*F\1\)<\/f><v>51<\/v>/')
            ->toMatch('/<f>G\d+\*H\d+\/1000<\/f><v>612<\/v>/')
            ->toMatch('/<f>G\d+\*H\d+<\/f><v>450<\/v>/')
            ->toMatch('/<f>SUM\(I\d+:I\d+\)<\/f><v>1062<\/v>/');
        expect($styles)
            ->not->toContain('rgb=')
            ->not->toContain('patternType="solid"');
    } finally {
        @unlink($temporaryPath);
    }
});

test('RM unit cost standard report filters the Finished Good code range and skips FG without formula', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $material = rmUcStandardMaterial('RM-0001', null, 0, 1000);
    rmUcStandardFinishedGood('FG-0001', 'First Product', [[$material, 10]]);
    rmUcStandardFinishedGood('FG-0009', 'Last Product', [[$material, 10]]);
    FinishedGood::create(['code' => 'FG-0010', 'description' => 'Product Without Formula', 'multi_level' => 'N', 'active' => 'Y']);

    $this->actingAs($superadmin)
        ->get(route('admin.reporting.rm-uc-standard.index', ['code_from' => 'FG-0005', 'code_to' => 'FG-0010']))
        ->assertOk()
        ->assertSee('Last Product')
        ->assertDontSee('First Product')
        ->assertDontSee('Product Without Formula');
});

test('RM unit cost standard Excel download fails cleanly when batch data has expired', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->getJson(route('admin.reporting.rm-uc-standard.excel', ['token' => str_repeat('a', 32), 'batches' => 1]))
        ->assertStatus(422);
});
