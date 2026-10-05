@extends('layouts.app')

@section('content')
    <div class="min-w-0 max-w-full"
        x-data="{
            deleteOpen: false,
            detailOpen: false,
            loading: false,
            error: '',
            finishedGoods: @js($finishedGoods->items()),
            meta: @js([
                'current_page' => $finishedGoods->currentPage(),
                'last_page' => $finishedGoods->lastPage(),
                'from' => $finishedGoods->firstItem() ?? 0,
                'to' => $finishedGoods->lastItem() ?? 0,
                'total' => $finishedGoods->total(),
                'per_page' => $finishedGoods->perPage(),
            ]),
            search: @js($search),
            sort: @js($sort),
            direction: @js($direction),
            perPage: @js($perPage),
            selectedItem: { code: '', description: '', product_type_1: 0, product_type_2: 0, multi_level: 'N', active: 'Y' },
            pages() {
                return Array.from({ length: this.meta.last_page }, (_, index) => index + 1);
            },
            formatPrice(value) {
                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value) || 0);
            },
            async load(page = 1) {
                this.loading = true;
                this.error = '';
                const params = new URLSearchParams({
                    search: this.search,
                    sort: this.sort,
                    direction: this.direction,
                    per_page: this.perPage,
                    page: page,
                });

                try {
                    const response = await fetch(`{{ route('admin.maintenance.finished-good.data') }}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) throw new Error('Request failed');
                    const payload = await response.json();
                    this.finishedGoods = payload.data;
                    this.meta = payload.meta;
                    window.history.replaceState({}, '', `{{ route('admin.maintenance.finished-good.index') }}?${params.toString()}`);
                } catch (error) {
                    this.error = '{{ __('Data gagal dimuat. Silakan coba lagi.') }}';
                } finally {
                    this.loading = false;
                }
            },
            changeSort(field) {
                this.direction = this.sort === field && this.direction === 'asc' ? 'desc' : 'asc';
                this.sort = field;
                this.load(1);
            },
            resetFilters() {
                this.search = '';
                this.load(1);
            },
            openDelete(item) {
                this.selectedItem = { ...item };
                this.deleteOpen = true;
            },
            openDetail(item) {
                this.selectedItem = { ...item };
                this.detailOpen = true;
            },
            closeModals() {
                this.deleteOpen = false;
                this.detailOpen = false;
            },
            async deleteItem() {
                await this.mutate(`{{ url('/admin/maintenance/finished-good') }}/${this.selectedItem.id}`, 'DELETE');
            },
            async mutate(url, method, body = null) {
                this.loading = true;
                this.error = '';
                try {
                    const response = await fetch(url, { method, headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: body ? JSON.stringify(body) : null });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(Object.values(payload.errors ?? {}).flat()[0] ?? payload.message);
                    this.closeModals();
                    await this.load(this.meta.current_page);
                } catch (error) {
                    this.error = error.message || '{{ __('Data gagal diproses.') }}';
                } finally {
                    this.loading = false;
                }
            }
        }"
        @keydown.escape.window="closeModals()">
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
        @endif
        <section class="min-w-0 max-w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-5 border-b border-gray-200 px-6 py-5 dark:border-gray-800 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Finished Good') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Manajemen data master finished good.') }}</p>
                </div>

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <a href="{{ route('admin.maintenance.finished-good.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/20">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg>
                        {{ __('Tambah Finished Good') }}
                    </a>

                    <form @submit.prevent="load(1)" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative w-full sm:w-64">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg></span>
                            <input x-model="search" name="search" type="search" placeholder="{{ __('Cari code FG atau description...') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>

                        <div class="relative w-full sm:w-36">
                            <select x-model="perPage" @change="load(1)" name="per_page" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 pe-9 text-sm text-gray-700 outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="5">5 / page</option><option value="10">10 / page</option><option value="20">20 / page</option>
                            </select>
                            <svg class="pointer-events-none absolute end-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </div>

                        <template x-if="search !== ''"><button type="button" @click="resetFilters()" class="inline-flex size-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800" aria-label="{{ __('Reset pencarian') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg></button></template>
                    </form>
                </div>
            </div>

            <div x-show="error" x-text="error" class="border-b border-error-200 bg-error-50 px-6 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>

            <div class="relative max-h-[420px] overflow-auto" :class="loading ? 'opacity-55' : ''">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    @php
                        $standardColumns = [
                            'description_1' => __('Description 1'),
                            'batch' => __('Batch'),
                            'selling_price' => __('Hrg Jual'),
                            'multi_level' => __('Multi Level'),
                            'active' => __('Active'),
                            'unit_cost_current' => __('Unit Cost Current'),
                            'unit_price_current' => __('Unit Price Current'),
                            'unit_cost_le' => __('Unit Cost LE'),
                            'unit_price_le' => __('Unit Price LE'),
                            'unit_cost_qtr_1' => __('Unit Cost Qtr 1'),
                            'unit_price_qtr_1' => __('Unit Price Qtr 1'),
                            'unit_cost_qtr_2' => __('Unit Cost Qtr 2'),
                            'unit_price_qtr_2' => __('Unit Price Qtr 2'),
                            'unit_cost_qtr_3' => __('Unit Cost Qtr 3'),
                            'unit_price_qtr_3' => __('Unit Price Qtr 3'),
                            'unit_cost_qtr_4' => __('Unit Cost Qtr 4'),
                            'unit_price_qtr_4' => __('Unit Price Qtr 4'),
                        ];
                    @endphp
                    <thead class="sticky top-0 z-20 bg-gray-50 shadow-theme-xs dark:bg-gray-900">
                        <tr>
                            @foreach (['code' => __('Code FG'), 'description' => __('Description')] as $field => $label)
                                <th rowspan="2" class="sticky {{ $field === 'code' ? 'start-0 z-40 min-w-36' : 'start-36 z-30 min-w-72' }} bg-gray-50 px-6 py-3.5 text-start align-middle text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    <button type="button" @click="changeSort('{{ $field }}')" class="inline-flex items-center gap-2 transition hover:text-brand-500 dark:hover:text-brand-400">{{ $label }}<span class="flex flex-col"><svg class="size-2.5" :class="sort === '{{ $field }}' && direction === 'asc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor"><path d="M5 0 10 6H0L5 0Z" /></svg><svg class="mt-0.5 size-2.5" :class="sort === '{{ $field }}' && direction === 'desc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor"><path d="m5 6 5-6H0l5 6Z" /></svg></span></button>
                                </th>
                            @endforeach
                            <th rowspan="2" class="min-w-52 px-6 py-3.5 text-start align-middle text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"><button type="button" @click="changeSort('description_1')" class="inline-flex items-center gap-2 transition hover:text-brand-500 dark:hover:text-brand-400">{{ __('Description 1') }}</button></th>
                            <th colspan="2" class="border-b border-gray-200 px-6 py-2.5 text-center text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ __('Product Type') }}</th>
                            @foreach (array_slice($standardColumns, 1, null, true) as $field => $label)
                                <th rowspan="2" class="min-w-36 px-6 py-3.5 text-start align-middle text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"><button type="button" @click="changeSort('{{ $field }}')" class="inline-flex items-center gap-2 whitespace-nowrap transition hover:text-brand-500 dark:hover:text-brand-400">{{ $label }}</button></th>
                            @endforeach
                            <th rowspan="2" class="sticky end-0 z-40 min-w-52 border-s border-gray-200 bg-gray-50 px-6 py-3.5 text-start align-middle text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">{{ __('Aksi') }}</th>
                        </tr>
                        <tr>
                            @foreach (['product_type_1' => '1', 'product_type_2' => '2'] as $field => $label)
                                <th class="min-w-24 px-6 py-2.5 text-center text-theme-xs font-medium text-gray-500 dark:text-gray-400"><button type="button" @click="changeSort('{{ $field }}')" class="inline-flex items-center gap-2 transition hover:text-brand-500 dark:hover:text-brand-400">{{ $label }}</button></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="item in finishedGoods" :key="item.code">
                            <tr class="group transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="sticky start-0 z-10 min-w-36 whitespace-nowrap bg-white px-6 py-4 text-sm font-medium text-gray-800 group-hover:bg-gray-50 dark:bg-gray-900 dark:text-white/90 dark:group-hover:bg-gray-900" x-text="item.code"></td>
                                <td class="sticky start-36 z-10 min-w-72 bg-white px-6 py-4 text-sm text-gray-600 group-hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:group-hover:bg-gray-900" x-text="item.description"></td>
                                <td class="min-w-52 px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="item.description_1"></td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300" x-text="item.product_type_1"></td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300" x-text="item.product_type_2"></td>
                                @foreach (array_slice($standardColumns, 1, null, true) as $field => $label)
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        @if (in_array($field, ['multi_level', 'active'], true))
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium" :class="item.{{ $field }} === 'Y' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'" x-text="item.{{ $field }}"></span>
                                        @elseif (str_contains($field, 'price') || str_contains($field, 'cost'))
                                            <span x-text="formatPrice(item.{{ $field }})"></span>
                                        @else
                                            <span x-text="item.{{ $field }}"></span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="sticky end-0 z-10 min-w-72 whitespace-nowrap border-s border-gray-200 bg-white px-6 py-4 group-hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:group-hover:bg-gray-900"><div class="flex items-center gap-2"><button type="button" @click="openDetail(item)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M2.75 12s3.25-5.5 9.25-5.5 9.25 5.5 9.25 5.5-3.25 5.5-9.25 5.5S2.75 12 2.75 12Z"/><circle cx="12" cy="12" r="2.25" stroke-width="1.8"/></svg>{{ __('Detail') }}</button><a :href="`{{ url('/admin/maintenance/finished-good') }}/${item.id}/edit`" class="inline-flex h-9 items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-3 text-theme-xs font-medium text-brand-600 transition hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.86 3.49 3.65 3.65M5 19l3.85-.77L19.74 7.34a1.5 1.5 0 0 0 0-2.12l-.96-.96a1.5 1.5 0 0 0-2.12 0L5.77 15.15 5 19Z" /></svg>{{ __('Edit') }}</a><button type="button" @click="openDelete(item)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-error-200 bg-error-50 px-3 text-theme-xs font-medium text-error-600 transition hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 7.5h15m-9-3h3m-7.5 3 .75 12h10.5l.75-12M9.5 11v5m5-5v5" /></svg>{{ __('Hapus') }}</button></div></td>
                            </tr>
                        </template>
                        <template x-if="!loading && finishedGoods.length === 0"><tr><td colspan="22" class="px-6 py-14 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Data finished good tidak ditemukan.') }}</td></tr></template>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-6 py-3 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ __('Menampilkan') }} <span class="font-medium text-gray-700 dark:text-gray-300"><span x-text="meta.from"></span>–<span x-text="meta.to"></span></span> {{ __('dari') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="meta.total"></span> {{ __('data') }}</div>
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                <button type="button" @click="load(meta.current_page - 1)" :disabled="loading || meta.current_page === 1" class="inline-flex h-10 items-center gap-2 justify-self-start rounded-lg border px-4 text-sm transition disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 enabled:border-gray-300 enabled:font-medium enabled:text-gray-700 enabled:hover:bg-gray-50 dark:disabled:border-gray-800 dark:disabled:text-gray-600 dark:enabled:border-gray-700 dark:enabled:text-gray-300"><svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>{{ __('Previous') }}</button>
                <div class="flex items-center justify-center gap-1.5"><template x-for="page in pages()" :key="page"><button type="button" @click="load(page)" :disabled="loading" x-text="page" class="inline-flex size-10 items-center justify-center rounded-lg text-sm font-medium transition" :class="page === meta.current_page ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'"></button></template></div>
                <button type="button" @click="load(meta.current_page + 1)" :disabled="loading || meta.current_page === meta.last_page" class="inline-flex h-10 items-center gap-2 justify-self-end rounded-lg border px-4 text-sm transition disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 enabled:border-gray-300 enabled:font-medium enabled:text-gray-700 enabled:hover:bg-gray-50 dark:disabled:border-gray-800 dark:disabled:text-gray-600 dark:enabled:border-gray-700 dark:enabled:text-gray-300">{{ __('Next') }}<svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m9 18 6-6-6-6" /></svg></button>
            </div>
        </section>

        @php
            $detailPeriods = ['current' => __('Current'), 'le' => __('LE'), 'qtr_1' => __('Qtr 1'), 'qtr_2' => __('Qtr 2'), 'qtr_3' => __('Qtr 3'), 'qtr_4' => __('Qtr 4')];
            $detailFactories = ['cikampek' => __('Cikampek'), 'semarang' => __('Semarang'), 'surabaya' => __('Surabaya'), 'palembang' => __('Palembang')];
        @endphp
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="finished-good-detail-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div>
            <div x-show="detailOpen" x-transition class="relative max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="finished-good-detail-title" class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Detail Finished Good') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400"><span x-text="selectedItem.code"></span> · <span x-text="selectedItem.description"></span></p>
                    </div>
                    <button type="button" @click="closeModals()" class="flex size-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Tutup') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg></button>
                </div>

                <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($detailFactories as $factory => $factoryLabel)
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                            <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('PE') }} {{ $factoryLabel }}</p>
                            <p class="mt-2 text-lg font-semibold text-gray-800 dark:text-white/90" x-text="formatPrice(selectedItem.pe_{{ $factory }})"></p>
                        </div>
                    @endforeach
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-[900px] w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('Periode') }}</th>
                                    <th class="px-4 py-3 text-end font-medium text-gray-600 dark:text-gray-400">{{ __('Unit Cost') }}</th>
                                    @foreach ($detailFactories as $factoryLabel)
                                        <th class="px-4 py-3 text-end font-medium text-gray-600 dark:text-gray-400">{{ __('Unit Price') }} {{ $factoryLabel }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($detailPeriods as $period => $periodLabel)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">{{ $periodLabel }}</td>
                                        <td class="px-4 py-3 text-end text-gray-600 dark:text-gray-300" x-text="formatPrice(selectedItem.unit_cost_{{ $period }})"></td>
                                        @foreach ($detailFactories as $factory => $factoryLabel)
                                            @php($detailField = $factory === 'cikampek' ? "unit_price_{$period}" : "unit_price_{$factory}_{$period}")
                                            <td class="px-4 py-3 text-end text-gray-600 dark:text-gray-300" x-text="formatPrice(selectedItem.{{ $detailField }})"></td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="closeModals()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Tutup') }}</button>
                    <a :href="`{{ url('/admin/maintenance/finished-good') }}/${selectedItem.id}/edit`" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Edit Finished Good') }}</a>
                </div>
            </div>
        </div>

        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="alertdialog" aria-modal="true" aria-labelledby="delete-finished-good-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div>
            <div x-show="deleteOpen" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15 dark:text-error-400"><svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5m0 3.5v.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
                <h2 id="delete-finished-good-title" class="mt-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Hapus Finished Good?') }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Data') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedItem.code"></span> {{ __('akan dihapus permanen.') }}</p>
                <div class="mt-6 flex justify-center gap-3"><button type="button" @click="closeModals()" :disabled="loading" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="button" @click="deleteItem()" :disabled="loading" class="inline-flex h-11 items-center gap-2 rounded-lg bg-error-500 px-5 text-sm font-medium text-white hover:bg-error-600 disabled:cursor-not-allowed disabled:opacity-50"><svg x-show="loading" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/></svg><span x-text="loading ? '{{ __('Menghapus...') }}' : '{{ __('Ya, Hapus') }}'"></span></button></div>
            </div>
        </div>
    </div>
@endsection
