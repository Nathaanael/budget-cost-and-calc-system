<?php

namespace App\Support;

use Illuminate\Support\Collection;

class NoodleCatalog
{
    public static function all(): Collection
    {
        return collect([
            ['code' => '2000001', 'description' => 'Indomie Mi Goreng', 'unit' => 'Dus'],
            ['code' => '2000002', 'description' => 'Indomie Rasa Ayam Bawang', 'unit' => 'Dus'],
            ['code' => '2000003', 'description' => 'Supermi Rasa Ayam Bawang', 'unit' => 'Dus'],
            ['code' => '2000004', 'description' => 'Sarimi Isi 2 Mi Goreng', 'unit' => 'Dus'],
            ['code' => '2000005', 'description' => 'Pop Mie Rasa Ayam', 'unit' => 'Cup'],
            ['code' => '2000006', 'description' => 'Indomie Mi Goreng', 'unit' => 'Dus'],
            ['code' => '2000007', 'description' => 'Indomie Rasa Ayam Bawang', 'unit' => 'Dus'],
            ['code' => '2000008', 'description' => 'Supermi Rasa Ayam Bawang', 'unit' => 'Dus'],
            ['code' => '2000009', 'description' => 'Sarimi Isi 2 Mi Goreng', 'unit' => 'Dus'],
            ['code' => '2000010', 'description' => 'Pop Mie Rasa Ayam', 'unit' => 'Cup'],
        ]);
    }
}
