@extends('layouts.app')

@section('content')
    <div x-data="{
        saved: false,
        form: {
            area_code: '', area_description: '', noodle_code: '', noodle_description: '',
            le_june: 0, le_august: 0, le_september: 0, le_october: 0, le_november: 0, le_december: 0,
            january: 0, february: 0, march: 0, april: 0, may: 0, june: 0,
            july: 0, august: 0, september: 0, october: 0, november: 0, december: 0
        },
        get isValid() { return this.form.area_code !== '' && this.form.noodle_code !== ''; }
    }">
        <a href="{{ route('admin.entry.volume-noodle.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400"><svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>{{ __('Kembali ke tabel') }}</a>

        <div x-show="saved" x-cloak class="mb-5 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ __('Simulasi berhasil. Data belum disimpan ke backend.') }}</div>

        <form @submit.prevent="saved = true">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-7">
                <div class="mb-6"><h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Maintenance Volume Noodle') }}</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Lengkapi relasi produk, LE, dan volume bulanan.') }}</p></div>
                <x-volume-noodle.form-fields model="form" :area-options="$areaOptions" :noodle-options="$noodleOptions" />
            </section>

            <button type="submit" :disabled="!isValid" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>{{ __('Simpan') }}</button>
        </form>
    </div>
@endsection
