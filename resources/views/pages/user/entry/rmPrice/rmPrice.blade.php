@extends('layouts.app')

@section('content')
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-7">
        <div class="flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v18m4-14.5h-6a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6H7.5" /></svg>
        </div>
        <h1 class="mt-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('RM Price') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Skeleton halaman Entry RM Price. Form dan tabel akan ditambahkan pada tahap berikutnya.') }}</p>
    </section>
@endsection
