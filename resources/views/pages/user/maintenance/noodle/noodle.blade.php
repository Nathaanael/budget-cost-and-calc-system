@extends('layouts.app')

@section('content')
    <div
        x-data="{
            editOpen: false,
            deleteOpen: false,
            loading: false,
            error: '',
            noodles: @js($noodles->items()),
            meta: @js([
                'current_page' => $noodles->currentPage(),
                'last_page' => $noodles->lastPage(),
                'from' => $noodles->firstItem() ?? 0,
                'to' => $noodles->lastItem() ?? 0,
                'total' => $noodles->total(),
                'per_page' => $noodles->perPage(),
            ]),
            search: @js($search),
            selectedUnit: @js($selectedUnit),
            sort: @js($sort),
            direction: @js($direction),
            perPage: @js($perPage),
            selectedNoodle: { code: '', description: '', unit: '' },
            pages() {
                return Array.from({ length: this.meta.last_page }, (_, index) => index + 1);
            },
            async load(page = 1) {
                this.loading = true;
                this.error = '';

                const params = new URLSearchParams({
                    search: this.search,
                    unit: this.selectedUnit,
                    sort: this.sort,
                    direction: this.direction,
                    per_page: this.perPage,
                    page: page,
                });

                try {
                    const response = await fetch(`{{ route('admin.maintenance.noodle.data') }}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    if (!response.ok) throw new Error('Request failed');

                    const payload = await response.json();
                    this.noodles = payload.data;
                    this.meta = payload.meta;
                    window.history.replaceState({}, '', `{{ route('admin.maintenance.noodle.index') }}?${params.toString()}`);
                } catch (error) {
                    this.error = '{{ __('Data gagal dimuat. Silakan coba lagi.') }}';
                } finally {
                    this.loading = false;
                }
            },
            changeSort(field) {
                if (this.sort === field) {
                    this.direction = this.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sort = field;
                    this.direction = 'asc';
                }
                this.load(1);
            },
            resetFilters() {
                this.search = '';
                this.selectedUnit = '';
                this.load(1);
            },
            openEdit(noodle) {
                this.selectedNoodle = { ...noodle };
                this.editOpen = true;
                this.$nextTick(() => this.$refs.editCode?.focus());
            },
            openDelete(noodle) {
                this.selectedNoodle = { ...noodle };
                this.deleteOpen = true;
            },
            closeModals() {
                this.editOpen = false;
                this.deleteOpen = false;
            }
        }"
        @keydown.escape.window="closeModals()">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-5 border-b border-gray-200 px-6 py-5 dark:border-gray-800 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Noodle') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Manajemen data master noodle.') }}</p>
                </div>

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <a href="{{ route('admin.maintenance.noodle.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/20">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg>
                        {{ __('Tambah Noodle') }}
                    </a>

                    <form @submit.prevent="load(1)" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative w-full sm:w-72">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg>
                            </span>
                            <input x-model="search" name="search" type="search" placeholder="{{ __('Cari code atau description...') }}"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 outline-hidden transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>

                        <div class="relative w-full sm:w-44">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M7 12h10m-7 6h4" /></svg>
                            </span>
                            <select x-model="selectedUnit" @change="load(1)" name="unit"
                                class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white ps-11 pe-9 text-sm text-gray-700 outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">{{ __('Semua Satuan') }}</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit }}">{{ $unit }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute end-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </div>

                        <div class="relative w-full sm:w-32">
                            <select x-model.number="perPage" @change="load(1)" name="per_page"
                                class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 pe-9 text-sm text-gray-700 outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="5">5 / page</option>
                                <option value="10">10 / page</option>
                                <option value="20">20 / page</option>
                            </select>
                            <svg class="pointer-events-none absolute end-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </div>

                        <template x-if="search !== '' || selectedUnit !== ''">
                            <button type="button" @click="resetFilters()" class="inline-flex size-11 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white" title="{{ __('Reset pencarian') }}" aria-label="{{ __('Reset pencarian') }}">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg>
                            </button>
                        </template>
                    </form>
                </div>
            </div>

            <div x-show="error" x-text="error" class="border-b border-error-200 bg-error-50 px-6 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>

            <div class="relative max-h-[420px] overflow-auto" :class="loading ? 'opacity-55' : ''">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="sticky top-0 z-1 bg-gray-50 shadow-theme-xs dark:bg-gray-900">
                        <tr>
                            @foreach (['code' => __('Code'), 'description' => __('Description'), 'unit' => __('Satuan')] as $field => $label)
                                <th class="px-6 py-3.5 text-start text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    <button type="button" @click="changeSort('{{ $field }}')" class="inline-flex items-center gap-2 transition hover:text-brand-500 dark:hover:text-brand-400">
                                        {{ $label }}
                                        <span class="flex flex-col">
                                            <svg class="size-2.5" :class="sort === '{{ $field }}' && direction === 'asc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor" aria-hidden="true"><path d="M5 0 10 6H0L5 0Z" /></svg>
                                            <svg class="mt-0.5 size-2.5" :class="sort === '{{ $field }}' && direction === 'desc' ? 'text-brand-500' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 10 6" fill="currentColor" aria-hidden="true"><path d="m5 6 5-6H0l5 6Z" /></svg>
                                        </span>
                                    </button>
                                </th>
                            @endforeach
                            <th class="w-52 px-6 py-3.5 text-start text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="noodle in noodles" :key="noodle.code">
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90" x-text="noodle.code"></td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="noodle.description"></td>
                                <td class="whitespace-nowrap px-6 py-4"><span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400" x-text="noodle.unit"></span></td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="openEdit(noodle)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-3 text-theme-xs font-medium text-brand-600 transition hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/20">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.86 3.49 3.65 3.65M5 19l3.85-.77L19.74 7.34a1.5 1.5 0 0 0 0-2.12l-.96-.96a1.5 1.5 0 0 0-2.12 0L5.77 15.15 5 19Z" /></svg>
                                            {{ __('Edit') }}
                                        </button>
                                        <button type="button" @click="openDelete(noodle)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-error-200 bg-error-50 px-3 text-theme-xs font-medium text-error-600 transition hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 7.5h15m-9-3h3m-7.5 3 .75 12h10.5l.75-12M9.5 11v5m5-5v5" /></svg>
                                            {{ __('Hapus') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="!loading && noodles.length === 0">
                            <tr><td colspan="4" class="px-6 py-14 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Data noodle tidak ditemukan.') }}</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-6 py-3 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
                {{ __('Menampilkan') }} <span class="font-medium text-gray-700 dark:text-gray-300"><span x-text="meta.from"></span>–<span x-text="meta.to"></span></span> {{ __('dari') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="meta.total"></span> {{ __('data') }}
            </div>

            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                <div class="justify-self-start">
                    <button type="button" @click="load(meta.current_page - 1)" :disabled="loading || meta.current_page === 1" class="inline-flex h-10 items-center gap-2 rounded-lg border px-4 text-sm transition disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 enabled:border-gray-300 enabled:font-medium enabled:text-gray-700 enabled:hover:bg-gray-50 dark:disabled:border-gray-800 dark:disabled:text-gray-600 dark:enabled:border-gray-700 dark:enabled:text-gray-300 dark:enabled:hover:bg-gray-800"><svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6" /></svg>{{ __('Previous') }}</button>
                </div>

                <div class="flex items-center justify-center gap-1.5">
                    <template x-for="page in pages()" :key="page">
                        <button type="button" @click="load(page)" :disabled="loading" x-text="page" class="inline-flex size-10 items-center justify-center rounded-lg text-sm font-medium transition" :class="page === meta.current_page ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'"></button>
                    </template>
                </div>

                <div class="justify-self-end">
                    <button type="button" @click="load(meta.current_page + 1)" :disabled="loading || meta.current_page === meta.last_page" class="inline-flex h-10 items-center gap-2 rounded-lg border px-4 text-sm transition disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 enabled:border-gray-300 enabled:font-medium enabled:text-gray-700 enabled:hover:bg-gray-50 dark:disabled:border-gray-800 dark:disabled:text-gray-600 dark:enabled:border-gray-700 dark:enabled:text-gray-300 dark:enabled:hover:bg-gray-800">{{ __('Next') }}<svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m9 18 6-6-6-6" /></svg></button>
                </div>
            </div>
        </section>

        <div x-show="editOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="edit-noodle-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div>
            <div x-show="editOpen" x-transition class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div><h2 id="edit-noodle-title" class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Edit Noodle') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Perubahan masih berupa simulasi UI.') }}</p></div>
                    <button type="button" @click="closeModals()" class="flex size-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Tutup') }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" /></svg></button>
                </div>
                <form @submit.prevent="closeModals()" class="mt-6 space-y-5">
                    <div><label for="edit-noodle-code" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Noodle Code') }}</label><input id="edit-noodle-code" x-ref="editCode" x-model="selectedNoodle.code" type="text" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                    <div><label for="edit-noodle-description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }}</label><input id="edit-noodle-description" x-model="selectedNoodle.description" type="text" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white" /></div>
                    <div><label for="edit-noodle-unit" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Unit') }}</label><select id="edit-noodle-unit" x-model="selectedNoodle.unit" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white">@foreach ($units as $unit)<option value="{{ $unit }}">{{ $unit }}</option>@endforeach</select></div>
                    <div class="flex justify-end gap-3 pt-2"><button type="button" @click="closeModals()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="submit" class="h-11 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">{{ __('Simpan Perubahan') }}</button></div>
                </form>
            </div>
        </div>

        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4 sm:p-6" role="alertdialog" aria-modal="true" aria-labelledby="delete-noodle-title">
            <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeModals()"></div>
            <div x-show="deleteOpen" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-theme-xl dark:bg-gray-900 sm:p-7">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15 dark:text-error-400"><svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5m0 3.5v.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
                <h2 id="delete-noodle-title" class="mt-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Hapus Data Noodle?') }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Data') }} <span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedNoodle.code"></span> {{ __('akan dihapus. Aksi ini belum terhubung ke backend.') }}</p>
                <div class="mt-6 flex justify-center gap-3"><button type="button" @click="closeModals()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="button" @click="closeModals()" class="h-11 rounded-lg bg-error-500 px-5 text-sm font-medium text-white hover:bg-error-600">{{ __('Ya, Hapus') }}</button></div>
            </div>
        </div>
    </div>
@endsection
