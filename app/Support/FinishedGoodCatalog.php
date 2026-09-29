<?php

namespace App\Support;

use Illuminate\Support\Collection;

class FinishedGoodCatalog
{
    public static function all(): Collection
    {
        return collect([
            ['code' => 'FG-0001', 'description' => 'Indomie Mi Goreng 5 x 85 gr', 'product_type_1' => 12, 'product_type_2' => 24, 'multi_level' => 'Y', 'active' => 'Y'],
            ['code' => 'FG-0002', 'description' => 'Indomie Ayam Bawang 5 x 69 gr', 'product_type_1' => 10, 'product_type_2' => 20, 'multi_level' => 'N', 'active' => 'Y'],
            ['code' => 'FG-0003', 'description' => 'Supermi Ayam Bawang 5 x 75 gr', 'product_type_1' => 8, 'product_type_2' => 16, 'multi_level' => 'Y', 'active' => 'Y'],
            ['code' => 'FG-0004', 'description' => 'Sarimi Isi 2 Mi Goreng', 'product_type_1' => 6, 'product_type_2' => 12, 'multi_level' => 'N', 'active' => 'Y'],
            ['code' => 'FG-0005', 'description' => 'Pop Mie Rasa Ayam', 'product_type_1' => 4, 'product_type_2' => 8, 'multi_level' => 'N', 'active' => 'N'],
            ['code' => 'FG-0006', 'description' => 'Indomie Soto Mie', 'product_type_1' => 14, 'product_type_2' => 28, 'multi_level' => 'Y', 'active' => 'Y'],
            ['code' => 'FG-0007', 'description' => 'Indomie Kari Ayam', 'product_type_1' => 9, 'product_type_2' => 18, 'multi_level' => 'N', 'active' => 'Y'],
            ['code' => 'FG-0008', 'description' => 'Supermi Semur Ayam', 'product_type_1' => 7, 'product_type_2' => 14, 'multi_level' => 'Y', 'active' => 'N'],
            ['code' => 'FG-0009', 'description' => 'Sarimi Ayam Kremes', 'product_type_1' => 5, 'product_type_2' => 10, 'multi_level' => 'N', 'active' => 'Y'],
            ['code' => 'FG-0010', 'description' => 'Pop Mie Baso', 'product_type_1' => 3, 'product_type_2' => 6, 'multi_level' => 'N', 'active' => 'Y'],
        ])->map(function (array $item, int $index) {
            $sequence = $index + 1;
            $baseCost = 2500 + ($sequence * 125);
            $basePrice = 3000 + ($sequence * 150);

            return [
                ...$item,
                'description_1' => 'Finished Good '.$sequence,
                'batch' => 100 + $sequence,
                'selling_price' => $basePrice + 500,
                'unit_cost_current' => $baseCost,
                'unit_price_current' => $basePrice,
                'unit_cost_le' => $baseCost + 25,
                'unit_price_le' => $basePrice + 25,
                'unit_cost_qtr_1' => $baseCost + 50,
                'unit_price_qtr_1' => $basePrice + 50,
                'unit_cost_qtr_2' => $baseCost + 75,
                'unit_price_qtr_2' => $basePrice + 75,
                'unit_cost_qtr_3' => $baseCost + 100,
                'unit_price_qtr_3' => $basePrice + 100,
                'unit_cost_qtr_4' => $baseCost + 125,
                'unit_price_qtr_4' => $basePrice + 125,
            ];
        });
    }
}
