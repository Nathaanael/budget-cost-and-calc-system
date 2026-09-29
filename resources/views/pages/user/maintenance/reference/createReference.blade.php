@extends('layouts.app')

@section('content')
    <div x-data="{
        saved: false,
        form: {
            code: '', description_1: '', description_2: '', period: '', period_description: '',
            rate_current: '', rate_le: '', rate_1: '', rate_2: '', rate_3: '', rate_4: '',
            pe_ckp_le: '', pe_ckp_1: '', pe_ckp_2: '', pe_ckp_3: '', pe_ckp_4: '',
            pe_smg_le: '', pe_smg_1: '', pe_smg_2: '', pe_smg_3: '', pe_smg_4: '',
            pe_sby_le: '', pe_sby_1: '', pe_sby_2: '', pe_sby_3: '', pe_sby_4: ''
        },
        get isValid() {
            return this.form.code.trim() !== ''
                && this.form.description_1.trim() !== ''
                && this.form.description_2.trim() !== ''
                && this.form.period.trim() !== ''
                && this.form.period_description.trim() !== '';
        }
    }">
        <a href="{{ route('admin.maintenance.reference.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
            <svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>
            {{ __('Kembali ke tabel') }}
        </a>

        <div x-show="saved" x-cloak class="mb-5 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">
            {{ __('Simulasi berhasil. Data belum disimpan ke backend.') }}
        </div>

        <form @submit.prevent="saved = true" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-7">
            <div class="mb-5">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Maintenance Reference') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Lengkapi data reference dan nilai PE setiap plant.') }}</p>
            </div>

            <x-reference.form-fields model="form" />

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.maintenance.reference.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Batal') }}</a>
                <button type="submit" :disabled="!isValid" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border bg-transparent px-6 text-sm font-semibold transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>
                    {{ __('Simpan') }}
                </button>
            </div>
        </form>
    </div>
@endsection
