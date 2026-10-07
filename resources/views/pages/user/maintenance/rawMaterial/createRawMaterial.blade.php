@extends('layouts.app')

@section('content')
    @php
        $isEdit = isset($rawMaterial);
        $periods = ['current' => __('Current'), 'le' => __('LE'), 'qtr_1' => __('Qtr 1'), 'qtr_2' => __('Qtr 2'), 'qtr_3' => __('Qtr 3'), 'qtr_4' => __('Qtr 4')];
        $prices = $isEdit ? $rawMaterial->prices->keyBy('period') : collect();
        $form = [
            'code' => old('code', $rawMaterial->code ?? ''),
            'description' => old('description', $rawMaterial->description ?? ''),
            'unit' => old('unit', $rawMaterial->unit ?? ''),
            'wastage_all' => old('wastage_all', $rawMaterial->wastage_all ?? ''),
            'material_id' => old('material_id', $rawMaterial->material_id ?? ''),
            'currency_type' => old('currency_type', $rawMaterial->currency_type ?? 'Rp'),
            'type_rm' => old('type_rm', $rawMaterial->type_rm ?? ''),
        ];

        foreach (array_keys($periods) as $period) {
            $form["usd_{$period}"] = old("usd_{$period}", $prices->get($period)?->usd_amount ?? '');
            $form["rupiah_{$period}"] = old("rupiah_{$period}", $prices->get($period)?->rupiah_amount ?? '');
        }
    @endphp
    <div x-data="{
        form: @js($form),
        submitting: false,
        get isValid() {
            return this.form.code.trim() !== ''
                && this.form.description.trim() !== ''
                && this.form.unit.trim() !== ''
                && this.form.wastage_all !== '';
        }
    }">
        <a href="{{ route('admin.maintenance.raw-material.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400"><svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>{{ __('Kembali ke tabel') }}</a>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('admin.maintenance.raw-material.update', $rawMaterial) : route('admin.maintenance.raw-material.store') }}" @submit="submitting = true">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-7 py-6 dark:border-gray-800"><h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $isEdit ? __('Edit Raw Material') : __('Tambah Raw Material Baru') }}</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $isEdit ? __('Perbarui data master raw material.') : __('Lengkapi data master raw material.') }}</p></div>
                <div class="space-y-8 p-7">
                    <div>
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Informasi Utama') }}</h2>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div><label for="rm-code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Code RM') }} <span class="text-error-500">*</span></label><input id="rm-code" x-model="form.code" name="code" type="text" maxlength="7" pattern="[A-Za-z0-9]{1,7}" required placeholder="{{ __('Contoh: RM123A') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /><p class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Gunakan huruf dan angka, maksimal 7 karakter.') }}</p></div>
                            <div><label for="rm-id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ID') }} <span class="text-gray-400">({{ __('Opsional') }})</span></label><input id="rm-id" x-model="form.material_id" name="material_id" placeholder="{{ __('Contoh: MAT-011') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></div>
                            <div class="sm:col-span-2"><label for="rm-description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }} <span class="text-error-500">*</span></label><input id="rm-description" x-model="form.description" name="description" required placeholder="{{ __('Masukkan description raw material') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div><label for="rm-unit" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Unit') }} <span class="text-error-500">*</span></label><input id="rm-unit" x-model="form.unit" name="unit" type="text" required placeholder="{{ __('Contoh: Kg, Liter, Pcs') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div><label for="rm-wastage" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Wastage All') }} <span class="text-error-500">*</span></label><input id="rm-wastage" x-model="form.wastage_all" name="wastage_all" type="number" min="0" step="0.01" required placeholder="0" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div><label for="rm-currency" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Currency Type') }} <span class="text-error-500">*</span></label><select id="rm-currency" x-model="form.currency_type" name="currency_type" required class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white"><option value="Rp">Rp</option><option value="USD">USD</option></select></div>
                            <div><label for="rm-type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Type RM') }} <span class="text-gray-400">({{ __('Opsional') }})</span></label><input id="rm-type" x-model="form.type_rm" name="type_rm" type="text" placeholder="{{ __('Masukkan type RM') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Harga Raw Material per Periode') }} <span class="font-normal normal-case text-gray-400">({{ __('Opsional') }})</span></h2>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Harga dapat dikosongkan sekarang dan dilengkapi nanti melalui Edit Raw Material atau Entry RM Price.') }}</p>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[620px] divide-y divide-gray-200 text-sm dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="w-1/3 px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('Periode') }}</th>
                                            <th class="w-1/3 px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('USD') }}</th>
                                            <th class="w-1/3 px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('Rupiah') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach ($periods as $period => $periodLabel)
                                            <tr>
                                                <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700 dark:text-gray-300">{{ $periodLabel }}</td>
                                                <td class="p-2"><input x-model.number="form.usd_{{ $period }}" name="usd_{{ $period }}" type="number" min="0" max="999999999.99" step="0.01" placeholder="0" aria-label="{{ $periodLabel }} {{ __('USD') }}" class="h-11 w-full min-w-36 rounded-lg border border-gray-300 bg-transparent px-3 text-end text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></td>
                                                <td class="p-2"><input x-model.number="form.rupiah_{{ $period }}" name="rupiah_{{ $period }}" type="number" min="0" max="999999999.99" step="0.01" placeholder="0" aria-label="{{ $periodLabel }} {{ __('Rupiah') }}" class="h-11 w-full min-w-36 rounded-lg border border-gray-300 bg-transparent px-3 text-end text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <button type="submit" :disabled="!isValid || submitting" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600"><svg x-show="!submitting" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg><svg x-show="submitting" x-cloak class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/></svg><span x-text="submitting ? '{{ __('Menyimpan...') }}' : '{{ $isEdit ? __('Simpan Perubahan') : __('Submit') }}'"></span></button>
        </form>
    </div>
@endsection
