@extends('layouts.app')

@section('content')
    <div x-data="{
        code: @js(old('code', '')),
        description: @js(old('description', '')),
        unit: @js(old('unit', '')),
        get isValid() {
            return this.code.trim() !== '' && this.description.trim() !== '' && this.unit !== '';
        }
    }">
        <a href="{{ route('admin.maintenance.noodle.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
            <svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>
            {{ __('Kembali ke tabel') }}
        </a>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.maintenance.noodle.store') }}">
            @csrf
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-7 py-6 dark:border-gray-800">
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Tambah Noodle Baru') }}</h1>
                </div>

                <div class="space-y-6 p-7">
                    <div>
                        <label for="noodle-code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Noodle Code') }} <span class="text-error-500">*</span></label>
                        <input id="noodle-code" x-model="code" name="code" type="text" inputmode="numeric" minlength="6" maxlength="30" pattern="[0-9]{6,30}" required placeholder="{{ __('Contoh: 200001') }}"
                            class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label for="noodle-description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }} <span class="text-error-500">*</span></label>
                        <input id="noodle-description" x-model="description" name="description" type="text" required placeholder="{{ __('Masukkan description noodle') }}"
                            class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label for="noodle-unit" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Unit') }} <span class="text-error-500">*</span></label>
                        <input id="noodle-unit" x-model="unit" name="unit" type="text" maxlength="30" required placeholder="{{ __('Contoh: Dus, Cup, Kg') }}"
                            class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>
            </section>

            <button type="submit" :disabled="!isValid" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>
                {{ __('Submit') }}
            </button>
        </form>
    </div>
@endsection
