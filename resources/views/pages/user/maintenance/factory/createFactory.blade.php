@extends('layouts.app')

@section('content')
    <div x-data="{
        saved: false,
        form: {
            code: '', description: '', area_1: '', area_2: '', area_3: '', area_4: '', area_5: '',
            area_6: '', area_7: '', area_8: '', area_9: '', area_10: ''
        },
        get isValid() {
            return this.form.code.trim() !== ''
                && this.form.description.trim() !== ''
                && Array.from({ length: 10 }, (_, index) => this.form[`area_${index + 1}`]).every(Boolean);
        }
    }">
        <a href="{{ route('admin.maintenance.factory.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
            <svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>
            {{ __('Kembali ke tabel') }}
        </a>

        <div x-show="saved" x-cloak class="mb-5 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ __('Simulasi berhasil. Data belum disimpan ke backend.') }}</div>

        <form @submit.prevent="saved = true">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-7">
                <div class="mb-5">
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Maintenance Factory') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Lengkapi data factory dan relasi area.') }}</p>
                </div>

                <x-factory.form-fields model="form" :area-options="$areaOptions" />
            </section>

            <button type="submit" :disabled="!isValid" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>{{ __('Simpan') }}</button>
        </form>
    </div>
@endsection
