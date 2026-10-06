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
        $factories = [
            'cikampek' => __('Cikampek'),
            'semarang' => __('Semarang'),
            'surabaya' => __('Surabaya'),
            'palembang' => __('Palembang'),
        ];
        $unitCostFields = collect(array_keys($periods))->mapWithKeys(fn ($period) => [$period => "unit_cost_{$period}"]);
        $peFields = collect(array_keys($factories))->mapWithKeys(fn ($factory) => [$factory => "pe_{$factory}"]);
        $unitPriceFields = [];

        foreach (array_keys($factories) as $factory) {
            foreach (array_keys($periods) as $period) {
                $unitPriceFields[$factory][$period] = $factory === 'cikampek'
                    ? "unit_price_{$period}"
                    : "unit_price_{$factory}_{$period}";
            }
        }
    @endphp

    <div
        class="min-w-0 max-w-full"
        x-data="{
            finishedGoods: @js($table['data']),
            meta: @js($table['meta']),
            summary: @js($table['summary']),
            selectedPeriod: 'current',
            selectedFactory: 'cikampek',
            calcMulti: true,
            search: '',
            loading: false,
            calculating: false,
            selectedIds: [], confirmOpen: false,
            init() {
                ['search', 'calcMulti', 'selectedPeriod', 'selectedFactory'].forEach(field => this.$watch(field, () => { this.selectedIds = []; }));
            },
            openConfirmation() {
                if (this.calculating || this.loading || this.selectedIds.length === 0) return;
                this.confirmOpen = true;
                this.$refs.confirmCalculation.showModal();
                this.$nextTick(() => this.$refs.cancelCalculation.focus());
            },
            closeConfirmation() {
                if (this.calculating) return;
                this.confirmOpen = false;
                this.$refs.confirmCalculation.close();
                this.$refs.calculateTrigger.focus();
            },
            error: '',
            success: '',
            unitCostFields: @js($unitCostFields),
            peFields: @js($peFields),
            unitPriceFields: @js($unitPriceFields),
            async load(page = 1) {
                this.loading = true;
                this.error = '';

                try {
                    const params = new URLSearchParams({
                        page: String(page),
                        search: this.search.trim(),
                        calc_multi: this.calcMulti ? '1' : '0',
                    });
                    const response = await fetch(`{{ route('admin.calculate.unit-cost-price.data') }}?${params}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const payload = await response.json();

                    if (!response.ok) throw new Error(payload.message || '{{ __('Data gagal dimuat.') }}');

                    this.finishedGoods = payload.data;
                    this.meta = payload.meta;
                    this.summary = payload.summary;
                } catch (error) {
                    this.error = error.message || '{{ __('Data gagal dimuat.') }}';
                } finally {
                    this.loading = false;
                }
            },
            async setCalcMulti(value) {
                this.calcMulti = value;
                await this.load(1);
            },
            async calculate() {
                if (!this.confirmOpen || this.calculating || this.selectedIds.length === 0) return;

                this.calculating = true;
                this.error = '';
                this.success = '';

                try {
                    const response = await fetch('{{ route('admin.calculate.unit-cost-price.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ calculate_multi_level: this.calcMulti, finished_good_ids: this.selectedIds }),
                    });
                    const payload = await response.json();

                    if (!response.ok) throw new Error(payload.message || '{{ __('Perhitungan gagal diproses.') }}');

                    const result = payload.summary;
                    this.selectedIds = [];
                    this.success = `${payload.message} ${result.calculated_finished_goods} {{ __('FG dihitung') }}, ${result.updated_periods} {{ __('periode diperbarui') }}, ${result.skipped_finished_goods} {{ __('FG dilewati') }}.`;
                    await this.load(this.meta.current_page);
                } catch (error) {
                    this.error = error.message || '{{ __('Perhitungan gagal diproses.') }}';
                } finally {
                    this.calculating = false;
                    this.closeConfirmation();
                }
            },
            pages() {
                const last = Number(this.meta.last_page) || 1;
                let start = Math.max(1, (Number(this.meta.current_page) || 1) - 2);
                start = Math.min(start, Math.max(1, last - 4));
                const end = Math.min(last, start + 4);
                return Array.from({ length: end - start + 1 }, (_, index) => start + index);
            },
            unitCost(row) { return Number(row[this.unitCostFields[this.selectedPeriod]]) || 0; },
            pe(row) { return Number(row[this.peFields[this.selectedFactory]]) || 0; },
            previewPrice(row) { return this.unitCost(row) + this.pe(row); },
            storedPrice(row) { return Number(row[this.unitPriceFields[this.selectedFactory][this.selectedPeriod]]) || 0; },
            difference(row) { return this.previewPrice(row) - this.storedPrice(row); },
            formatAmount(value) {
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value) || 0);
            }
        }">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Calculate U.Cost + U.Price') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Menghitung biaya formula dan harga Finished Good per pabrik sesuai proses Calc3 lama.') }}</p>
                </div>
                <button x-ref="calculateTrigger" type="button" @click="openConfirmation()" :disabled="calculating || loading || selectedIds.length === 0" aria-haspopup="dialog" class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60">
                    <svg class="size-5" :class="calculating && 'animate-spin'" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg>
                    <span x-text="calculating ? '{{ __('Menghitung...') }}' : '{{ __('Calculate & Save') }}'"></span>
                    (<span x-text="selectedIds.length"></span>)
                </button>
            </div>

            <div x-show="success" x-cloak class="border-b border-success-200 bg-success-50 px-6 py-3 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300" x-text="success"></div>
            <div x-show="error" x-cloak class="border-b border-error-200 bg-error-50 px-6 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300" x-text="error"></div>

            <div class="grid gap-4 border-b border-gray-200 p-6 dark:border-gray-800 lg:grid-cols-3">
                <div>
                    <label for="unit-cost-period" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Periode Preview') }}</label>
                    <select id="unit-cost-period" x-model="selectedPeriod" class="h-12 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @foreach ($periods as $period => $label)
                            <option value="{{ $period }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <span class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Calc Multi Level') }}</span>
                    <div class="grid grid-cols-2 gap-2 rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
                        <button type="button" @click="setCalcMulti(true)" :class="calcMulti ? 'bg-white text-brand-600 shadow-theme-xs dark:bg-gray-900 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400'" class="h-10 rounded-md text-sm font-medium transition">{{ __('Ya') }}</button>
                        <button type="button" @click="setCalcMulti(false)" :class="!calcMulti ? 'bg-white text-brand-600 shadow-theme-xs dark:bg-gray-900 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400'" class="h-10 rounded-md text-sm font-medium transition">{{ __('Tidak') }}</button>
                    </div>
                </div>
                <form @submit.prevent="load(1)">
                    <label for="unit-cost-search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Cari Finished Good') }}</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg>
                        <input id="unit-cost-search" x-model="search" type="search" placeholder="{{ __('Cari Code FG atau description...') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </form>
            </div>

            <div class="grid gap-4 border-b border-gray-200 p-6 dark:border-gray-800 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800"><p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Finished Good Ditampilkan') }}</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="summary.total"></p></div>
                <div class="rounded-xl border border-success-200 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/10"><p class="text-theme-xs font-medium uppercase tracking-wide text-success-600 dark:text-success-400">{{ __('Formula Tersedia') }}</p><p class="mt-2 text-2xl font-semibold text-success-700 dark:text-success-300" x-text="summary.formula_ready"></p></div>
                <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-500/30 dark:bg-warning-500/10"><p class="text-theme-xs font-medium uppercase tracking-wide text-warning-600 dark:text-warning-400">{{ __('Formula Belum Ada') }}</p><p class="mt-2 text-2xl font-semibold text-warning-700 dark:text-warning-300" x-text="summary.missing_formula"></p></div>
                <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-500/30 dark:bg-brand-500/10"><p class="text-theme-xs font-medium uppercase tracking-wide text-brand-600 dark:text-brand-400">{{ __('FG Multi Level') }}</p><p class="mt-2 text-2xl font-semibold text-brand-700 dark:text-brand-300" x-text="summary.multi_level"></p></div>
            </div>

            <div class="border-b border-gray-200 px-6 pt-5 dark:border-gray-800">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div><h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Preview Unit Cost dan Unit Price') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Hasil Preview = Unit Cost tersimpan + PE pabrik terpilih.') }}</p></div>
                    <p class="text-sm font-medium text-brand-600 dark:text-brand-400">{{ __('Unit Price = Unit Cost + PE') }}</p>
                </div>
                <div class="grid grid-cols-2 gap-px overflow-hidden rounded-t-xl border border-b-0 border-gray-200 bg-gray-200 dark:border-gray-800 dark:bg-gray-800 sm:grid-cols-4" role="tablist" aria-label="{{ __('Pabrik') }}">
                    @foreach ($factories as $factory => $label)
                        <button type="button" role="tab" @click="selectedFactory = '{{ $factory }}'" :aria-selected="selectedFactory === '{{ $factory }}'" :class="selectedFactory === '{{ $factory }}' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800'" class="relative px-4 py-3 text-sm font-medium transition">{{ $label }}<span x-show="selectedFactory === '{{ $factory }}'" class="absolute inset-x-0 bottom-0 h-0.5 bg-brand-500" aria-hidden="true"></span></button>
                    @endforeach
                </div>
            </div>

            <div class="relative overflow-x-auto">
                <div x-show="loading" x-cloak class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 text-sm font-medium text-gray-600 dark:bg-gray-900/70 dark:text-gray-300">{{ __('Memuat data...') }}</div>
                <table class="min-w-[1180px] divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-start">{{ __('Select') }}</th>
                            <th class="px-5 py-3 text-start">{{ __('Code FG') }}</th><th class="px-5 py-3 text-start">{{ __('Description') }}</th><th class="px-5 py-3 text-center">{{ __('Level') }}</th><th class="px-5 py-3 text-center">{{ __('Komponen Formula') }}</th><th class="px-5 py-3 text-end">{{ __('Unit Cost') }}</th><th class="px-5 py-3 text-end">{{ __('PE') }}</th><th class="px-5 py-3 text-end">{{ __('Hasil Preview') }}</th><th class="px-5 py-3 text-end">{{ __('Unit Price Tersimpan') }}</th><th class="px-5 py-3 text-end">{{ __('Selisih') }}</th><th class="px-5 py-3 text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="row in finishedGoods" :key="row.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4"><label class="inline-flex items-center">
                                    <input type="checkbox" x-model="selectedIds" :value="String(row.id)" :disabled="calculating || loading" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900" />
                                    <span class="sr-only">{{ __('Select Finished Good') }} <span x-text="row.code"></span></span>
                                </label></td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90" x-text="row.code"></td>
                                <td class="min-w-64 px-5 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="row.description"></td>
                                <td class="px-5 py-4 text-center"><span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium" :class="row.multi_level === 'Y' ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'" x-text="row.multi_level"></span></td>
                                <td class="px-5 py-4 text-center text-sm text-gray-600 dark:text-gray-300" x-text="row.formula_items_count"></td>
                                <td class="px-5 py-4 text-end text-sm text-gray-600 dark:text-gray-300" x-text="formatAmount(unitCost(row))"></td>
                                <td class="px-5 py-4 text-end text-sm text-gray-600 dark:text-gray-300" x-text="formatAmount(pe(row))"></td>
                                <td class="px-5 py-4 text-end text-sm font-semibold text-brand-600 dark:text-brand-400" x-text="formatAmount(previewPrice(row))"></td>
                                <td class="px-5 py-4 text-end text-sm text-gray-600 dark:text-gray-300" x-text="formatAmount(storedPrice(row))"></td>
                                <td class="px-5 py-4 text-end text-sm" :class="difference(row) === 0 ? 'text-gray-500 dark:text-gray-400' : 'text-warning-600 dark:text-warning-400'" x-text="formatAmount(difference(row))"></td>
                                <td class="px-5 py-4 text-center"><span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium" :class="Number(row.formula_items_count) > 0 ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400'" x-text="Number(row.formula_items_count) > 0 ? '{{ __('Formula Tersedia') }}' : '{{ __('Formula Belum Ada') }}'"></span></td>
                            </tr>
                        </template>
                        <template x-if="finishedGoods.length === 0"><tr><td colspan="11" class="px-6 py-14 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Finished Good tidak ditemukan.') }}</td></tr></template>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="meta.total ? `{{ __('Menampilkan') }} ${meta.from}–${meta.to} {{ __('dari') }} ${meta.total} {{ __('data') }}` : '{{ __('Tidak ada data') }}'"></p>
                <div class="flex items-center gap-1">
                    <button type="button" @click="load(meta.current_page - 1)" :disabled="loading || meta.current_page <= 1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Sebelumnya') }}</button>
                    <template x-for="page in pages()" :key="page"><button type="button" @click="load(page)" :disabled="loading" :class="page === meta.current_page ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800'" class="size-9 rounded-lg border text-sm" x-text="page"></button></template>
                    <button type="button" @click="load(meta.current_page + 1)" :disabled="loading || meta.current_page >= meta.last_page" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Berikutnya') }}</button>
                </div>
            </div>

            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                <div class="mb-2 flex flex-wrap items-center gap-3" aria-live="polite">
                    <span>{{ __('5 records per page') }}</span>
                    <span><span x-text="selectedIds.length"></span> {{ __('FG selected across pages') }}</span>
                    <button type="button" @click="selectedIds = []" :disabled="calculating || selectedIds.length === 0" class="font-medium text-brand-600 disabled:opacity-40 dark:text-brand-400">{{ __('Clear selection') }}</button>
                </div>
                <p>{{ __('Only selected FG are calculated for Current, LE, Quarter 1–4 and all plants. Changing search, plant, period or Multi Level clears the selection.') }}</p>
                <p class="mt-1">{{ __('Select intermediate FG too if their prices need recalculation. Unselected intermediate FG use existing RM prices.') }}</p>
            </div>
        </section>
        <dialog x-ref="confirmCalculation" x-cloak @cancel.prevent="closeConfirmation()"
            @keydown.escape.window="if (confirmOpen) closeConfirmation()"
            @click="if ($event.target === $refs.confirmCalculation) closeConfirmation()"
            aria-labelledby="cost-confirm-title" aria-describedby="cost-confirm-description"
            class="fixed inset-0 m-auto max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-0 text-gray-800 shadow-theme-xl backdrop:bg-gray-950/50 backdrop:backdrop-blur-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white/90">
            <div class="p-6 sm:p-8">
                <h2 id="cost-confirm-title" class="text-xl font-semibold">{{ __('Confirm Unit Cost & Unit Price') }}</h2>
                <p id="cost-confirm-description" class="mt-3 text-sm text-gray-600 dark:text-gray-300"><span class="font-semibold" x-text="selectedIds.length"></span> {{ __('selected FG will be processed for Current, LE, Quarter 1–4 and all plants.') }}</p>
                <p x-show="calcMulti" class="mt-4 rounded-xl bg-warning-50 p-4 text-sm text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">{{ __('Selected multi-level FG are calculated first. Their Cikampek prices also update RM with the same code, including Current. Unselected FG are not recalculated.') }}</p>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('FG without a formula will be skipped.') }}</p>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button x-ref="cancelCalculation" type="button" @click="closeConfirmation()" :disabled="calculating" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Cancel') }}</button>
                    <button type="button" @click="calculate()" :disabled="calculating || selectedIds.length === 0" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 dark:bg-brand-600 dark:text-white dark:hover:bg-brand-700"><span x-text="calculating ? @js(__('Menghitung...')) : @js(__('Confirm & Save'))"></span></button>
                </div>
            </div>
        </dialog>
    </div>
@endsection
