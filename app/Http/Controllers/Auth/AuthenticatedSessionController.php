<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.signin', [
            'title' => __('Masuk'),
            'plants' => Plant::query()->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'plant_id' => ['nullable', 'integer', 'exists:plants,id'],
        ]);

        $plantId = (int) ($credentials['plant_id'] ?? Plant::query()->orderBy('code')->value('id'));
        unset($credentials['plant_id']);

        $credentials['is_active'] = true;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('username', 'plant_id'))
                ->withErrors(['username' => __('Username atau password tidak sesuai.')]);
        }

        $request->session()->regenerate();
        $request->session()->put('active_plant_id', $plantId);

        if ($request->user()->must_change_password) {
            return redirect()->route('password.first.edit');
        }

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
