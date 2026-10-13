<?php

use App\Models\FinishedGood;
use App\Models\Reference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function sellUcUpFinishedGood(string $code, string $description, array $attributes = []): FinishedGood
{
    return FinishedGood::create(array_merge([
        'code' => $code, 'description' => $description, 'multi_level' => 'N', 'active' => 'Y',
        'product_type_1' => 1, 'product_type_2' => 1,
    ], $attributes));
}

function sellUcUpReference(): Reference
{
    return Reference::create([
        'code' => '00', 'description_1' => 'PT Indofood', 'description_2' => 'Noodle Division',
        'period' => '01012027', 'period_description' => 'JANUARY 2027',
    ]);
}

test('selling price and unit cost report follows the legacy Report3 filter and order', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    sellUcUpReference();
    sellUcUpFinishedGood('FG-0003', 'Type Two Product', ['product_type_1' => 2, 'unit_cost_current' => 1500, 'unit_price_current' => 1750]);
    sellUcUpFinishedGood('FG-0002', 'Type One Product', ['unit_cost_qtr_1' => 1200.5]);
    // Report3() lama melewatkan FG yang hanya punya harga Palembang; di sini tetap tampil.
    sellUcUpFinishedGood('FG-0004', 'Palembang Only', ['product_type_1' => 2, 'unit_price_palembang_le' => 2000]);
    sellUcUpFinishedGood('FG-0005', 'All Zero Product');
    sellUcUpFinishedGood('FG-0006', 'Inactive Product', ['active' => 'N', 'unit_cost_current' => 900]);

    $this->actingAs($superadmin)
        ->get(route('admin.reporting.sell-uc-up.index'))
        ->assertOk()
        ->assertSee('Tahun AOP 2028')
        ->assertSee('Selling Price Palembang')
        ->assertSeeInOrder(['Product Type 1 / 1', 'Type One Product', '1.200,50', 'Product Type 2 / 1', 'Type Two Product', '1.750,00', '1.500,00', 'Palembang Only', '2.000,00'])
        ->assertDontSee('All Zero Product')
        ->assertDontSee('Inactive Product');
});

test('selling price and unit cost Excel is colorless and respects the FG code range', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    sellUcUpReference();
    sellUcUpFinishedGood('FG-0001', 'Included Product', ['unit_cost_current' => 1500, 'unit_price_semarang_qtr_4' => 1825.25]);
    sellUcUpFinishedGood('FG-0009', 'Excluded Product', ['unit_cost_current' => 1500]);

    $filters = ['code_from' => 'FG-0001', 'code_to' => 'FG-0005'];
    $plan = $this->actingAs($superadmin)
        ->getJson(route('admin.reporting.sell-uc-up.excel.plan', $filters))
        ->assertOk()
        ->assertJsonCount(1, 'finished_good_ids')
        ->json();

    $this->postJson(route('admin.reporting.sell-uc-up.excel.batch'), [
        'token' => $plan['token'], 'index' => 0, 'finished_good_ids' => $plan['finished_good_ids'],
    ])->assertOk()->assertJsonPath('processed', 1);

    $response = $this->get(route('admin.reporting.sell-uc-up.excel', $filters + ['token' => $plan['token'], 'batches' => 1]))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->assertDownload('selling-price-unit-cost-january-2027.xlsx');

    $temporaryPath = tempnam(sys_get_temp_dir(), 'sell-uc-up-test-');
    file_put_contents($temporaryPath, $response->getContent());

    try {
        $archive = new ZipArchive;
        expect($archive->open($temporaryPath))->toBeTrue();
        $worksheet = $archive->getFromName('xl/worksheets/sheet1.xml');
        $styles = $archive->getFromName('xl/styles.xml');
        $archive->close();

        expect($worksheet)
            ->toContain('SELLING PRICE &amp; UNIT COST')
            ->toContain(': 2028')
            ->toContain('Selling Price Palembang')
            ->toContain('Product Type 1 / 1')
            ->toContain('Included Product')
            ->toContain('<v>1825.25</v>')
            ->not->toContain('Excluded Product');
        expect($styles)
            ->not->toContain('rgb=')
            ->not->toContain('patternType="solid"');
    } finally {
        @unlink($temporaryPath);
    }
});

test('selling price and unit cost report filters by product type', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    sellUcUpFinishedGood('FG-0001', 'Indomie Product', ['product_type_1' => 0, 'product_type_2' => 1, 'unit_cost_current' => 1000]);
    sellUcUpFinishedGood('FG-0002', 'Export Product', ['product_type_1' => 0, 'product_type_2' => 23, 'unit_cost_current' => 1000]);
    sellUcUpFinishedGood('FG-0003', 'Other Type One', ['product_type_1' => 1, 'product_type_2' => 23, 'unit_cost_current' => 1000]);

    $this->actingAs($superadmin)
        ->get(route('admin.reporting.sell-uc-up.index', ['product_type_1' => '0', 'product_type_2' => '23']))
        ->assertOk()
        ->assertSee('Export Product')
        ->assertDontSee('Indomie Product')
        ->assertDontSee('Other Type One');

    $this->getJson(route('admin.reporting.sell-uc-up.excel.plan', ['product_type_2' => '23']))
        ->assertOk()
        ->assertJsonCount(2, 'finished_good_ids');
});
