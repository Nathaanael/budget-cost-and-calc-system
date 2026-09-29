<?php

namespace App\Support;

use Illuminate\Support\Collection;

class AreaNoodleCatalog
{
    public static function all(): Collection
    {
        return collect([
            ['code' => 'C1', 'description' => 'ANCOL'],
            ['code' => 'C2', 'description' => 'TANGERANG'],
            ['code' => 'C3', 'description' => 'BANDUNG'],
            ['code' => 'C4', 'description' => 'SEMARANG'],
            ['code' => 'C5', 'description' => 'CIBITUNG'],
            ['code' => 'C6', 'description' => 'SOLO'],
            ['code' => 'E1', 'description' => 'SURABAYA'],
            ['code' => 'E2', 'description' => 'BANJARMASIN'],
            ['code' => 'E3', 'description' => 'UJUNG PANDANG'],
            ['code' => 'E4', 'description' => 'MANADO'],
            ['code' => 'W1', 'description' => 'MEDAN'],
            ['code' => 'W2', 'description' => 'PEKANBARU'],
            ['code' => 'W3', 'description' => 'PALEMBANG'],
        ]);
    }
}
