@extends('layouts.app')

@section('content')
    @php
        $isEdit = isset($finishedGood);
        $periods = ['current' => __('Current'), 'le' => __('LE'), 'qtr_1' => __('Qtr 1'), 'qtr_2' => __('Qtr 2'), 'qtr_3' => __('Qtr 3'), 'qtr_4' => __('Qtr 4')];
        $factories = ['cikampek' => __('Cikampek'), 'semarang' => __('Semarang'), 'surabaya' => __('Surabaya'), 'palembang' => __('Palembang')];
        $priceFields = ['selling_price'];

        foreach (array_keys($factories) as $factory) {
            $priceFields[] = "pe_{$factory}";
        }

        foreach (array_keys($periods) as $period) {
            $priceFields[] = "unit_cost_{$period}";
            $priceFields[] = "unit_price_{$period}";

            foreach (['semarang', 'surabaya', 'palembang'] as $factory) {
                $priceFields[] = "unit_price_{$factory}_{$period}";
            }
        }
        $form = [
            'code' => old('code', $finishedGood->code ?? ''),
            'description' => old('description', $finishedGood->description ?? ''),
            'description_1' => old('description_1', $finishedGood->description_1 ?? ''),
            'product_type_1' => old('product_type_1', $finishedGood->product_type_1 ?? ''),
            'product_type_2' => old('product_type_2', $finishedGood->product_type_2 ?? ''),
            'batch' => old('batch', $finishedGood->batch ?? ''),
            'multi_level' => old('multi_level', $finishedGood->multi_level ?? 'N'),
            'active' => old('active', $finishedGood->active ?? 'Y'),
        ];
        foreach ($priceFields as $field) {
            $form[$field] = old(
                $field,
                isset($finishedGood) ? number_format((float) $finishedGood->{$field}, 2, '.', '') : '',
            );
        }
    @endphp
    <div x-data="{
        form: @js($form),
        activeFactory: 'cikampek',
        submitting: false,
        get isValid() {
            return this.form.code.trim() !== ''
                && this.form.description.trim() !== ''
                && this.form.product_type_1 !== ''
                && this.form.product_type_2 !== '';
        },
        parsePrice(value) {
            const normalized = String(value ?? '').trim().replace(/\s/g, '');

            if (normalized === '') return null;
            if (normalized.includes(',')) return Number(normalized.replace(/\./g, '').replace(',', '.'));
            if (/^\d{1,3}(\.\d{3})+$/.test(normalized)) return Number(normalized.replace(/\./g, ''));

            return Number(normalized);
        },
        formatPrice(value) {
            const number = this.parsePrice(value);

            return number === null || !Number.isFinite(number)
                ? ''
                : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(number);
        },
        editablePrice(value) {
            const number = this.parsePrice(value);

            return number === null || !Number.isFinite(number) ? '' : number.toFixed(2).replace('.', ',');
        },
        normalizePriceInput(event, field) {
            const number = this.parsePrice(event.target.value);
            this.form[field] = number === null || !Number.isFinite(number) ? '' : number.toFixed(2);
            event.target.value = this.formatPrice(this.form[field]);
        }
    }">
        <a href="{{ route('admin.maintenance.finished-good.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
            <svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>
            {{ __('Kembali ke tabel') }}
        </a>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('admin.maintenance.finished-good.update', $finishedGood) : route('admin.maintenance.finished-good.store') }}" @submit="submitting = true">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-7 py-6 dark:border-gray-800">
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $isEdit ? __('Edit Finished Good') : __('Tambah Finished Good Baru') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $isEdit ? __('Perbarui data maintenance finished good.') : __('Lengkapi data maintenance finished good.') }}</p>
                </div>

                <div class="space-y-8 p-7">
                    <div>
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Informasi Utama') }}</h2>
                        <div class="grid gap-5 lg:grid-cols-2">
                            <div><label for="fg-code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Code FG') }} <span class="text-error-500">*</span></label><input id="fg-code" x-model="form.code" name="code" type="text" maxlength="7" pattern="[A-Za-z0-9]{1,7}" required placeholder="{{ __('Contoh: FG123A') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /><p class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Gunakan huruf dan angka, maksimal 7 karakter.') }}</p></div>
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
                            <div><label for="fg-selling-price" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Hrg Jual') }}</label><input id="fg-selling-price" name="selling_price" type="text" inputmode="decimal" :value="formatPrice(form.selling_price)" @focus="$event.target.value = editablePrice(form.selling_price)" @input="form.selling_price = $event.target.value" @blur="normalizePriceInput($event, 'selling_price')" placeholder="0,00" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                            <div><label for="fg-active" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Active [Y/N]') }}</label><select id="fg-active" x-model="form.active" name="active" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white"><option value="Y">Y</option><option value="N">N</option></select></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Nilai PE per Pabrik') }}</h2>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Data PE FGMast lama disatukan dalam maintenance Finished Good.') }}</p>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($factories as $factory => $factoryLabel)
                                <div>
                                    <label for="fg-pe-{{ $factory }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('PE') }} {{ $factoryLabel }}</label>
                                    <input id="fg-pe-{{ $factory }}" name="pe_{{ $factory }}" type="text" inputmode="decimal" :value="formatPrice(form.pe_{{ $factory }})" @focus="$event.target.value = editablePrice(form.pe_{{ $factory }})" @input="form.pe_{{ $factory }} = $event.target.value" @blur="normalizePriceInput($event, 'pe_{{ $factory }}')" placeholder="0,00" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" />
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Unit Cost dan Unit Price Utama') }}</h2>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Sesuai UC dan UP tanpa kota pada FGMAST lama. Unit Price Utama merupakan harga Cikampek.') }}</p>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ __('UC dan UP Tanpa Kota') }}</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[540px] divide-y divide-gray-200 text-sm dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="w-1/3 px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('Periode') }}</th>
                                            <th class="w-1/3 px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('Unit Cost') }}</th>
                                            <th class="w-1/3 px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('Unit Price Utama') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach ($periods as $period => $periodLabel)
                                            <tr>
                                                <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700 dark:text-gray-300">{{ $periodLabel }}</td>
                                                <td class="p-2"><input type="text" inputmode="decimal" :value="formatPrice(form.unit_cost_{{ $period }})" @focus="$event.target.value = editablePrice(form.unit_cost_{{ $period }})" @input="form.unit_cost_{{ $period }} = $event.target.value" @blur="normalizePriceInput($event, 'unit_cost_{{ $period }}')" placeholder="0,00" aria-label="{{ __('Unit Cost Utama') }} {{ $periodLabel }}" class="h-11 w-full min-w-32 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></td>
                                                <td class="p-2"><input name="unit_price_{{ $period }}" type="text" inputmode="decimal" :value="formatPrice(form.unit_price_{{ $period }})" @focus="$event.target.value = editablePrice(form.unit_price_{{ $period }})" @input="form.unit_price_{{ $period }} = $event.target.value" @blur="normalizePriceInput($event, 'unit_price_{{ $period }}')" placeholder="0,00" aria-label="{{ __('Unit Price Utama') }} {{ $periodLabel }}" class="h-11 w-full min-w-32 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Unit Price per Pabrik') }}</h2>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Unit Cost menggunakan nilai utama di atas. Unit Price Cikampek sama dengan Unit Price Utama.') }}</p>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="grid grid-cols-2 gap-px border-b border-gray-200 bg-gray-200 dark:border-gray-800 dark:bg-gray-800 sm:grid-cols-4" role="tablist" aria-label="{{ __('Pabrik') }}">
                                @foreach ($factories as $factory => $factoryLabel)
                                    <button type="button" role="tab" @click="activeFactory = '{{ $factory }}'" :aria-selected="activeFactory === '{{ $factory }}'" :class="activeFactory === '{{ $factory }}' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800'" class="relative px-4 py-3 text-sm font-medium transition">
                                        {{ $factoryLabel }}
                                        <span x-show="activeFactory === '{{ $factory }}'" class="absolute inset-x-0 bottom-0 h-0.5 bg-brand-500" aria-hidden="true"></span>
                                    </button>
                                @endforeach
                            </div>

                            @foreach ($periods as $period => $periodLabel)
                                <input type="hidden" name="unit_cost_{{ $period }}" :value="form.unit_cost_{{ $period }}">
                            @endforeach

                            @foreach ($factories as $factory => $factoryLabel)
                                <div x-show="activeFactory === '{{ $factory }}'" x-cloak role="tabpanel" aria-label="{{ $factoryLabel }}">
                                    <div class="border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ __('Harga Pabrik') }} {{ $factoryLabel }}</p>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full min-w-[540px] divide-y divide-gray-200 text-sm dark:divide-gray-800">
                                            <thead class="bg-gray-50 dark:bg-gray-900">
                                                <tr>
                                                    <th class="w-1/2 px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('Periode') }}</th>
                                                    <th class="w-1/2 px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('Unit Price') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach ($periods as $period => $periodLabel)
                                            @php($field = $factory === 'cikampek' ? "unit_price_{$period}" : "unit_price_{$factory}_{$period}")
                                            <tr>
                                                <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700 dark:text-gray-300">{{ $periodLabel }}</td>
                                                <td class="p-2"><input @if ($factory !== 'cikampek') name="{{ $field }}" @endif type="text" inputmode="decimal" :value="formatPrice(form.{{ $field }})" @focus="$event.target.value = editablePrice(form.{{ $field }})" @input="form.{{ $field }} = $event.target.value" @blur="normalizePriceInput($event, '{{ $field }}')" placeholder="0,00" aria-label="{{ __('Unit Price') }} {{ $factoryLabel }} {{ $periodLabel }}" class="h-11 w-full min-w-32 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></td>
                                            </tr>
                                        @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <button type="submit" :disabled="!isValid || submitting" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border bg-transparent text-sm font-semibold shadow-theme-xs transition enabled:border-brand-300 enabled:text-brand-600 enabled:hover:bg-brand-50 enabled:focus:ring-3 enabled:focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 dark:enabled:border-brand-500/40 dark:enabled:text-brand-400 dark:enabled:hover:bg-brand-500/10 dark:disabled:border-gray-800 dark:disabled:text-gray-600"><svg x-show="!submitting" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg><svg x-show="submitting" x-cloak class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/></svg><span x-text="submitting ? '{{ __('Menyimpan...') }}' : '{{ $isEdit ? __('Simpan Perubahan') : __('Submit') }}'"></span></button>
        </form>
    </div>
@endsection
