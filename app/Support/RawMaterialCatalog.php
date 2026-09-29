<?php

namespace App\Support;

use Illuminate\Support\Collection;

class RawMaterialCatalog
{
    public static function all(): Collection
    {
        return collect([
            ['code' => 'RM-0001', 'description' => 'Tepung Terigu', 'unit' => 'Kg', 'wastage_all' => 1.25, 'material_id' => 'MAT-001', 'currency_type' => 'Rp', 'type_rm' => 'Bahan Utama'],
            ['code' => 'RM-0002', 'description' => 'Minyak Goreng', 'unit' => 'Liter', 'wastage_all' => 0.75, 'material_id' => 'MAT-002', 'currency_type' => 'Rp', 'type_rm' => 'Bahan Utama'],
            ['code' => 'RM-0003', 'description' => 'Garam', 'unit' => 'Kg', 'wastage_all' => 0.20, 'material_id' => 'MAT-003', 'currency_type' => 'Rp', 'type_rm' => 'Bumbu'],
            ['code' => 'RM-0004', 'description' => 'Bawang Bubuk', 'unit' => 'Kg', 'wastage_all' => 0.50, 'material_id' => 'MAT-004', 'currency_type' => 'USD', 'type_rm' => 'Bumbu'],
            ['code' => 'RM-0005', 'description' => 'Kemasan Plastik', 'unit' => 'Roll', 'wastage_all' => 2.00, 'material_id' => 'MAT-005', 'currency_type' => 'Rp', 'type_rm' => 'Packaging'],
            ['code' => 'RM-0006', 'description' => 'Karton Box', 'unit' => 'Pcs', 'wastage_all' => 1.50, 'material_id' => 'MAT-006', 'currency_type' => 'Rp', 'type_rm' => 'Packaging'],
            ['code' => 'RM-0007', 'description' => 'Seasoning Ayam', 'unit' => 'Kg', 'wastage_all' => 0.40, 'material_id' => 'MAT-007', 'currency_type' => 'USD', 'type_rm' => 'Bumbu'],
            ['code' => 'RM-0008', 'description' => 'Cabai Bubuk', 'unit' => 'Kg', 'wastage_all' => 0.30, 'material_id' => 'MAT-008', 'currency_type' => 'Rp', 'type_rm' => 'Bumbu'],
            ['code' => 'RM-0009', 'description' => 'Label Produk', 'unit' => 'Lembar', 'wastage_all' => 1.00, 'material_id' => 'MAT-009', 'currency_type' => 'Rp', 'type_rm' => 'Packaging'],
            ['code' => 'RM-0010', 'description' => 'Flavor Import', 'unit' => 'Kg', 'wastage_all' => 0.15, 'material_id' => 'MAT-010', 'currency_type' => 'USD', 'type_rm' => 'Bumbu'],
        ]);
    }
}
