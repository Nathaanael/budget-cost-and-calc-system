@extends('layouts.app')

@section('content')
    <div x-data="{
        form: {
            area_noodle_id: @js(old('area_noodle_id', '')), area_description: '', noodle_id: @js(old('noodle_id', '')), noodle_description: '',
            le_july: @js(old('le_july', 0)), le_august: @js(old('le_august', 0)), le_september: @js(old('le_september', 0)), le_october: @js(old('le_october', 0)), le_november: @js(old('le_november', 0)), le_december: @js(old('le_december', 0)),
            january: @js(old('january', 0)), february: @js(old('february', 0)), march: @js(old('march', 0)), april: @js(old('april', 0)), may: @js(old('may', 0)), june: @js(old('june', 0)),
            july: @js(old('july', 0)), august: @js(old('august', 0)), september: @js(old('september', 0)), october: @js(old('october', 0)), november: @js(old('november', 0)), december: @js(old('december', 0))
        },
        get isValid() { return this.form.area_noodle_id !== '' && this.form.noodle_id !== ''; }
    }">
        <a href="{{ route('admin.entry.volume-noodle.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400"><svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>{{ __('Kembali ke tabel') }}</a>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.entry.volume-noodle.store') }}">
            @csrf
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-7">
                <div class="mb-6"><h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Maintenance Volume Noodle') }}</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Lengkapi relasi produk, LE, dan volume bulanan.') }}</p></div>
                <x-volume-noodle.form-fields model="form" :area-options="$areaOptions" :noodle-options="$noodleOptions" />
            </section>

            <button type="submit" :disabled="!isValid" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>{{ __('Simpan') }}</button>
        </form>
    </div>
@endsection
