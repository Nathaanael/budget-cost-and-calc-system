@extends('layouts.app')

@php
    $editingUser = old('editing_user')
        ? $users->getCollection()->firstWhere('id', (int) old('editing_user'))
        : null;
    $initialEditor = $editingUser ? [
        'id' => $editingUser->id,
        'username' => old('username', $editingUser->username),
        'role' => old('role', $editingUser->role),
    ] : null;
    $createHasErrors = old('form_context') === 'create';
@endphp

@section('content')
    <div
        x-data="{
            createOpen: @js($createHasErrors),
            editOpen: @js($initialEditor !== null),
            selectedUser: @js($initialEditor ?? ['id' => null, 'username' => '', 'role' => 'user']),
            openCreate() {
                this.createOpen = true;
                this.$nextTick(() => this.$refs.nameInput?.focus());
            },
            closeCreate() {
                this.createOpen = false;
            },
            openEdit(user) {
                this.selectedUser = user;
                this.editOpen = true;
                this.$nextTick(() => this.$refs.usernameInput?.focus());
            },
            closeEdit() {
                this.editOpen = false;
            }
        }"
        @keydown.escape.window="closeCreate(); closeEdit()">
        <!-- <x-common.page-breadcrumb pageTitle="{{ __('Manajemen User') }}" /> -->

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-5 border-b border-gray-200 px-6 py-5 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Manajemen User') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Manajemen data user dan reset password.') }}</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button type="button" @click="openCreate()" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/20">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" />
                        </svg>
                        {{ __('Tambah User') }}
                    </button>

                    <form method="GET" action="{{ route('admin.users.index') }}" class="relative w-full sm:w-80">
                        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" />
                            </svg>
                        </span>
                        <input name="search" type="search" value="{{ $search }}" placeholder="{{ __('Cari username atau nama...') }}"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr>
                            @foreach ([__('Username'), __('Role'), __('Aksi')] as $heading)
                                <th class="px-6 py-3 text-start text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($users as $user)
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->username ?? '-' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $user->isSuperadmin() ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if ($user->is_active)
                                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" onsubmit="return confirm('{{ __('Nonaktifkan user ini?') }}')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex size-9 items-center justify-center rounded-lg border border-error-300 bg-error-50 text-error-600 transition hover:bg-error-100 focus:ring-3 focus:ring-error-500/20 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20" title="{{ __('Nonaktifkan user') }}" aria-label="{{ __('Nonaktifkan user') }}">
                                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.35 6.35a8 8 0 1 0 11.3 11.3 8 8 0 0 0-11.3-11.3Zm0 0 11.3 11.3" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex size-9 items-center justify-center rounded-lg bg-success-500 text-white transition hover:bg-success-600 focus:ring-3 focus:ring-success-500/20" title="{{ __('Aktifkan user') }}" aria-label="{{ __('Aktifkan user') }}">
                                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.5 19.5v-1.25A4.25 4.25 0 0 0 11.25 14h-4.5a4.25 4.25 0 0 0-4.25 4.25v1.25M9 10a3.75 3.75 0 1 0 0-7.5A3.75 3.75 0 0 0 9 10Zm8.5-2.5v6m3-3h-6" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($user->must_change_password)
                                            <button type="button" disabled class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-100 text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500" title="{{ __('Password awal belum diganti') }}" aria-label="{{ __('Reset password tidak tersedia karena password awal belum diganti') }}">
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.75 8.75V4.5m0 0H9m-4.25 0 3.1 3.1A7 7 0 1 1 5 13.25" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10.25v3.25l2.25 1.25" />
                                                </svg>
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" onsubmit="return confirm('{{ __('Reset password user ini menjadi sama dengan username?') }}')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex size-9 items-center justify-center rounded-lg border border-warning-300 bg-warning-50 text-warning-700 transition hover:bg-warning-100 focus:ring-3 focus:ring-warning-500/20 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400 dark:hover:bg-warning-500/20" title="{{ __('Reset password') }}" aria-label="{{ __('Reset password') }}">
                                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.75 8.75V4.5m0 0H9m-4.25 0 3.1 3.1A7 7 0 1 1 5 13.25" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10.25v3.25l2.25 1.25" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        <button
                                            type="button"
                                            @click="openEdit({{ Illuminate\Support\Js::from(['id' => $user->id, 'username' => $user->username, 'role' => $user->role]) }})"
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 transition hover:border-brand-300 hover:text-brand-500 focus:ring-3 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:text-brand-400"
                                            title="{{ __('Edit user') }}"
                                            aria-label="{{ __('Edit user') }}">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.86 3.49 3.65 3.65M5 19l3.85-.77L19.74 7.34a1.5 1.5 0 0 0 0-2.12l-.96-.96a1.5 1.5 0 0 0-2.12 0L5.77 15.15 5 19Z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada user.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">{{ $users->links() }}</div>
            @endif
        </div>

        <div
            x-show="createOpen"
            x-cloak
            class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-user-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeCreate()"></div>

            <div
                x-show="createOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="create-user-title" class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Tambah User') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Password awal otomatis sama dengan username.') }}</p>
                    </div>
                    <button type="button" @click="closeCreate()" class="flex size-9 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white" aria-label="{{ __('Tutup') }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="form_context" value="create">

                    <div>
                        <label for="create-name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Lengkap') }}</label>
                        <input id="create-name" x-ref="nameInput" name="name" type="text" value="{{ old('name') }}" required placeholder="{{ __('Masukkan nama lengkap') }}"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        @if ($createHasErrors) @error('name')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror @endif
                    </div>

                    <div>
                        <label for="create-username" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Username') }}</label>
                        <input id="create-username" name="username" type="text" value="{{ old('username') }}" required placeholder="{{ __('Masukkan username') }}"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        @if ($createHasErrors) @error('username')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror @endif
                    </div>

                    <div>
                        <label for="create-role" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Role') }}</label>
                        <select id="create-role" name="role" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="user" @selected(old('role', 'user') === 'user')>{{ __('User') }}</option>
                            <option value="superadmin" @selected(old('role') === 'superadmin')>{{ __('Superadmin') }}</option>
                        </select>
                        @if ($createHasErrors) @error('role')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror @endif
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeCreate()" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/20">
                            {{ __('Simpan User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div
            x-show="editOpen"
            x-cloak
            class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-user-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeEdit()"></div>

            <div
                x-show="editOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="edit-user-title" class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Edit User') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Ubah username atau role user.') }}</p>
                    </div>
                    <button type="button" @click="closeEdit()" class="flex size-9 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white" aria-label="{{ __('Tutup') }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg>
                    </button>
                </div>

                <form method="POST" :action="selectedUser ? '{{ url('/admin/users') }}/' + selectedUser.id : '#'" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="editing_user" :value="selectedUser?.id">

                    <div>
                        <label for="edit-username" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Username') }}</label>
                        <input id="edit-username" x-ref="usernameInput" x-model="selectedUser.username" name="username" type="text" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        @error('username')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit-role" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Role') }}</label>
                        <select id="edit-role" x-model="selectedUser.role" name="role" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="user">{{ __('User') }}</option>
                            <option value="superadmin">{{ __('Superadmin') }}</option>
                        </select>
                        @error('role')<p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeEdit()" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/20">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
