<?php

namespace App\Http\Controllers\Admin\Entry;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RmPriceController extends Controller
{
    public function index(): View
    {
        return view('pages.user.entry.rmPrice.rmPrice', [
            'title' => __('RM Price'),
        ]);
    }
}
