@extends('layouts.app')

@section('content')
    <div x-data="{
        submitting: false,
        form: @js([
            'rm_code' => old('rm_code', ''),
            'rm_description' => old('rm_description', ''),
            'fg_code' => old('fg_code', ''),
            'fg_description' => old('fg_description', ''),
        ]),
        get isValid() { return this.form.rm_code !== '' && this.form.fg_code !== ''; }
    }">
        <a href="{{ route('admin.maintenance.synonim.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400"><svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>{{ __('Kembali ke tabel') }}</a>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.maintenance.synonim.store') }}" @submit="submitting = true">
            @csrf
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-7">
                <div class="mb-6"><h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Maintenance Synonim') }}</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Hubungkan Raw Material dengan Finished Good.') }}</p></div>
                <x-synonim.form-fields model="form" :raw-material-options="$rawMaterialOptions" :finished-good-options="$finishedGoodOptions" />
            </section>

            <button type="submit" :disabled="!isValid || submitting" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600">
                <svg x-show="!submitting" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>
                <svg x-show="submitting" x-cloak class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" /><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z" /></svg>
                <span x-text="submitting ? '{{ __('Menyimpan...') }}' : '{{ __('Simpan') }}'"></span>
            </button>
        </form>
    </div>
@endsection
