@extends('layouts.app')

@section('content')
    <div class="min-w-0 max-w-full" x-data="{
        deleteOpen: false,
        detailOpen: false,
        loading: false,
        error: '',
        rawMaterials: @js($rawMaterials->items()),
        meta: @js([
            'current_page' => $rawMaterials->currentPage(),
            'last_page' => $rawMaterials->lastPage(),
            'from' => $rawMaterials->firstItem() ?? 0,
            'to' => $rawMaterials->lastItem() ?? 0,
            'total' => $rawMaterials->total(),
            'per_page' => $rawMaterials->perPage(),
        ]),
        search: @js($search),
        selectedCurrency: @js($selectedCurrency),
        sort: @js($sort),
        direction: @js($direction),
        perPage: @js($perPage),
        selectedItem: { code: '', description: '', unit: '', wastage_all: 0, material_id: '', currency_type: 'Rp', type_rm: '' },
        paginationItems() {
            const current = Number(this.meta.current_page);
            const last = Number(this.meta.last_page);

            if (last <= 7) {
                return Array.from({ length: last }, (_, index) => ({ key: `page-${index + 1}`, label: index + 1, page: index + 1 }));
            }

            const pages = new Set([1, last]);

            for (let page = Math.max(2, current - 1); page <= Math.min(last - 1, current + 1); page++) {
                pages.add(page);
            }

            if (current <= 4) [2, 3, 4, 5].forEach((page) => pages.add(page));
            if (current >= last - 3) [last - 4, last - 3, last - 2, last - 1].forEach((page) => pages.add(page));

            const sortedPages = [...pages].sort((first, second) => first - second);
            const items = [];

            sortedPages.forEach((page, index) => {
                const previousPage = sortedPages[index - 1];

                if (previousPage && page - previousPage > 1) {
                    items.push({ key: `ellipsis-${previousPage}-${page}`, label: '…', page: null });
                }

                items.push({ key: `page-${page}`, label: page, page });
            });

            return items;
        },
        formatPrice(value) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value) || 0); },
        price(period, field) { return this.selectedItem.prices?.find(price => price.period === period)?.[field] ?? 0; },
        async load(page = 1) {
            this.loading = true;
            this.error = '';
            const params = new URLSearchParams({ search: this.search, currency_type: this.selectedCurrency, sort: this.sort, direction: this.direction, per_page: this.perPage, page });
            try {
                const response = await fetch(`{{ route('admin.maintenance.raw-material.data') }}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Request failed');
                const payload = await response.json();
                this.rawMaterials = payload.data;
                this.meta = payload.meta;
                window.history.replaceState({}, '', `{{ route('admin.maintenance.raw-material.index') }}?${params.toString()}`);
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
        resetFilters() { this.search = ''; this.selectedCurrency = ''; this.load(1); },
        openDelete(item) { this.selectedItem = { ...item }; this.deleteOpen = true; },
        openDetail(item) { this.selectedItem = { ...item }; this.detailOpen = true; },
        closeModals() { this.deleteOpen = false; this.detailOpen = false; },
        async deleteItem() { await this.mutate(`{{ url('/admin/maintenance/raw-material') }}/${this.selectedItem.id}`, 'DELETE'); },
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
    }" @keydown.escape.window="closeModals()">
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
        @endif
        <section class="min-w-0 max-w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-5 border-b border-gray-200 px-6 py-5 dark:border-gray-800 xl:flex-row xl:items-center xl:justify-between">
                <div><h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Raw Material') }}</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Manajemen data master raw material.') }}</p></div>
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <a href="{{ route('admin.maintenance.raw-material.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/20"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg>{{ __('Tambah Raw Material') }}</a>
                    <form @submit.prevent="load(1)" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative w-full sm:w-64"><span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg></span><input x-model="search" type="search" placeholder="{{ __('Cari code RM, description, atau ID...') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></div>
                        <div class="relative w-full sm:w-40"><select x-model="selectedCurrency" @change="load(1)" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 pe-9 text-sm text-gray-700 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="">{{ __('Semua Currency') }}</option><option value="Rp">Rp</option><option value="USD">USD</option></select><svg class="pointer-events-none absolute end-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg></div>
                        <div class="relative w-full sm:w-36"><select x-model="perPage" @change="load(1)" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 pe-9 text-sm text-gray-700 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="5">5 / page</option><option value="10">10 / page</option><option value="20">20 / page</option></select><svg class="pointer-events-none absolute end-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg></div>
                        <template x-if="search !== '' || selectedCurrency !== ''"><button type="button" @click="resetFilters()" class="inline-flex size-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400" aria-label="{{ __('Reset filter') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg></button></template>
                    </form>
                </div>
            </div>

            <div x-show="error" x-text="error" class="border-b border-error-200 bg-error-50 px-6 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>
            <div class="relative max-h-[420px] overflow-auto" :class="loading ? 'opacity-55' : ''">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="sticky top-0 z-20 bg-gray-50 shadow-theme-xs dark:bg-gray-900"><tr>
                        @foreach (['code' => __('Code RM'), 'description' => __('Description'), 'unit' => __('Unit'), 'wastage_all' => __('Wastage All'), 'material_id' => __('ID'), 'currency_type' => __('Currency Type'), 'type_rm' => __('Type RM')] as $field => $label)
                            <th class="min-w-32 px-6 py-3.5 text-start text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"><button type="button" @click="changeSort('{{ $field }}')" class="inline-flex items-center gap-2 whitespace-nowrap transition hover:text-brand-500">{{ $label }}<span class="flex flex-col"><svg class="size-2.5" :class="sort === '{{ $field }}' && direction === 'asc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor"><path d="M5 0 10 6H0L5 0Z" /></svg><svg class="mt-0.5 size-2.5" :class="sort === '{{ $field }}' && direction === 'desc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor"><path d="m5 6 5-6H0l5 6Z" /></svg></span></button></th>
                        @endforeach
                        <th class="sticky end-0 z-30 min-w-52 border-s border-gray-200 bg-gray-50 px-6 py-3.5 text-start text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">{{ __('Aksi') }}</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="item in rawMaterials" :key="item.code"><tr class="group hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90" x-text="item.code"></td>
                            <td class="min-w-64 px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="item.description"></td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="item.unit"></td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="item.wastage_all"></td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="item.material_id"></td>
                            <td class="whitespace-nowrap px-6 py-4"><span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400" x-text="item.currency_type"></span></td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="item.type_rm"></td>
                            <td class="sticky end-0 z-10 min-w-72 whitespace-nowrap border-s border-gray-200 bg-white px-6 py-4 group-hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:group-hover:bg-gray-900"><div class="flex gap-2"><button type="button" @click="openDetail(item)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M2.75 12s3.25-5.5 9.25-5.5 9.25 5.5 9.25 5.5-3.25 5.5-9.25 5.5S2.75 12 2.75 12Z"/><circle cx="12" cy="12" r="2.25" stroke-width="1.8"/></svg>{{ __('Detail') }}</button><a :href="`{{ url('/admin/maintenance/raw-material') }}/${item.id}/edit`" class="inline-flex h-9 items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-3 text-theme-xs font-medium text-brand-600 hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.86 3.49 3.65 3.65M5 19l3.85-.77L19.74 7.34a1.5 1.5 0 0 0 0-2.12l-.96-.96a1.5 1.5 0 0 0-2.12 0L5.77 15.15 5 19Z" /></svg>{{ __('Edit') }}</a><button type="button" @click="openDelete(item)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-error-200 bg-error-50 px-3 text-theme-xs font-medium text-error-600 hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 7.5h15m-9-3h3m-7.5 3 .75 12h10.5l.75-12M9.5 11v5m5-5v5" /></svg>{{ __('Hapus') }}</button></div></td>
                        </tr></template>
                        <template x-if="!loading && rawMaterials.length === 0"><tr><td colspan="8" class="px-6 py-14 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Data raw material tidak ditemukan.') }}</td></tr></template>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-6 py-3 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ __('Menampilkan') }} <span class="font-medium text-gray-700 dark:text-gray-300"><span x-text="meta.from"></span>–<span x-text="meta.to"></span></span> {{ __('dari') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="meta.total"></span> {{ __('data') }}</div>
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                <button type="button" @click="load(meta.current_page - 1)" :disabled="loading || meta.current_page === 1" class="inline-flex h-10 items-center gap-2 justify-self-start rounded-lg border px-4 text-sm transition disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 enabled:border-gray-300 enabled:font-medium enabled:text-gray-700 enabled:hover:bg-gray-50 dark:disabled:border-gray-800 dark:enabled:border-gray-700 dark:enabled:text-gray-300"><svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>{{ __('Previous') }}</button>
                <div class="flex min-w-0 items-center justify-center gap-1 sm:gap-1.5">
                    <template x-for="item in paginationItems()" :key="item.key">
                        <button type="button" @click="item.page && load(item.page)" :disabled="loading || !item.page" x-text="item.label"
                            class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg text-sm font-medium transition sm:size-10"
                            :class="item.page === meta.current_page ? 'bg-brand-500 text-white' : item.page ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800' : 'cursor-default text-gray-400 dark:text-gray-600'"
                            :aria-current="item.page === meta.current_page ? 'page' : null"
                            :aria-label="item.page ? `{{ __('Halaman') }} ${item.page}` : null"></button>
                    </template>
                </div>
                <button type="button" @click="load(meta.current_page + 1)" :disabled="loading || meta.current_page === meta.last_page" class="inline-flex h-10 items-center gap-2 justify-self-end rounded-lg border px-4 text-sm transition disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 enabled:border-gray-300 enabled:font-medium enabled:text-gray-700 enabled:hover:bg-gray-50 dark:disabled:border-gray-800 dark:enabled:border-gray-700 dark:enabled:text-gray-300">{{ __('Next') }}<svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m9 18 6-6-6-6" /></svg></button>
            </div>
        </section>

        @php($detailPeriods = ['current' => __('Current'), 'le' => __('LE'), 'qtr_1' => __('Qtr 1'), 'qtr_2' => __('Qtr 2'), 'qtr_3' => __('Qtr 3'), 'qtr_4' => __('Qtr 4')])
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="raw-material-detail-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div>
            <div x-show="detailOpen" x-transition class="relative max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div><h2 id="raw-material-detail-title" class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Detail Raw Material') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"><span x-text="selectedItem.code"></span> · <span x-text="selectedItem.description"></span></p></div>
                    <button type="button" @click="closeModals()" class="flex size-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Tutup') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg></button>
                </div>
                <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]"><p class="text-theme-xs uppercase text-gray-500 dark:text-gray-400">{{ __('ID') }}</p><p class="mt-1 font-medium text-gray-800 dark:text-white/90" x-text="selectedItem.material_id"></p></div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]"><p class="text-theme-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Type RM') }}</p><p class="mt-1 font-medium text-gray-800 dark:text-white/90" x-text="selectedItem.type_rm"></p></div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]"><p class="text-theme-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Currency Type') }}</p><p class="mt-1 font-medium text-gray-800 dark:text-white/90" x-text="selectedItem.currency_type"></p></div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]"><p class="text-theme-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Wastage All') }}</p><p class="mt-1 font-medium text-gray-800 dark:text-white/90" x-text="selectedItem.wastage_all"></p></div>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-400">{{ __('Periode') }}</th><th class="px-4 py-3 text-end font-medium text-gray-600 dark:text-gray-400">{{ __('USD') }}</th><th class="px-4 py-3 text-end font-medium text-gray-600 dark:text-gray-400">{{ __('Rupiah') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($detailPeriods as $period => $periodLabel)
                                <tr><td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">{{ $periodLabel }}</td><td class="px-4 py-3 text-end text-gray-600 dark:text-gray-300" x-text="formatPrice(price('{{ $period }}', 'usd_amount'))"></td><td class="px-4 py-3 text-end text-gray-600 dark:text-gray-300" x-text="formatPrice(price('{{ $period }}', 'rupiah_amount'))"></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex justify-end gap-3"><button type="button" @click="closeModals()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Tutup') }}</button><a :href="`{{ url('/admin/maintenance/raw-material') }}/${selectedItem.id}/edit`" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">{{ __('Edit Raw Material') }}</a></div>
            </div>
        </div>

        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4" role="alertdialog" aria-modal="true"><div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div><div x-show="deleteOpen" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-7 text-center shadow-theme-xl dark:bg-gray-900"><div class="mx-auto flex size-14 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15"><svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5m0 3.5v.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div><h2 class="mt-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Hapus Raw Material?') }}</h2><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Data') }} <span class="font-medium" x-text="selectedItem.code"></span> {{ __('akan dihapus permanen.') }}</p><div class="mt-6 flex justify-center gap-3"><button type="button" @click="closeModals()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="button" @click="deleteItem()" :disabled="loading" class="inline-flex h-11 items-center gap-2 rounded-lg bg-error-500 px-5 text-sm font-medium text-white hover:bg-error-600 disabled:cursor-not-allowed disabled:opacity-50"><svg x-show="loading" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/></svg><span x-text="loading ? '{{ __('Menghapus...') }}' : '{{ __('Ya, Hapus') }}'"></span></button></div></div></div>
    </div>
@endsection
