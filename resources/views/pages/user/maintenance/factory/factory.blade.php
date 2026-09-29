@extends('layouts.app')

@section('content')
    <div class="min-w-0 max-w-full" x-data="{
        detailOpen: false,
        editOpen: false,
        deleteOpen: false,
        loading: false,
        error: '',
        factories: @js($factories->items()),
        meta: @js(['current_page' => $factories->currentPage(), 'last_page' => $factories->lastPage(), 'from' => $factories->firstItem() ?? 0, 'to' => $factories->lastItem() ?? 0, 'total' => $factories->total(), 'per_page' => $factories->perPage()]),
        search: @js($search),
        sort: @js($sort),
        direction: @js($direction),
        perPage: @js($perPage),
        selectedFactory: {},
        editOriginalCode: '',
        pages() { return Array.from({ length: this.meta.last_page }, (_, index) => index + 1); },
        async load(page = 1) {
            this.loading = true;
            this.error = '';
            const params = new URLSearchParams({ search: this.search, sort: this.sort, direction: this.direction, per_page: this.perPage, page });
            try {
                const response = await fetch(`{{ route('admin.maintenance.factory.data') }}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Request failed');
                const payload = await response.json();
                this.factories = payload.data;
                this.meta = payload.meta;
                window.history.replaceState({}, '', `{{ route('admin.maintenance.factory.index') }}?${params.toString()}`);
            } catch (error) { this.error = '{{ __('Data gagal dimuat. Silakan coba lagi.') }}'; }
            finally { this.loading = false; }
        },
        changeSort(field) { this.direction = this.sort === field && this.direction === 'asc' ? 'desc' : 'asc'; this.sort = field; this.load(1); },
        openDetail(factory) { this.selectedFactory = { ...factory }; this.detailOpen = true; },
        openEdit(factory) { this.selectedFactory = { ...factory }; this.editOriginalCode = factory.code; this.editOpen = true; },
        openDelete(factory) { this.selectedFactory = { ...factory }; this.deleteOpen = true; },
        saveEdit() {
            const index = this.factories.findIndex((factory) => factory.code === this.editOriginalCode);
            if (index !== -1) this.factories[index] = { ...this.selectedFactory };
            this.closeModals();
        },
        confirmDelete() {
            this.factories = this.factories.filter((factory) => factory.code !== this.selectedFactory.code);
            this.meta.total = Math.max(0, this.meta.total - 1);
            this.meta.to = Math.max(this.meta.from - 1, this.meta.to - 1);
            this.closeModals();
        },
        closeModals() { this.detailOpen = false; this.editOpen = false; this.deleteOpen = false; }
    }" @keydown.escape.window="closeModals()">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-5 border-b border-gray-200 px-6 py-5 dark:border-gray-800 xl:flex-row xl:items-center xl:justify-between">
                <div><h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Factory') }}</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Manajemen data factory dan relasi area.') }}</p></div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('admin.maintenance.factory.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg>{{ __('Tambah Factory') }}</a>
                    <form @submit.prevent="load(1)" class="flex gap-3"><div class="relative w-full sm:w-72"><svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg><input x-model="search" type="search" placeholder="{{ __('Cari factory code atau description...') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></div><div class="relative w-36"><select x-model="perPage" @change="load(1)" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 pe-9 text-sm text-gray-700 outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="5">5 / page</option><option value="10">10 / page</option><option value="20">20 / page</option></select></div></form>
                </div>
            </div>
            <div x-show="error" x-text="error" class="border-b border-error-200 bg-error-50 px-6 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>
            <div class="relative max-h-[420px] overflow-auto" :class="loading ? 'opacity-55' : ''">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800"><thead class="sticky top-0 z-20 bg-gray-50 shadow-theme-xs dark:bg-gray-900"><tr>
                    @foreach (['code' => __('Factory Code'), 'description' => __('Description')] as $field => $label)
                        <th class="px-6 py-3.5 text-start text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"><button type="button" @click="changeSort('{{ $field }}')" class="inline-flex items-center gap-2 hover:text-brand-500">{{ $label }}<span class="flex flex-col"><svg class="size-2.5" :class="sort === '{{ $field }}' && direction === 'asc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor"><path d="M5 0 10 6H0L5 0Z" /></svg><svg class="mt-0.5 size-2.5" :class="sort === '{{ $field }}' && direction === 'desc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor"><path d="m5 6 5-6H0l5 6Z" /></svg></span></button></th>
                    @endforeach
                    <th class="w-40 px-4 py-3.5 text-center text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Aksi') }}</th>
                </tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="factory in factories" :key="factory.code"><tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]"><td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90" x-text="factory.code"></td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="factory.description"></td><td class="px-4 py-4"><div class="flex items-center justify-center gap-2"><button type="button" @click="openDetail(factory)" class="inline-flex size-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-400" title="{{ __('Lihat detail') }}" aria-label="{{ __('Lihat detail') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.75 12s3.5-6 9.25-6 9.25 6 9.25 6-3.5 6-9.25 6S2.75 12 2.75 12Z"/><circle cx="12" cy="12" r="2.75" stroke-width="1.8"/></svg></button><button type="button" @click="openEdit(factory)" class="inline-flex size-9 items-center justify-center rounded-lg border border-brand-200 bg-brand-50 text-brand-600 transition hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.86 3.49 3.65 3.65M5 19l3.85-.77L19.74 7.34a1.5 1.5 0 0 0 0-2.12l-.96-.96a1.5 1.5 0 0 0-2.12 0L5.77 15.15 5 19Z" /></svg></button><button type="button" @click="openDelete(factory)" class="inline-flex size-9 items-center justify-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400" title="{{ __('Hapus') }}" aria-label="{{ __('Hapus') }}"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 7.5h15m-9-3h3m-7.5 3 .75 12h10.5l.75-12M9.5 11v5m5-5v5" /></svg></button></div></td></tr></template>
                    <template x-if="!loading && factories.length === 0"><tr><td colspan="3" class="px-6 py-14 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Data factory tidak ditemukan.') }}</td></tr></template>
                </tbody></table>
            </div>
            <div class="border-t border-gray-200 px-6 py-3 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ __('Menampilkan') }} <span class="font-medium text-gray-700 dark:text-gray-300"><span x-text="meta.from"></span>-<span x-text="meta.to"></span></span> {{ __('dari') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="meta.total"></span> {{ __('data') }}</div>
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800"><button type="button" @click="load(meta.current_page - 1)" :disabled="loading || meta.current_page === 1" class="h-10 justify-self-start rounded-lg border border-gray-300 px-4 text-sm text-gray-700 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">{{ __('Previous') }}</button><div class="flex gap-1.5"><template x-for="page in pages()" :key="page"><button type="button" @click="load(page)" x-text="page" class="inline-flex size-10 items-center justify-center rounded-lg text-sm font-medium" :class="page === meta.current_page ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'"></button></template></div><button type="button" @click="load(meta.current_page + 1)" :disabled="loading || meta.current_page === meta.last_page" class="h-10 justify-self-end rounded-lg border border-gray-300 px-4 text-sm text-gray-700 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">{{ __('Next') }}</button></div>
        </section>

        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="factory-detail-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div>
            <div x-show="detailOpen" x-transition class="relative max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="mb-5 flex items-start justify-between gap-4"><div><h2 id="factory-detail-title" class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Detail Factory') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"><span x-text="selectedFactory.code"></span> · <span x-text="selectedFactory.description"></span></p></div><button type="button" @click="closeModals()" class="flex size-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Tutup') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg></button></div>
                <x-factory.form-fields model="selectedFactory" :area-options="$areaOptions" readonly />
                <div class="mt-6 flex justify-end"><button type="button" @click="closeModals()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Tutup') }}</button></div>
            </div>
        </div>

        <div x-show="editOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="factory-edit-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div>
            <div x-show="editOpen" x-transition class="relative max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="mb-5 flex items-start justify-between gap-4"><div><h2 id="factory-edit-title" class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Edit Factory') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Perubahan masih berupa simulasi UI.') }}</p></div><button type="button" @click="closeModals()" class="flex size-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Tutup') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg></button></div>
                <form @submit.prevent="saveEdit()">
                    <x-factory.form-fields model="selectedFactory" :area-options="$areaOptions" />
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><button type="button" @click="closeModals()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Batal') }}</button><button type="submit" class="h-11 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('Simpan Perubahan') }}</button></div>
                </form>
            </div>
        </div>

        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="alertdialog" aria-modal="true" aria-labelledby="factory-delete-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div>
            <div x-show="deleteOpen" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15 dark:text-error-400"><svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5m0 3.5v.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
                <h2 id="factory-delete-title" class="mt-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Hapus Data Factory?') }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Data') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedFactory.code"></span> {{ __('akan dihapus. Aksi ini belum terhubung ke backend.') }}</p>
                <div class="mt-6 flex justify-center gap-3"><button type="button" @click="closeModals()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Batal') }}</button><button type="button" @click="confirmDelete()" class="h-11 rounded-lg bg-error-500 px-5 text-sm font-medium text-white transition hover:bg-error-600">{{ __('Ya, Hapus') }}</button></div>
            </div>
        </div>
    </div>
@endsection
