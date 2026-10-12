<?php

namespace App\View\Components\ecommerce;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MonthlyTarget extends Component
{
    public function __construct()
    {
        //
    }

    /**
     * @return View|Closure|string
     */
    public function render()
    {
        return view('components.ecommerce.monthly-target');
    }
}




