@props([
    'title',
    'description',
    'lookupLabel',
    'lookupPlaceholder',
    'ingredientCodeLabel',
    'ingredientDescriptionLabel',
    'masterItems' => [],
    'ingredientItems' => [],
    'initialFormulas' => [],
    'listId' => 'formula-master-list',
    'ingredientListId' => 'formula-ingredient-list',
])

<div class="min-w-0 max-w-full" x-data="{
    query: '',
    selectedMaster: null,
    rows: [],
    masterItems: @js($masterItems),
    ingredientItems: @js($ingredientItems),
    initialFormulas: @js($initialFormulas),
    status: 'idle',
    error: '',
    saved: false,
    deleteOpen: false,
    deleteIndex: null,
    nextKey: 1,
    lookup() {
        const value = this.query.trim().toLowerCase();
        const target = this.masterItems.find(item => item.code.toLowerCase() === value)
            ?? this.masterItems.find(item => item.description.toLowerCase() === value);

        if (!target) {
            this.selectedMaster = null;
            this.rows = [];
            this.status = 'invalid';
            this.error = '{{ __('Kode tidak ditemukan pada data master.') }}';
            return;
        }

        this.selectedMaster = target;
        this.query = target.code;
        this.error = '';
        this.saved = false;
        const formula = this.initialFormulas[target.code] ?? [];
        this.rows = formula.map(row => ({ ...row, deleted: false, key: this.nextKey++ }));
        this.status = this.rows.length > 0 ? 'available' : 'missing';
    },
    addRow() {
        if (!this.selectedMaster) {
            this.error = '{{ __('Pilih kode master terlebih dahulu.') }}';
            return;
        }
        this.rows.push({ code: '', description: '', standard: 0, deleted: false, key: this.nextKey++ });
        this.status = 'editing';
        this.error = '';
    },
    fillDescription(row) {
        const ingredient = this.ingredientItems.find(item => item.code.toLowerCase() === row.code.trim().toLowerCase());
        row.description = ingredient?.description ?? '';
    },
    toggleSoftDelete(row) {
        row.deleted = !row.deleted;
        this.status = 'editing';
    },
    requestHardDelete(index) {
        this.deleteIndex = index;
        this.deleteOpen = true;
    },
    confirmHardDelete() {
        if (this.deleteIndex !== null) this.rows.splice(this.deleteIndex, 1);
        this.deleteIndex = null;
        this.deleteOpen = false;
        this.status = 'editing';
    },
    save() {
        if (!this.selectedMaster) {
            this.error = '{{ __('Pilih kode master terlebih dahulu.') }}';
            return;
        }
        const invalidRow = this.rows.find(row => !row.deleted && (row.code.trim() === '' || row.description.trim() === '' || Number(row.standard) < 0));
        if (invalidRow) {
            this.error = '{{ __('Lengkapi kode bahan dan standard pada setiap baris aktif.') }}';
            return;
        }
        this.error = '';
        this.saved = true;
        this.status = 'available';
    },
    closeDelete() {
        this.deleteOpen = false;
        this.deleteIndex = null;
    }
}" @keydown.escape.window="closeDelete()">
    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800 sm:px-7">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __($title) }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __($description) }}</p>
        </div>

        <div class="space-y-6 p-6 sm:p-7">
            <form @submit.prevent="lookup()" class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                <label for="{{ $listId }}-input" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __($lookupLabel) }}</label>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative min-w-0 flex-1">
                        <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg>
                        <input id="{{ $listId }}-input" x-model="query" list="{{ $listId }}" autocomplete="off" placeholder="{{ __($lookupPlaceholder) }}" class="h-12 w-full rounded-lg border border-gray-300 bg-white ps-12 pe-4 text-sm text-gray-800 outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        <datalist id="{{ $listId }}">@foreach ($masterItems as $item)<option value="{{ $item['code'] }}">{{ $item['description'] }}</option>@endforeach</datalist>
                    </div>
                    <button type="submit" class="inline-flex h-12 items-center justify-center rounded-lg border border-brand-300 bg-transparent px-5 text-sm font-medium text-brand-600 transition hover:bg-brand-50 focus:ring-3 focus:ring-brand-500/20 dark:border-brand-500/40 dark:text-brand-400 dark:hover:bg-brand-500/10">{{ __('Tampilkan Formula') }}</button>
                </div>
                <p class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pilih kode dari daftar lalu tekan Enter.') }}</p>
            </form>

            <div x-show="error" x-text="error" class="rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>
            <div x-show="saved" x-cloak class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ __('Simulasi penyimpanan berhasil. Data belum terhubung ke database.') }}</div>

            <div x-show="selectedMaster" x-cloak class="flex flex-col gap-4 rounded-xl border border-gray-200 p-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <div><span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Data terpilih') }}</span><p class="mt-1 font-medium text-gray-800 dark:text-white/90"><span x-text="selectedMaster?.code"></span> · <span x-text="selectedMaster?.description"></span></p></div>
                <span class="inline-flex w-fit rounded-full px-3 py-1 text-theme-xs font-medium" :class="status === 'missing' ? 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400' : status === 'editing' ? 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400' : 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400'" x-text="status === 'missing' ? '{{ __('Formula belum ada') }}' : status === 'editing' ? '{{ __('Belum disimpan') }}' : '{{ __('Formula tersedia') }}'"></span>
            </div>

            <div x-show="selectedMaster && status === 'missing'" x-cloak class="rounded-xl border border-warning-200 bg-warning-50 px-5 py-4 text-sm text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400">{{ __('Formula belum tersedia. Klik Tambah Baris untuk membuat formula baru.') }}</div>

            <div x-show="selectedMaster" x-cloak>
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div><h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Formula Bahan') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Baris dapat diedit langsung sebelum disimpan.') }}</p></div>
                    <button type="button" @click="addRow()" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg border border-brand-300 bg-transparent px-4 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-400 dark:hover:bg-brand-500/10"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg>{{ __('Tambah Baris') }}</button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="min-w-[900px] w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900"><tr><th class="w-16 px-4 py-3 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('No') }}</th><th class="w-52 px-4 py-3 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __($ingredientCodeLabel) }}</th><th class="px-4 py-3 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __($ingredientDescriptionLabel) }}</th><th class="w-40 px-4 py-3 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Standard') }}</th><th class="w-64 px-4 py-3 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Aksi') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="(row, index) in rows" :key="row.key"><tr :class="row.deleted ? 'bg-warning-50/70 opacity-65 dark:bg-warning-500/5' : 'bg-white dark:bg-gray-900'"><td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400" x-text="index + 1"></td><td class="px-4 py-3"><input x-model="row.code" @change="fillDescription(row)" @keyup.enter.prevent="fillDescription(row)" list="{{ $ingredientListId }}" :disabled="row.deleted" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm font-medium text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed dark:border-gray-700 dark:text-white" /></td><td class="px-4 py-3"><input x-model="row.description" readonly class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 outline-hidden dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300" placeholder="{{ __('Otomatis terisi') }}" /></td><td class="px-4 py-3"><input x-model.number="row.standard" type="number" min="0" step="0.000001" :disabled="row.deleted" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed dark:border-gray-700 dark:text-white" /></td><td class="px-4 py-3"><div class="flex items-center gap-2"><button type="button" @click="toggleSoftDelete(row)" class="inline-flex h-9 items-center gap-2 rounded-lg border px-3 text-theme-xs font-medium transition" :class="row.deleted ? 'border-success-200 bg-success-50 text-success-600 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400' : 'border-warning-200 bg-warning-50 text-warning-700 hover:bg-warning-100 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400'"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M5 12h14" /></svg><span x-text="row.deleted ? '{{ __('Batalkan Marker') }}' : '{{ __('Soft Delete') }}'"></span></button><button type="button" @click="requestHardDelete(index)" class="inline-flex h-9 items-center gap-2 rounded-lg border border-error-200 bg-error-50 px-3 text-theme-xs font-medium text-error-600 transition hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 7.5h15m-9-3h3m-7.5 3 .75 12h10.5l.75-12M9.5 11v5m5-5v5" /></svg>{{ __('Hard Delete') }}</button></div></td></tr></template>
                            <template x-if="rows.length === 0"><tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada baris formula. Klik Tambah Baris untuk mulai mengisi.') }}</td></tr></template>
                        </tbody>
                    </table>
                    <datalist id="{{ $ingredientListId }}">@foreach ($ingredientItems as $item)<option value="{{ $item['code'] }}">{{ $item['description'] }}</option>@endforeach</datalist>
                </div>

                <button type="button" @click="save()" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border border-brand-300 bg-transparent text-sm font-semibold text-brand-600 shadow-theme-xs transition hover:bg-brand-50 focus:ring-3 focus:ring-brand-500/20 dark:border-brand-500/40 dark:text-brand-400 dark:hover:bg-brand-500/10"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg>{{ __('Simpan Formula') }}</button>
            </div>
        </div>
    </section>

    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4" role="alertdialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeDelete()"></div>
        <div x-show="deleteOpen" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-7 text-center shadow-theme-xl dark:bg-gray-900">
            <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15 dark:text-error-400"><svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5m0 3.5v.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
            <h2 class="mt-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Hapus Permanen Baris Formula?') }}</h2>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Baris akan langsung dihilangkan dari tabel. Aksi UI ini tidak dapat dibatalkan setelah dikonfirmasi.') }}</p>
            <div class="mt-6 flex justify-center gap-3"><button type="button" @click="closeDelete()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="button" @click="confirmHardDelete()" class="h-11 rounded-lg bg-error-500 px-5 text-sm font-medium text-white hover:bg-error-600">{{ __('Ya, Hapus Permanen') }}</button></div>
        </div>
    </div>
</div>
