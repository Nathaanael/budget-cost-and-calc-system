@extends('layouts.app')

@section('content')
    @php
        $leFields = [
            'le_july' => __('LE Juli'), 'le_august' => 'LE Agustus', 'le_september' => 'LE September',
            'le_october' => 'LE Oktober', 'le_november' => 'LE November', 'le_december' => 'LE Desember',
        ];
        $monthFields = [
            'january' => 'Januari', 'february' => 'Februari', 'march' => 'Maret', 'april' => 'April',
            'may' => 'Mei', 'june' => 'Juni', 'july' => 'Juli', 'august' => 'Agustus',
            'september' => 'September', 'october' => 'Oktober', 'november' => 'November', 'december' => 'Desember',
        ];
    @endphp

    <div class="min-w-0 max-w-full" x-data="{
        editOpen: false, deleteOpen: false, loading: false, error: '',
        volumes: @js($volumes->items()),
        meta: @js(['current_page' => $volumes->currentPage(), 'last_page' => $volumes->lastPage(), 'from' => $volumes->firstItem() ?? 0, 'to' => $volumes->lastItem() ?? 0, 'total' => $volumes->total(), 'per_page' => $volumes->perPage()]),
        search: @js($search), sort: @js($sort), direction: @js($direction), perPage: @js($perPage),
        selectedVolume: {},
        formatVolume(value) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value || 0));
        },
        paginationItems() {
            const current = Number(this.meta.current_page);
            const last = Number(this.meta.last_page);

            if (last <= 7) {
                return Array.from({ length: last }, (_, index) => ({ key: `page-${index + 1}`, label: index + 1, page: index + 1 }));
            }

            const pages = new Set([1, last]);

            for (let page = Math.max(2, current - 1); page <= Math.min(last - 1, current + 1); page++) pages.add(page);
            if (current <= 4) [2, 3, 4, 5].forEach((page) => pages.add(page));
            if (current >= last - 3) [last - 4, last - 3, last - 2, last - 1].forEach((page) => pages.add(page));

            const sortedPages = [...pages].sort((first, second) => first - second);
            const items = [];

            sortedPages.forEach((page, index) => {
                const previousPage = sortedPages[index - 1];
                if (previousPage && page - previousPage > 1) items.push({ key: `ellipsis-${previousPage}-${page}`, label: '…', page: null });
                items.push({ key: `page-${page}`, label: page, page });
            });

            return items;
        },
        async load(page = 1) {
            this.loading = true; this.error = '';
            const params = new URLSearchParams({ search: this.search, sort: this.sort, direction: this.direction, per_page: this.perPage, page });
            try {
                const response = await fetch(`{{ route('admin.entry.volume-noodle.data') }}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Request failed');
                const payload = await response.json(); this.volumes = payload.data; this.meta = payload.meta;
                window.history.replaceState({}, '', `{{ route('admin.entry.volume-noodle.index') }}?${params.toString()}`);
            } catch (error) { this.error = '{{ __('Data gagal dimuat. Silakan coba lagi.') }}'; }
            finally { this.loading = false; }
        },
        changeSort(field) { this.direction = this.sort === field && this.direction === 'asc' ? 'desc' : 'asc'; this.sort = field; this.load(1); },
        keyOf(volume) { return volume.id; },
        openEdit(volume) { this.selectedVolume = { ...volume }; this.editOpen = true; },
        openDelete(volume) { this.selectedVolume = { ...volume }; this.deleteOpen = true; },
        async saveEdit() { await this.mutate(`{{ url('/admin/entry/volume-noodle') }}/${this.selectedVolume.id}`, 'PUT', this.selectedVolume); },
        async confirmDelete() { await this.mutate(`{{ url('/admin/entry/volume-noodle') }}/${this.selectedVolume.id}`, 'DELETE'); },
        async mutate(url, method, body = null) {
            this.loading = true; this.error = '';
            try {
                const response = await fetch(url, { method, headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: body ? JSON.stringify(body) : null });
                const payload = await response.json();
                if (!response.ok) throw new Error(Object.values(payload.errors ?? {}).flat()[0] ?? payload.message);
                this.closeModals(); await this.load(this.meta.current_page);
            } catch (error) { this.error = error.message || '{{ __('Data gagal diproses.') }}'; }
            finally { this.loading = false; }
        },
        closeModals() { this.editOpen = false; this.deleteOpen = false; }
    }" @keydown.escape.window="closeModals()">
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
        @endif
        <section class="min-w-0 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-5 border-b border-gray-200 px-6 py-5 dark:border-gray-800 xl:flex-row xl:items-center xl:justify-between">
                <div><h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Volume Noodle') }}</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Entry LE dan volume bulanan per area dan noodle.') }}</p></div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center"><a href="{{ route('admin.entry.volume-noodle.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg>{{ __('Tambah Volume') }}</a><form @submit.prevent="load(1)" class="flex gap-3"><div class="relative w-full sm:w-72"><svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg><input x-model="search" type="search" placeholder="{{ __('Cari area atau noodle...') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></div><select x-model="perPage" @change="load(1)" class="h-11 w-36 rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="5">5 / page</option><option value="10">10 / page</option><option value="20">20 / page</option></select></form></div>
            </div>

            <div class="flex flex-wrap gap-4 border-b border-gray-200 bg-gray-50 px-6 py-3 text-theme-xs text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"><span class="inline-flex items-center gap-2"><span class="size-2.5 rounded-full bg-brand-400"></span>{{ __('Kolom LE') }}</span><span class="inline-flex items-center gap-2"><span class="size-2.5 rounded-full bg-gray-400"></span>{{ __('Kolom AOP') }}</span><span>{{ __('Geser tabel ke kanan untuk melihat seluruh periode.') }}</span></div>
            <div x-show="error" x-text="error" class="border-b border-error-200 bg-error-50 px-6 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>

            <div class="relative max-h-[440px] overflow-auto" :class="loading ? 'opacity-55' : ''">
                <table class="min-w-[3500px] divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="text-theme-xs font-medium uppercase tracking-wider">
                        <tr>
                            <th rowspan="2" class="sticky start-0 top-0 z-50 h-24 w-28 min-w-28 border-e border-gray-200 bg-gray-50 px-4 text-start align-middle text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"><button type="button" @click="changeSort('area_code')" class="inline-flex items-center gap-2 whitespace-nowrap hover:text-brand-500">{{ __('Area Code') }}</button></th>
                            <th rowspan="2" class="sticky start-28 top-0 z-50 h-24 w-48 min-w-48 border-e border-gray-200 bg-gray-50 px-4 text-start align-middle text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">{{ __('Area Description') }}</th>
                            <th rowspan="2" class="sticky start-[19rem] top-0 z-50 h-24 w-36 min-w-36 border-e border-gray-200 bg-gray-50 px-4 text-start align-middle text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"><button type="button" @click="changeSort('noodle_code')" class="inline-flex items-center gap-2 whitespace-nowrap hover:text-brand-500">{{ __('Noodle Code') }}</button></th>
                            <th rowspan="2" class="sticky start-[28rem] top-0 z-50 h-24 w-56 min-w-56 border-e border-gray-200 bg-gray-50 px-4 text-start align-middle text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">{{ __('Noodle Description') }}</th>
                            <th colspan="7" class="sticky top-0 z-40 h-12 border-b border-brand-200 bg-brand-100 px-4 text-center align-middle font-semibold text-brand-700 shadow-theme-xs dark:border-brand-500/30 dark:bg-brand-500/20 dark:text-brand-300">{{ __('LE') }}</th>
                            <th colspan="13" class="sticky top-0 z-40 h-12 border-b border-gray-200 bg-gray-100 px-4 text-center align-middle font-semibold text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ __('AOP') }}</th>
                            <th rowspan="2" class="sticky end-0 top-0 z-50 h-24 w-28 min-w-28 border-s border-gray-200 bg-gray-50 px-4 text-center align-middle text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">{{ __('Aksi') }}</th>
                        </tr>
                        <tr>
                            @foreach ($leFields as $field => $label)
                                <th class="sticky top-12 z-40 h-12 min-w-32 bg-brand-50 px-4 text-end align-middle text-brand-700 shadow-theme-xs dark:bg-gray-900 dark:text-brand-300"><button type="button" @click="changeSort('{{ $field }}')" class="whitespace-nowrap hover:text-brand-500">{{ str_replace('LE ', '', $label) }}</button></th>
                            @endforeach
                            <th class="sticky top-12 z-40 h-12 min-w-32 border-s border-brand-200 bg-brand-100 px-4 text-end align-middle font-semibold text-brand-700 shadow-theme-xs dark:border-brand-500/30 dark:bg-brand-500/20 dark:text-brand-300"><button type="button" @click="changeSort('total_le')" class="whitespace-nowrap hover:text-brand-500">{{ __('Total LE') }}</button></th>
                            @foreach ($monthFields as $field => $label)
                                <th class="sticky top-12 z-40 h-12 min-w-28 bg-gray-50 px-4 text-end align-middle text-gray-500 shadow-theme-xs dark:bg-gray-900 dark:text-gray-400"><button type="button" @click="changeSort('{{ $field }}')" class="whitespace-nowrap hover:text-brand-500">{{ $label }}</button></th>
                            @endforeach
                            <th class="sticky top-12 z-40 h-12 min-w-32 border-s border-gray-200 bg-gray-100 px-4 text-end align-middle font-semibold text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"><button type="button" @click="changeSort('total_aop')" class="whitespace-nowrap hover:text-brand-500">{{ __('Total AOP') }}</button></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800"><template x-for="volume in volumes" :key="keyOf(volume)"><tr class="group hover:bg-gray-50 dark:hover:bg-white/[0.02]"><td class="sticky start-0 z-20 border-e border-gray-200 bg-white px-4 py-4 text-sm font-medium text-gray-800 group-hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:group-hover:bg-gray-900" x-text="volume.area_code"></td><td class="sticky start-28 z-20 border-e border-gray-200 bg-white px-4 py-4 text-sm text-gray-600 group-hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:group-hover:bg-gray-900" x-text="volume.area_description"></td><td class="sticky start-[19rem] z-20 border-e border-gray-200 bg-white px-4 py-4 text-sm font-medium text-gray-800 group-hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:group-hover:bg-gray-900" x-text="volume.noodle_code"></td><td class="sticky start-[28rem] z-20 border-e border-gray-200 bg-white px-4 py-4 text-sm text-gray-600 group-hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:group-hover:bg-gray-900" x-text="volume.noodle_description"></td>
                        @foreach ($leFields as $field => $label)<td class="bg-brand-50/40 px-4 py-4 text-end text-sm text-gray-600 dark:bg-brand-500/[0.03] dark:text-gray-300" x-text="formatVolume(volume.{{ $field }})"></td>@endforeach
                        <td class="border-s border-brand-200 bg-brand-50 px-4 py-4 text-end text-sm font-semibold text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300" x-text="formatVolume(volume.total_le)"></td>
                        @foreach ($monthFields as $field => $label)<td class="px-4 py-4 text-end text-sm text-gray-600 dark:text-gray-300" x-text="formatVolume(volume.{{ $field }})"></td>@endforeach
                        <td class="border-s border-gray-200 bg-gray-50 px-4 py-4 text-end text-sm font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" x-text="formatVolume(volume.total_aop)"></td>
                        <td class="sticky end-0 z-20 border-s border-gray-200 bg-white px-4 py-4 group-hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:group-hover:bg-gray-900"><div class="flex justify-center gap-2"><button type="button" @click="openEdit(volume)" class="inline-flex size-9 items-center justify-center rounded-lg border border-brand-200 bg-brand-50 text-brand-600 hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.86 3.49 3.65 3.65M5 19l3.85-.77L19.74 7.34a1.5 1.5 0 0 0 0-2.12l-.96-.96a1.5 1.5 0 0 0-2.12 0L5.77 15.15 5 19Z" /></svg></button><button type="button" @click="openDelete(volume)" class="inline-flex size-9 items-center justify-center rounded-lg border border-error-200 bg-error-50 text-error-600 hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400" title="{{ __('Hapus') }}" aria-label="{{ __('Hapus') }}"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 7.5h15m-9-3h3m-7.5 3 .75 12h10.5l.75-12M9.5 11v5m5-5v5" /></svg></button></div></td></tr></template><template x-if="!loading && volumes.length === 0"><tr><td colspan="25" class="px-6 py-14 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Data volume noodle tidak ditemukan.') }}</td></tr></template></tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-6 py-3 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ __('Menampilkan') }} <span class="font-medium text-gray-700 dark:text-gray-300"><span x-text="meta.from"></span>-<span x-text="meta.to"></span></span> {{ __('dari') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="meta.total"></span> {{ __('data') }}</div>
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                <button type="button" @click="load(meta.current_page - 1)" :disabled="loading || meta.current_page === 1" class="h-10 justify-self-start rounded-lg border border-gray-300 px-4 text-sm text-gray-700 disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">{{ __('Previous') }}</button>
                <div class="flex min-w-0 items-center justify-center gap-1 sm:gap-1.5">
                    <template x-for="item in paginationItems()" :key="item.key">
                        <button type="button" @click="item.page && load(item.page)" :disabled="loading || !item.page" x-text="item.label"
                            class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg text-sm font-medium transition sm:size-10"
                            :class="item.page === meta.current_page ? 'bg-brand-500 text-white' : item.page ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800' : 'cursor-default text-gray-400 dark:text-gray-600'"
                            :aria-current="item.page === meta.current_page ? 'page' : null"
                            :aria-label="item.page ? `{{ __('Halaman') }} ${item.page}` : null"></button>
                    </template>
                </div>
                <button type="button" @click="load(meta.current_page + 1)" :disabled="loading || meta.current_page === meta.last_page" class="h-10 justify-self-end rounded-lg border border-gray-300 px-4 text-sm text-gray-700 disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">{{ __('Next') }}</button>
            </div>
        </section>

        <div x-show="editOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="edit-volume-title"><div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div><div x-show="editOpen" x-transition class="relative max-h-[94vh] w-full max-w-6xl overflow-y-auto rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 sm:p-7"><div class="mb-6 flex items-start justify-between gap-4"><div><h2 id="edit-volume-title" class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Edit Volume Noodle') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"><span x-text="`${selectedVolume.area_code} - ${selectedVolume.area_description}`"></span><span class="mx-2" aria-hidden="true">/</span><span x-text="`${selectedVolume.noodle_code} - ${selectedVolume.noodle_description}`"></span></p></div><button type="button" @click="closeModals()" class="flex size-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Tutup') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg></button></div><form @submit.prevent="saveEdit()"><x-volume-noodle.form-fields model="selectedVolume" :area-options="$areaOptions" :noodle-options="$noodleOptions" /><div class="mt-6 flex justify-end gap-3"><button type="button" @click="closeModals()" :disabled="loading" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="submit" :disabled="loading" class="h-11 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50">{{ __('Simpan Perubahan') }}</button></div></form></div></div>

        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="alertdialog" aria-modal="true" aria-labelledby="delete-volume-title"><div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div><div x-show="deleteOpen" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-theme-xl dark:bg-gray-900 sm:p-7"><div class="mx-auto flex size-14 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15 dark:text-error-400"><svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5m0 3.5v.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div><h2 id="delete-volume-title" class="mt-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Hapus Volume Noodle?') }}</h2><p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400"><span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedVolume.area_code"></span> / <span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedVolume.noodle_code"></span> {{ __('akan dihapus dari database.') }}</p><div class="mt-6 flex justify-center gap-3"><button type="button" @click="closeModals()" :disabled="loading" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="button" @click="confirmDelete()" :disabled="loading" class="h-11 rounded-lg bg-error-500 px-5 text-sm font-medium text-white hover:bg-error-600 disabled:opacity-50">{{ __('Ya, Hapus') }}</button></div></div></div>
    </div>
@endsection
