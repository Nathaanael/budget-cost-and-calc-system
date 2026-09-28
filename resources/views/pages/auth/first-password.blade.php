@extends('layouts.fullscreen-layout')

@section('content')
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gray-100 px-4 py-10 dark:bg-gray-950 sm:px-6">
        <img src="{{ asset('images/auth-background.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover" />
        <div class="absolute inset-0 bg-white/78 backdrop-blur-[3px] dark:bg-gray-950/85"></div>

        <section class="relative z-1 w-full max-w-[480px] overflow-hidden rounded-3xl bg-white shadow-theme-xl dark:bg-gray-900">
            <header class="bg-brand-500 px-8 py-8 text-center text-white">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full border-4 border-white/70 bg-white shadow-theme-sm">
                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Logo" class="size-9" />
                </span>
                <h1 class="mt-4 text-xl font-semibold">{{ __('Buat Password Baru') }}</h1>
                <p class="mt-1 text-sm text-white/75">{{ __('Amankan akun Anda sebelum melanjutkan.') }}</p>
            </header>

            <div class="px-7 py-9 sm:px-10">
                <div class="mb-6 rounded-xl border border-blue-light-100 bg-blue-light-50 px-4 py-3 text-sm leading-6 text-blue-light-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                    {{ __('Ini adalah login pertama Anda. Buat password pribadi yang tidak sama dengan username.') }}
                </div>

                <form method="POST" action="{{ route('password.first.update') }}" x-data="{ showPassword: false }" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password Baru') }}</label>
                        <div class="relative">
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required
                                class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 pe-12 text-sm text-gray-800 outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/15 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 end-0 flex items-center pe-4 text-gray-500 hover:text-brand-500">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.04 12.32a1 1 0 010-.64C3.42 7.51 7.35 4.5 12 4.5c4.64 0 8.57 3 9.96 7.18a1 1 0 010 .64C20.58 16.49 16.65 19.5 12 19.5c-4.64 0-8.57-3-9.96-7.18zM15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Konfirmasi Password Baru') }}</label>
                        <input id="password_confirmation" name="password_confirmation" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required
                            class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-gray-800 outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/15 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <p class="text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Minimal 8 karakter serta mengandung huruf dan angka.') }}</p>

                    @if ($errors->any())
                        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <button type="submit" class="flex h-12 w-full items-center justify-center rounded-full bg-brand-500 px-5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/30">
                        {{ __('Simpan Password dan Lanjutkan') }}
                    </button>
                </form>
            </div>
        </section>
    </main>
@endsection
