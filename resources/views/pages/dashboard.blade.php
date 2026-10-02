@extends('layouts.app')

@section('content')
    @php
        $user = auth()->user();
        $displayName = $user->name ?: $user->username;
    @endphp

    <section
        class="relative isolate overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-8 lg:p-10"
        x-data="{
            currentTime: new Date(),
            timer: null,
            formatter(options) {
                return new Intl.DateTimeFormat('id-ID', { ...options, timeZone: 'Asia/Jakarta' }).format(this.currentTime);
            },
            day() { return this.formatter({ weekday: 'long' }); },
            date() { return this.formatter({ day: '2-digit', month: 'long', year: 'numeric' }); },
            time() { return this.formatter({ hour: '2-digit', minute: '2-digit', hour12: false }); }
        }"
        x-init="timer = setInterval(() => currentTime = new Date(), 1000); $cleanup(() => clearInterval(timer))"
    >
        <div class="pointer-events-none absolute -end-20 -top-24 size-72 rounded-full bg-brand-100/70 blur-3xl dark:bg-brand-500/10"></div>
        <div class="pointer-events-none absolute -bottom-32 start-1/3 size-80 rounded-full bg-blue-light-100/70 blur-3xl dark:bg-blue-light-500/10"></div>

        <div class="relative grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.72fr)] lg:items-center">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-3 py-1.5 text-sm font-medium text-brand-600 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400">
                    <span class="size-2 rounded-full bg-success-500"></span>
                    {{ __(ucfirst($user->role)) }}
                </span>

                <h1 class="mt-6 text-title-sm font-semibold text-gray-900 dark:text-white sm:text-title-md">
                    {{ __('Selamat Datang') }}, {{ $displayName }}
                </h1>
                <p class="mt-3 max-w-2xl text-base leading-7 text-gray-500 dark:text-gray-400">
                    {{ __('Semoga hari Anda produktif bersama Budget Cost and Sales Program.') }}
                </p>

                <div class="mt-8 flex items-center gap-3 text-gray-600 dark:text-gray-300">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-gray-100 text-brand-500 dark:bg-gray-800 dark:text-brand-400">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3v3m10-3v3M4.5 9.5h15M6.5 5h11a2 2 0 0 1 2 2v11.5a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Hari ini') }}</p>
                        <p class="font-semibold text-gray-800 dark:text-white/90">
                            <span x-text="day()">{{ __('Hari') }}</span>, <span x-text="date()">{{ now('Asia/Jakarta')->format('d-m-Y') }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-6 text-center shadow-theme-xs backdrop-blur-sm dark:border-gray-700 dark:bg-gray-800/70 sm:p-8">
                <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-brand-500 text-white shadow-theme-sm">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke-width="1.8" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v5l3.25 2" />
                    </svg>
                </div>
                <p class="mt-5 text-sm font-medium uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ __('Waktu Indonesia Barat') }}</p>
                <p class="mt-2 font-mono text-4xl font-semibold tabular-nums tracking-tight text-gray-900 dark:text-white sm:text-5xl" x-text="time()">
                    {{ now('Asia/Jakarta')->format('H:i') }}
                </p>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('Jakarta, Indonesia') }}</p>
            </div>
        </div>
    </section>
@endsection
