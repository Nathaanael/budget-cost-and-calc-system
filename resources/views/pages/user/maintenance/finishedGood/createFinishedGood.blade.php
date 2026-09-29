@extends('layouts.app')

@section('content')
    <div x-data="{
        form: {
            code: @js(old('code', '')), description: @js(old('description', '')), description_1: @js(old('description_1', '')), product_type_1: @js(old('product_type_1', '')), product_type_2: @js(old('product_type_2', '')),
            batch: @js(old('batch', '')), selling_price: @js(old('selling_price', '')), multi_level: @js(old('multi_level', 'N')), active: @js(old('active', 'Y')),
            unit_cost_current: @js(old('unit_cost_current', '')), unit_price_current: @js(old('unit_price_current', '')), unit_cost_le: @js(old('unit_cost_le', '')), unit_price_le: @js(old('unit_price_le', '')),
            unit_cost_qtr_1: @js(old('unit_cost_qtr_1', '')), unit_price_qtr_1: @js(old('unit_price_qtr_1', '')), unit_cost_qtr_2: @js(old('unit_cost_qtr_2', '')), unit_price_qtr_2: @js(old('unit_price_qtr_2', '')),
            unit_cost_qtr_3: @js(old('unit_cost_qtr_3', '')), unit_price_qtr_3: @js(old('unit_price_qtr_3', '')), unit_cost_qtr_4: @js(old('unit_cost_qtr_4', '')), unit_price_qtr_4: @js(old('unit_price_qtr_4', ''))
        },
        get isValid() {
            return this.form.code.trim() !== ''
                && this.form.description.trim() !== ''
                && this.form.product_type_1 !== ''
                && this.form.product_type_2 !== '';
        },
        formatPrice(value) {
            const number = String(value ?? '').replace(/\D/g, '');
            return number === '' ? '' : new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(number));
        },
        updatePrice(event, field) {
            const value = event.target.value.replace(/\D/g, '');
            this.form[field] = value;
            event.target.value = this.formatPrice(value);
        }
    }">
        <a href="{{ route('admin.maintenance.finished-good.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
            <svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>
            {{ __('Kembali ke tabel') }}
        </a>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.maintenance.finished-good.store') }}">
            @csrf
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-7 py-6 dark:border-gray-800">
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Tambah Finished Good Baru') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Lengkapi data maintenance finished good.') }}</p>
                </div>

                <div class="space-y-8 p-7">
                    <div>
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Informasi Utama') }}</h2>
                        <div class="grid gap-5 lg:grid-cols-2">
                            <div><label for="fg-code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Code FG') }} <span class="text-error-500">*</span></label><input id="fg-code" x-model="form.code" name="code" type="text" inputmode="numeric" minlength="6" maxlength="30" pattern="[0-9]{6,30}" required placeholder="{{ __('Contoh: 400001') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div><label for="fg-multi-level" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Multi Level [Y/N]') }}</label><select id="fg-multi-level" x-model="form.multi_level" name="multi_level" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white"><option value="Y">Y</option><option value="N">N</option></select></div>
                            <div class="lg:col-span-2"><label for="fg-description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }} <span class="text-error-500">*</span></label><input id="fg-description" x-model="form.description" name="description" required placeholder="{{ __('Masukkan description finished good') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div class="lg:col-span-2"><label for="fg-description-1" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description 1') }}</label><input id="fg-description-1" x-model="form.description_1" name="description_1" placeholder="{{ __('Masukkan description tambahan') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                        </div>
                    </div>

                    <div>
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Data Produk') }}</h2>
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
                            <div><label for="fg-product-type-1" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Product Type 1') }} <span class="text-error-500">*</span></label><input id="fg-product-type-1" x-model="form.product_type_1" name="product_type_1" type="number" min="0" required placeholder="0" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div><label for="fg-product-type-2" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Product Type 2') }} <span class="text-error-500">*</span></label><input id="fg-product-type-2" x-model="form.product_type_2" name="product_type_2" type="number" min="0" required placeholder="0" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div><label for="fg-batch" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Batch') }}</label><input id="fg-batch" x-model="form.batch" name="batch" type="number" min="0" placeholder="0" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div><label for="fg-selling-price" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Hrg Jual') }}</label><input id="fg-selling-price" name="selling_price" type="text" inputmode="numeric" :value="formatPrice(form.selling_price)" @input="updatePrice($event, 'selling_price')" placeholder="0" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div><label for="fg-active" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Active [Y/N]') }}</label><select id="fg-active" x-model="form.active" name="active" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white"><option value="Y">Y</option><option value="N">N</option></select></div>
                        </div>
                    </div>

                    <div>
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Unit Cost dan Unit Price') }}</h2>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="grid grid-cols-[1fr_1fr_1fr] gap-px bg-gray-200 text-sm dark:bg-gray-800">
                                <div class="bg-gray-50 px-4 py-3 font-medium text-gray-600 dark:bg-gray-900 dark:text-gray-400">{{ __('Periode') }}</div>
                                <div class="bg-gray-50 px-4 py-3 font-medium text-gray-600 dark:bg-gray-900 dark:text-gray-400">{{ __('Unit Cost') }}</div>
                                <div class="bg-gray-50 px-4 py-3 font-medium text-gray-600 dark:bg-gray-900 dark:text-gray-400">{{ __('Unit Price') }}</div>
                                @foreach (['current' => __('Current'), 'le' => __('LE'), 'qtr_1' => __('Qtr 1'), 'qtr_2' => __('Qtr 2'), 'qtr_3' => __('Qtr 3'), 'qtr_4' => __('Qtr 4')] as $key => $label)
                                    <div class="flex items-center bg-white px-4 py-3 font-medium text-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ $label }}</div>
                                    <div class="bg-white p-2 dark:bg-gray-900"><input name="unit_cost_{{ $key }}" type="text" inputmode="numeric" :value="formatPrice(form.unit_cost_{{ $key }})" @input="updatePrice($event, 'unit_cost_{{ $key }}')" placeholder="0" aria-label="{{ __('Unit Cost') }} {{ $label }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                                    <div class="bg-white p-2 dark:bg-gray-900"><input name="unit_price_{{ $key }}" type="text" inputmode="numeric" :value="formatPrice(form.unit_price_{{ $key }})" @input="updatePrice($event, 'unit_price_{{ $key }}')" placeholder="0" aria-label="{{ __('Unit Price') }} {{ $label }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <button type="submit" :disabled="!isValid" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>{{ __('Submit') }}</button>
        </form>
    </div>
@endsection
