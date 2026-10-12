<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivePlantController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(['plant_id' => ['required', 'integer', 'exists:plants,id']]);
        $plant = Plant::query()->findOrFail($validated['plant_id']);
        $request->session()->put('active_plant_id', $plant->id);

        return back()->with('success', __('Plant aktif berhasil diubah ke :plant.', ['plant' => $plant->code]));
    }
}
