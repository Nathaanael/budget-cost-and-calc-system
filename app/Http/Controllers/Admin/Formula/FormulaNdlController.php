<?php

namespace App\Http\Controllers\Admin\Formula;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class FormulaNdlController extends Controller
{
    public function index(): View
    {
        return view('pages.user.maintenance.formula.formulaNdl', [
            'title' => __('Formula NDL'),
            'masterItems' => $this->noodles(),
            'ingredientItems' => $this->finishedGoods(),
            'initialFormulas' => [
                '2000001' => [
                    ['code' => 'FG-0001', 'description' => 'Indomie Mi Goreng 5 x 85 gr', 'standard' => 1.000000],
                    ['code' => 'FG-0005', 'description' => 'Pop Mie Rasa Ayam', 'standard' => 0.500000],
                ],
                '2000002' => [
                    ['code' => 'FG-0002', 'description' => 'Indomie Ayam Bawang 5 x 69 gr', 'standard' => 1.000000],
                ],
            ],
        ]);
    }

    private function noodles(): array
    {
        return [
            ['code' => '2000001', 'description' => 'Indomie Mi Goreng'],
            ['code' => '2000002', 'description' => 'Indomie Rasa Ayam Bawang'],
            ['code' => '2000003', 'description' => 'Supermi Rasa Ayam Bawang'],
            ['code' => '2000004', 'description' => 'Sarimi Isi 2 Mi Goreng'],
            ['code' => '2000005', 'description' => 'Pop Mie Rasa Ayam'],
            ['code' => '2000006', 'description' => 'Indomie Mi Goreng'],
            ['code' => '2000007', 'description' => 'Indomie Rasa Ayam Bawang'],
            ['code' => '2000008', 'description' => 'Supermi Rasa Ayam Bawang'],
            ['code' => '2000009', 'description' => 'Sarimi Isi 2 Mi Goreng'],
            ['code' => '2000010', 'description' => 'Pop Mie Rasa Ayam'],
        ];
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
}
