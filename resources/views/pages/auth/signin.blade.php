@extends('layouts.fullscreen-layout')

@section('content')
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gray-100 px-4 py-10 dark:bg-gray-950 sm:px-6">
        <img src="{{ asset('images/auth-background.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover" />
        <div class="absolute inset-0 bg-white/72 backdrop-blur-[2px] dark:bg-gray-950/80"></div>

        <section class="relative z-1 w-full max-w-[460px] overflow-hidden rounded-3xl bg-white shadow-theme-xl dark:bg-gray-900">
            <header class="flex items-center justify-center gap-4 bg-brand-500 px-8 py-8 text-white sm:py-9">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-full border-4 border-white/70 bg-white shadow-theme-sm">
                    <svg class="size-8 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 10V7.5a4.5 4.5 0 0 1 9 0V10m-10 0h11a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Z" />
                        <path stroke-linecap="round" stroke-width="1.8" d="M12 14v3" />
                    </svg>
                </span>
                <div>
                    <p class="text-theme-xs font-medium uppercase tracking-[0.24em] text-white/75">Indofood</p>
                    <h1 class="mt-1 text-xl font-semibold">{{ __('Budget Cost and Sales Program') }}</h1>
                </div>
            </header>

            <div class="px-7 py-9 sm:px-10 sm:py-11">
                <div class="mb-7 text-center">
                    <h2 class="text-title-sm font-semibold text-gray-900 dark:text-white">{{ __('Selamat Datang') }}</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Masuk menggunakan akun yang telah dibuat oleh superadmin.') }}</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Username') }}</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.12a7.5 7.5 0 0115 0A17.9 17.9 0 0112 21.75a17.9 17.9 0 01-7.5-1.63z" /></svg>
                            </span>
                            <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autofocus required
                                placeholder="{{ __('Masukkan username') }}"
                                class="h-12 w-full rounded-full border border-blue-light-100 bg-blue-light-50 ps-12 pe-5 text-sm text-gray-800 outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/15 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>
                    </div>

                    <div x-data="{ showPassword: false }">
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password') }}</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 10.5h10.5A2.25 2.25 0 0019.5 18.75v-6A2.25 2.25 0 0017.25 10.5H6.75A2.25 2.25 0 004.5 12.75v6A2.25 2.25 0 006.75 21z" /></svg>
                            </span>
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required
                                placeholder="{{ __('Masukkan password') }}"
                                class="h-12 w-full rounded-full border border-blue-light-100 bg-blue-light-50 ps-12 pe-14 text-sm text-gray-800 outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/15 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 end-0 flex items-center pe-4 text-gray-500 transition hover:text-brand-500" :aria-label="showPassword ? '{{ __('Sembunyikan password') }}' : '{{ __('Tampilkan password') }}'">
                                <svg x-show="!showPassword" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.04 12.32a1 1 0 010-.64C3.42 7.51 7.35 4.5 12 4.5c4.64 0 8.57 3 9.96 7.18a1 1 0 010 .64C20.58 16.49 16.65 19.5 12 19.5c-4.64 0-8.57-3-9.96-7.18z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <svg x-show="showPassword" x-cloak class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3.98 8.22A10.48 10.48 0 002.04 12.3a1 1 0 000 .64C3.42 17.1 7.35 20.1 12 20.1c.99 0 1.94-.14 2.84-.4M6.23 6.23A10.45 10.45 0 0112 4.5c4.64 0 8.57 3 9.96 7.18a1 1 0 010 .64 10.5 10.5 0 01-4.3 5.37M6.23 6.23L3 3m3.23 3.23l3.54 3.54m7.89 7.89L21 21m-3.34-3.34l-3.54-3.54m0 0A3 3 0 009.88 9.88m4.24 4.24L9.88 9.88" /></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="plant_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Plant') }}</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 20V9l8-5 8 5v11M8 20v-6h8v6M7 10h.01M12 10h.01M17 10h.01" /></svg>
                            </span>
                            <select id="plant_id" name="plant_id" required
                                class="h-12 w-full appearance-none rounded-full border border-blue-light-100 bg-blue-light-50 ps-12 pe-10 text-sm text-gray-800 outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/15 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                @foreach ($plants as $plant)
                                    <option value="{{ $plant->id }}" @selected((string) old('plant_id', $plants->first()?->id) === (string) $plant->id)>{{ $plant->code }} - {{ $plant->description }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute end-4 top-1/2 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- <label for="remember" class="flex cursor-pointer items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                        <input id="remember" name="remember" type="checkbox" class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" />
                        {{ __('Ingat saya') }}
                    </label> -->

                    <button type="submit" class="flex h-12 w-full items-center justify-center rounded-full bg-brand-500 px-5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/30">
                        {{ __('Masuk') }}
                    </button>
                </form>

                <div class="mt-7 flex items-center gap-4 text-gray-300 dark:text-gray-700"><span class="h-px flex-1 bg-current"></span><span class="text-theme-xs font-medium uppercase tracking-widest text-gray-400">Info</span><span class="h-px flex-1 bg-current"></span></div>
                <p class="mt-5 text-center text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Belum memiliki akun? Hubungi superadmin untuk pendaftaran user.') }}</p>
                <p class="mt-2 text-center text-theme-xs text-gray-400">{{ __('Login pertama: password awal sama dengan username.') }}</p>
            </div>
        </section>
    </main>
@endsection
