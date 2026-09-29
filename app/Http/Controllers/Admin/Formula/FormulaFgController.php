<?php

namespace App\Http\Controllers\Admin\Formula;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class FormulaFgController extends Controller
{
    public function index(): View
    {
        return view('pages.user.maintenance.formula.formulaFg', [
            'title' => __('Formula FG'),
            'masterItems' => $this->finishedGoods(),
            'ingredientItems' => $this->rawMaterials(),
            'initialFormulas' => [
                'FG-0001' => [
                    ['code' => 'RM-0001', 'description' => 'Tepung Terigu', 'standard' => 0.750000],
                    ['code' => 'RM-0002', 'description' => 'Minyak Goreng', 'standard' => 0.150000],
                    ['code' => 'RM-0003', 'description' => 'Garam', 'standard' => 0.025000],
                ],
                'FG-0002' => [
                    ['code' => 'RM-0001', 'description' => 'Tepung Terigu', 'standard' => 0.700000],
                    ['code' => 'RM-0007', 'description' => 'Seasoning Ayam', 'standard' => 0.050000],
                ],
            ],
        ]);
    }

    private function finishedGoods(): array
    {
        return [
            ['code' => 'FG-0001', 'description' => 'Indomie Mi Goreng 5 x 85 gr'],
            ['code' => 'FG-0002', 'description' => 'Indomie Ayam Bawang 5 x 69 gr'],
            ['code' => 'FG-0003', 'description' => 'Supermi Ayam Bawang 5 x 75 gr'],
            ['code' => 'FG-0004', 'description' => 'Sarimi Isi 2 Mi Goreng'],
            ['code' => 'FG-0005', 'description' => 'Pop Mie Rasa Ayam'],
            ['code' => 'FG-0006', 'description' => 'Indomie Soto Mie'],
            ['code' => 'FG-0007', 'description' => 'Indomie Kari Ayam'],
            ['code' => 'FG-0008', 'description' => 'Supermi Semur Ayam'],
            ['code' => 'FG-0009', 'description' => 'Sarimi Ayam Kremes'],
            ['code' => 'FG-0010', 'description' => 'Pop Mie Baso'],
        ];
    }

    private function rawMaterials(): array
    {
        return [
            ['code' => 'RM-0001', 'description' => 'Tepung Terigu'],
            ['code' => 'RM-0002', 'description' => 'Minyak Goreng'],
            ['code' => 'RM-0003', 'description' => 'Garam'],
            ['code' => 'RM-0004', 'description' => 'Bawang Bubuk'],
            ['code' => 'RM-0005', 'description' => 'Kemasan Plastik'],
            ['code' => 'RM-0006', 'description' => 'Karton Box'],
            ['code' => 'RM-0007', 'description' => 'Seasoning Ayam'],
            ['code' => 'RM-0008', 'description' => 'Cabai Bubuk'],
            ['code' => 'RM-0009', 'description' => 'Label Produk'],
            ['code' => 'RM-0010', 'description' => 'Flavor Import'],
        ];
    }
}
