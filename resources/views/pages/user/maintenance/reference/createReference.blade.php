@extends('layouts.app')

@section('content')
    @php
        $isEdit = isset($reference);
        $form = [
            'code' => old('code', $reference->code ?? ''),
            'description_1' => old('description_1', $reference->description_1 ?? ''),
            'description_2' => old('description_2', $reference->description_2 ?? ''),
            'period' => old('period', $reference->period ?? ''),
            'period_description' => old('period_description', $reference->period_description ?? ''),
        ];

        foreach (\App\Models\Reference::numericFields() as $field) {
            $value = isset($reference) ? $reference->{$field} : '';
            $form[$field] = old($field, str_starts_with($field, 'rate_') && $value !== '' ? (string) (int) $value : $value);
        }
    @endphp

    <div x-data="{
        form: @js($form),
        submitting: false,
        get isValid() {
            return this.form.code.trim() !== ''
                && this.form.description_1.trim() !== ''
                && this.form.description_2.trim() !== ''
                && this.form.period.trim() !== ''
                && this.form.period_description.trim() !== '';
        },
        formatPrice(value) { return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value) || 0); },
        updatePrice(event, target, field) {
            const value = event.target.value.replace(/\D/g, '');
            target[field] = value;
            event.target.value = this.formatPrice(value);
        }
    }">
        <a href="{{ route('admin.maintenance.reference.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
            <svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>
            {{ __('Kembali ke tabel') }}
        </a>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('admin.maintenance.reference.update', $reference) : route('admin.maintenance.reference.store') }}" @submit="submitting = true" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-7">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div class="mb-5">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $isEdit ? __('Edit Reference') : __('Maintenance Reference') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $isEdit ? __('Perbarui rate dan nilai PE Current, LE, serta kuartal setiap plant.') : __('Lengkapi rate dan nilai PE Current, LE, serta kuartal setiap plant.') }}</p>
            </div>

            <x-reference.form-fields model="form" />

            <button type="submit" :disabled="!isValid || submitting" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600">
                <svg x-show="!submitting" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>
                <svg x-show="submitting" x-cloak class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/></svg>
                <span x-text="submitting ? '{{ __('Menyimpan...') }}' : '{{ $isEdit ? __('Simpan Perubahan') : __('Simpan') }}'"></span>
            </button>
        </form>
    </div>
@endsection
