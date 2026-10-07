<?php

namespace App\Http\Middleware;

use App\Models\Plant;
use App\Support\PlantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetActivePlant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $plants = Plant::query()->orderBy('code')->get();
        $plant = $plants->firstWhere('id', (int) $request->session()->get('active_plant_id')) ?? $plants->first();

        abort_if($plant === null, 503, __('Belum ada Plant yang tersedia.'));

        $request->session()->put('active_plant_id', $plant->id);
        app(PlantContext::class)->set($plant);
        View::share(['activePlant' => $plant, 'availablePlants' => $plants]);

        return $next($request);
    }
}
