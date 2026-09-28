<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        return view('pages.admin.users.index', [
            'title' => __('Manajemen User'),
            'users' => User::query()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('username', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique(User::class),
            ],
            'role' => ['required', Rule::in(['superadmin', 'user'])],
        ]);

        User::create([
            ...$validated,
            'password' => $validated['username'],
            'is_active' => true,
            'must_change_password' => true,
        ]);

        return back()->with('status', __('User berhasil dibuat. Password awal sama dengan username.'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique(User::class)->ignore($user),
            ],
            'role' => ['required', Rule::in(['superadmin', 'user'])],
        ]);

        $user->username = $validated['username'];
        $user->role = $validated['role'];

        $user->save();

        return back()->with('status', __('Data user berhasil diperbarui.'));
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with(
            'status',
            $user->is_active ? __('User berhasil diaktifkan.') : __('User berhasil dinonaktifkan.'),
        );
    }

    public function resetPassword(User $user): RedirectResponse
    {
        if ($user->must_change_password) {
            return back()->with('status', __('Reset password tidak diperlukan karena user belum mengganti password awal.'));
        }

        $user->update([
            'password' => $user->username,
            'must_change_password' => true,
        ]);

        return back()->with('status', __('Password berhasil direset. Password sementara sama dengan username.'));
    }
}
