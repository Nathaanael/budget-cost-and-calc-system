@extends('layouts.app')

@section('content')
    @php
        $periods = [
            'current' => __('Current'),
            'le' => __('LE'),
            'qtr_1' => __('Quarter 1'),
            'qtr_2' => __('Quarter 2'),
            'qtr_3' => __('Quarter 3'),
            'qtr_4' => __('Quarter 4'),
        ];
        $emptyPrices = [];

        foreach (array_keys($periods) as $period) {
            $emptyPrices["usd_{$period}"] = 0;
            $emptyPrices["rupiah_{$period}"] = 0;
        }
    @endphp

    <div
        x-data="{
            selectedId: '',
            material: null,
            form: { raw_material_id: '', ...@js($emptyPrices) },
            loading: false,
            saving: false,
            error: '',
            success: '',
            resetForm() {
                this.material = null;
                this.form = { raw_material_id: '', ...@js($emptyPrices) };
            },
            async loadPrice() {
                this.error = '';
                this.success = '';

                if (!this.selectedId) {
                    this.resetForm();
                    return;
                }

                this.loading = true;
                try {
                    const response = await fetch(`{{ url('/admin/entry/rm-price/data') }}/${this.selectedId}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message);
                    this.material = payload.raw_material;
                    this.form = { raw_material_id: payload.raw_material.id, ...payload.prices };
                } catch (error) {
                    this.resetForm();
                    this.error = error.message || '{{ __('Data harga gagal dimuat.') }}';
                } finally {
                    this.loading = false;
                }
            },
            async savePrice() {
                if (!this.material || !window.confirm('{{ __('Data Sudah Benar?') }}')) return;

                this.saving = true;
                this.error = '';
                this.success = '';
                try {
                    const response = await fetch('{{ route('admin.entry.rm-price.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify(this.form),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(Object.values(payload.errors ?? {}).flat()[0] ?? payload.message);
                    this.form = { raw_material_id: payload.raw_material.id, ...payload.prices };
                    this.success = payload.message;
                } catch (error) {
                    this.error = error.message || '{{ __('Data harga gagal disimpan.') }}';
                } finally {
                    this.saving = false;
                }
            }
        }"
        @keydown.escape.window="error = ''; success = ''">
        <div x-show="success" x-cloak x-text="success" class="mb-5 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400"></div>
        <div x-show="error" x-cloak x-text="error" class="mb-5 rounded-xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>

        <form @submit.prevent="savePrice()" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-7">
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Entry Raw Material Price') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Masukkan harga Current, LE, dan kuartal berdasarkan Raw Material yang sudah terdaftar.') }}</p>
            </div>

            <section class="rounded-xl border border-gray-200 p-5 dark:border-gray-800">
                <label for="rm-price-code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Code RM') }} <span class="text-error-500">*</span></label>
                <select id="rm-price-code" x-model="selectedId" @change="loadPrice()" required class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">{{ __('Pilih Raw Material') }}</option>
                    @foreach ($rawMaterials as $rawMaterial)
                        <option value="{{ $rawMaterial->id }}">{{ $rawMaterial->code }} - {{ $rawMaterial->description }}</option>
                    @endforeach
                </select>

                <div x-show="loading" x-cloak class="mt-4 text-sm text-gray-500 dark:text-gray-400">{{ __('Memuat data harga...') }}</div>

                <div x-show="material && !loading" x-cloak class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Description') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90" x-text="material?.description"></p>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('ID') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90" x-text="material?.material_id"></p>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Currency Type') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90" x-text="material?.currency_type"></p>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Type RM') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90" x-text="material?.type_rm"></p>
                    </div>
                </div>
            </section>

            <section class="mt-5 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="grid grid-cols-[minmax(7rem,1fr)_minmax(9rem,2fr)_minmax(9rem,2fr)] bg-gray-50 text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <div class="border-e border-gray-200 px-4 py-3 dark:border-gray-700">{{ __('Periode') }}</div>
                    <div class="border-e border-gray-200 px-4 py-3 text-center dark:border-gray-700">{{ __('USD') }}</div>
                    <div class="px-4 py-3 text-center">{{ __('Rupiah') }}</div>
                </div>

                @foreach ($periods as $period => $label)
                    <div class="grid grid-cols-[minmax(7rem,1fr)_minmax(9rem,2fr)_minmax(9rem,2fr)] border-t border-gray-200 dark:border-gray-800">
                        <div class="flex items-center border-e border-gray-200 bg-gray-50/60 px-4 py-3 text-sm font-medium text-gray-700 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-300">{{ $label }}</div>
                        <div class="border-e border-gray-200 p-3 dark:border-gray-800">
                            <label for="usd-{{ $period }}" class="sr-only">{{ $label }} USD</label>
                            <input id="usd-{{ $period }}" x-model.number="form.usd_{{ $period }}" type="number" min="0" max="999999.99" step="0.01" required :disabled="!material || loading || saving" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-end text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 dark:border-gray-700 dark:text-white dark:disabled:bg-gray-800 dark:disabled:text-gray-600" />
                        </div>
                        <div class="p-3">
                            <label for="rupiah-{{ $period }}" class="sr-only">{{ $label }} Rupiah</label>
                            <input id="rupiah-{{ $period }}" x-model.number="form.rupiah_{{ $period }}" type="number" min="0" max="9999999.99" step="0.01" required :disabled="!material || loading || saving" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-end text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 dark:border-gray-700 dark:text-white dark:disabled:bg-gray-800 dark:disabled:text-gray-600" />
                        </div>
                    </div>
                @endforeach
            </section>

            <button type="submit" :disabled="!material || loading || saving" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600">
                <svg x-show="!saving" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>
                <svg x-show="saving" x-cloak class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/></svg>
                <span x-text="saving ? '{{ __('Menyimpan...') }}' : '{{ __('Simpan Harga') }}'"></span>
            </button>
        </form>
    </div>
@endsection
