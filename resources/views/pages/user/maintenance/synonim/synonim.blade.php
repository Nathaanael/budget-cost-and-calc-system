@extends('layouts.app')

@section('content')
    <div class="min-w-0 max-w-full" x-data="{
        editOpen: false,
        deleteOpen: false,
        loading: false,
        saving: false,
        deleting: false,
        error: '',
        synonims: @js($synonims->items()),
        meta: @js(['current_page' => $synonims->currentPage(), 'last_page' => $synonims->lastPage(), 'from' => $synonims->firstItem() ?? 0, 'to' => $synonims->lastItem() ?? 0, 'total' => $synonims->total(), 'per_page' => $synonims->perPage()]),
        search: @js($search), sort: @js($sort), direction: @js($direction), perPage: @js($perPage),
        selectedSynonim: {},
        pages() { return Array.from({ length: this.meta.last_page }, (_, index) => index + 1); },
        async load(page = 1) {
            this.loading = true; this.error = '';
            const params = new URLSearchParams({ search: this.search, sort: this.sort, direction: this.direction, per_page: this.perPage, page });
            try {
                const response = await fetch(`{{ route('admin.maintenance.synonim.data') }}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Request failed');
                const payload = await response.json(); this.synonims = payload.data; this.meta = payload.meta;
                window.history.replaceState({}, '', `{{ route('admin.maintenance.synonim.index') }}?${params.toString()}`);
            } catch (error) { this.error = '{{ __('Data gagal dimuat. Silakan coba lagi.') }}'; }
            finally { this.loading = false; }
        },
        changeSort(field) { this.direction = this.sort === field && this.direction === 'asc' ? 'desc' : 'asc'; this.sort = field; this.load(1); },
        openEdit(synonim) { this.error = ''; this.selectedSynonim = { ...synonim }; this.editOpen = true; },
        openDelete(synonim) { this.selectedSynonim = { ...synonim }; this.deleteOpen = true; },
        async saveEdit() {
            this.saving = true; this.error = '';
            try {
                const url = `{{ route('admin.maintenance.synonim.update', ['synonim' => '__ID__']) }}`.replace('__ID__', this.selectedSynonim.id);
                const response = await fetch(url, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ rm_code: this.selectedSynonim.rm_code, fg_code: this.selectedSynonim.fg_code }) });
                const payload = await response.json();
                if (!response.ok) throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || '{{ __('Data gagal diperbarui.') }}');
                const index = this.synonims.findIndex((item) => item.id === this.selectedSynonim.id);
                if (index !== -1) this.synonims[index] = payload.data;
                this.closeModals();
            } catch (error) { this.error = error.message; }
            finally { this.saving = false; }
        },
        async confirmDelete() {
            this.deleting = true; this.error = '';
            try {
                const url = `{{ route('admin.maintenance.synonim.destroy', ['synonim' => '__ID__']) }}`.replace('__ID__', this.selectedSynonim.id);
                const response = await fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                if (!response.ok) throw new Error('{{ __('Data gagal dihapus.') }}');
                this.closeModals(); await this.load(this.meta.current_page);
            } catch (error) { this.error = error.message; }
            finally { this.deleting = false; }
        },
        closeModals() { if (this.saving || this.deleting) return; this.editOpen = false; this.deleteOpen = false; }
    }" @keydown.escape.window="closeModals()">
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
        @endif
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-5 border-b border-gray-200 px-6 py-5 dark:border-gray-800 xl:flex-row xl:items-center xl:justify-between">
                <div><h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Synonim') }}</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Pemetaan Raw Material ke Finished Good.') }}</p></div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center"><a href="{{ route('admin.maintenance.synonim.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg>{{ __('Tambah Synonim') }}</a><form @submit.prevent="load(1)" class="flex gap-3"><div class="relative w-full sm:w-72"><svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg><input x-model="search" type="search" placeholder="{{ __('Cari kode atau description...') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></div><select x-model="perPage" @change="load(1)" class="h-11 w-36 rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="5">5 / page</option><option value="10">10 / page</option><option value="20">20 / page</option></select></form></div>
            </div>
            <div x-show="error" x-text="error" class="border-b border-error-200 bg-error-50 px-6 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>
            <div class="relative max-h-[420px] overflow-auto" :class="loading ? 'opacity-55' : ''"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800"><thead class="sticky top-0 z-20 bg-gray-50 shadow-theme-xs dark:bg-gray-900"><tr>
                @foreach (['rm_code' => __('RM Code'), 'rm_description' => __('RM Description'), 'fg_code' => __('FG Code'), 'fg_description' => __('FG Description')] as $field => $label)
                    <th class="min-w-36 px-6 py-3.5 text-start text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"><button type="button" @click="changeSort('{{ $field }}')" class="inline-flex items-center gap-2 whitespace-nowrap hover:text-brand-500">{{ $label }}<span class="flex flex-col"><svg class="size-2.5" :class="sort === '{{ $field }}' && direction === 'asc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor"><path d="M5 0 10 6H0L5 0Z" /></svg><svg class="mt-0.5 size-2.5" :class="sort === '{{ $field }}' && direction === 'desc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor"><path d="m5 6 5-6H0l5 6Z" /></svg></span></button></th>
                @endforeach
                <th class="w-28 px-4 py-3.5 text-center text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Aksi') }}</th>
            </tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800"><template x-for="synonim in synonims" :key="synonim.id"><tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]"><td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90" x-text="synonim.rm_code"></td><td class="min-w-48 px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="synonim.rm_description"></td><td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90" x-text="synonim.fg_code"></td><td class="min-w-60 px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="synonim.fg_description"></td><td class="px-4 py-4"><div class="flex justify-center gap-2"><button type="button" @click="openEdit(synonim)" class="inline-flex size-9 items-center justify-center rounded-lg border border-brand-200 bg-brand-50 text-brand-600 hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.86 3.49 3.65 3.65M5 19l3.85-.77L19.74 7.34a1.5 1.5 0 0 0 0-2.12l-.96-.96a1.5 1.5 0 0 0-2.12 0L5.77 15.15 5 19Z" /></svg></button><button type="button" @click="openDelete(synonim)" class="inline-flex size-9 items-center justify-center rounded-lg border border-error-200 bg-error-50 text-error-600 hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400" title="{{ __('Hapus') }}" aria-label="{{ __('Hapus') }}"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 7.5h15m-9-3h3m-7.5 3 .75 12h10.5l.75-12M9.5 11v5m5-5v5" /></svg></button></div></td></tr></template><template x-if="!loading && synonims.length === 0"><tr><td colspan="5" class="px-6 py-14 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Data synonim tidak ditemukan.') }}</td></tr></template></tbody></table></div>
            <div class="border-t border-gray-200 px-6 py-3 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ __('Menampilkan') }} <span class="font-medium text-gray-700 dark:text-gray-300"><span x-text="meta.from"></span>-<span x-text="meta.to"></span></span> {{ __('dari') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="meta.total"></span> {{ __('data') }}</div>
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800"><button type="button" @click="load(meta.current_page - 1)" :disabled="loading || meta.current_page === 1" class="h-10 justify-self-start rounded-lg border border-gray-300 px-4 text-sm text-gray-700 disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">{{ __('Previous') }}</button><div class="flex gap-1.5"><template x-for="page in pages()" :key="page"><button type="button" @click="load(page)" x-text="page" class="inline-flex size-10 items-center justify-center rounded-lg text-sm font-medium" :class="page === meta.current_page ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'"></button></template></div><button type="button" @click="load(meta.current_page + 1)" :disabled="loading || meta.current_page === meta.last_page" class="h-10 justify-self-end rounded-lg border border-gray-300 px-4 text-sm text-gray-700 disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">{{ __('Next') }}</button></div>
        </section>

        <div x-show="editOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="edit-synonim-title"><div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div><div x-show="editOpen" x-transition class="relative max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 sm:p-7"><div class="mb-6 flex items-start justify-between gap-4"><div><h2 id="edit-synonim-title" class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Edit Synonim') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Ubah pemetaan Raw Material dan Finished Good.') }}</p></div><button type="button" @click="closeModals()" class="flex size-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Tutup') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg></button></div><form @submit.prevent="saveEdit()"><x-synonim.form-fields model="selectedSynonim" :raw-material-options="$rawMaterialOptions" :finished-good-options="$finishedGoodOptions" /><div class="mt-6 flex justify-end gap-3"><button type="button" @click="closeModals()" :disabled="saving" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="submit" :disabled="saving" class="h-11 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-60"><span x-text="saving ? '{{ __('Menyimpan...') }}' : '{{ __('Simpan Perubahan') }}'"></span></button></div></form></div></div>

        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="alertdialog" aria-modal="true" aria-labelledby="delete-synonim-title"><div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div><div x-show="deleteOpen" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-theme-xl dark:bg-gray-900 sm:p-7"><div class="mx-auto flex size-14 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15 dark:text-error-400"><svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5m0 3.5v.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div><h2 id="delete-synonim-title" class="mt-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Hapus Data Synonim?') }}</h2><p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Pemetaan') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedSynonim.rm_code"></span> {{ __('akan dihapus permanen.') }}</p><div class="mt-6 flex justify-center gap-3"><button type="button" @click="closeModals()" :disabled="deleting" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="button" @click="confirmDelete()" :disabled="deleting" class="h-11 rounded-lg bg-error-500 px-5 text-sm font-medium text-white hover:bg-error-600 disabled:opacity-60"><span x-text="deleting ? '{{ __('Menghapus...') }}' : '{{ __('Ya, Hapus') }}'"></span></button></div></div></div>
    </div>
@endsection
