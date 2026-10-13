@props([
    'title',
    'description',
    'lookupLabel',
    'lookupPlaceholder',
    'lookupSearchPlaceholder' => __('Cari kode atau deskripsi...'),
    'ingredientCodeLabel',
    'ingredientDescriptionLabel',
    'masterItems' => [],
    'ingredientItems' => [],
    'initialFormulas' => [],
    'listId' => 'formula-master-list',
    'ingredientListId' => 'formula-ingredient-list',
    'lookupUrl' => null,
    'saveUrlTemplate' => null,
    'showBatch' => false,
    'standardLabel' => 'Standard',
    'standardBatchLabel' => 'Standard Batch',
    'standardStep' => '0.000001',
    'calculateStandard' => false,
    'splitPackaging' => false,
])

@php
    $masterOptions = collect($masterItems)->map(fn (array $item) => [
        'value' => $item['id'],
        'label' => $item['code'].' - '.$item['description'],
        'primary' => $item['code'],
        'secondary' => $item['description'],
        'search' => $item['code'].' '.$item['description'],
    ])->values()->all();
@endphp

<div class="min-w-0 max-w-full" x-data="{
    query: '',
    selectedMasterId: '',
    selectedMaster: null,
    rows: [],
    masterItems: @js($masterItems),
    ingredientItems: @js($ingredientItems),
    initialFormulas: @js($initialFormulas),
    lookupUrl: @js($lookupUrl),
    saveUrlTemplate: @js($saveUrlTemplate),
    showBatch: @js((bool) $showBatch),
    calculateStandard: @js((bool) $calculateStandard),
    splitPackaging: @js((bool) $splitPackaging),
    activeTab: 'rm',
    status: 'idle',
    error: '',
    saved: false,
    lookupLoading: false,
    saving: false,
    deleteOpen: false,
    deleteIndex: null,
    nextKey: 1,
    async lookup() {
        if (this.lookupLoading || this.saving) return;
        const value = this.query.trim().toLowerCase();

        if (this.lookupUrl) {
            if (!value) {
                this.error = '{{ __('Noodle Code wajib diisi.') }}';
                return;
            }

            this.lookupLoading = true;
            this.error = '';
            this.saved = false;

            try {
                const response = await fetch(`${this.lookupUrl}?code=${encodeURIComponent(this.query.trim())}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const payload = await response.json();

                if (!response.ok) throw new Error(this.responseError(payload, '{{ __('Kode tidak ditemukan pada data master.') }}'));

                this.selectedMaster = payload.data.master ?? payload.data.noodle;
                this.selectedMasterId = this.selectedMaster.id;
                this.query = this.selectedMaster.code;
                this.rows = payload.data.items.map(row => this.hydrateRow(row));
                this.activeTab = this.tabCount('rm') === 0 && this.tabCount('packaging') > 0 ? 'packaging' : 'rm';
                this.status = this.rows.length > 0 ? 'available' : 'missing';
            } catch (error) {
                this.selectedMaster = null;
                this.selectedMasterId = '';
                this.rows = [];
                this.status = 'invalid';
                this.error = error.message;
            } finally {
                this.lookupLoading = false;
            }

            return;
        }

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
        this.selectedMasterId = target.id;
        this.query = target.code;
        this.error = '';
        this.saved = false;
        const formula = this.initialFormulas[target.code] ?? [];
        this.rows = formula.map(row => this.hydrateRow({ ...row, deleted: false }));
        this.status = this.rows.length > 0 ? 'available' : 'missing';
    },
    addRow() {
        if (!this.selectedMaster) {
            this.error = '{{ __('Pilih kode master terlebih dahulu.') }}';
            return;
        }
        const isPackaging = this.splitPackaging && this.activeTab === 'packaging';
        this.rows.push(this.hydrateRow({ id: null, code: '', description: '', standard_batch: 0, standard: 0, is_packaging: isPackaging, deleted: false }));
        this.status = 'editing';
        this.error = '';
    },
    hydrateRow(row) {
        return { ...row, tab: row.is_packaging ? 'packaging' : 'rm', key: this.nextKey++ };
    },
    visibleRows() {
        return this.splitPackaging ? this.rows.filter(row => row.tab === this.activeTab) : this.rows;
    },
    tabCount(tab) {
        return this.rows.filter(row => row.tab === tab).length;
    },
    ingredientListFor(row) {
        return this.splitPackaging ? `{{ $ingredientListId }}-${row.tab}` : '{{ $ingredientListId }}';
    },
    fillDescription(row) {
        const ingredient = this.ingredientItems.find(item => item.code.toLowerCase() === row.code.trim().toLowerCase());

        if (this.splitPackaging && ingredient && (ingredient.is_packaging ? 'packaging' : 'rm') !== row.tab) {
            this.error = ingredient.is_packaging
                ? `${ingredient.code} {{ __('termasuk Packaging (memiliki ID). Tambahkan di tab Packaging.') }}`
                : `${ingredient.code} {{ __('termasuk RM (tanpa ID). Tambahkan di tab RM.') }}`;
            row.code = '';
            row.description = '';
            this.recalculate(row);
            return;
        }

        row.description = ingredient?.description ?? '';
        this.recalculate(row);
    },
    roundTo(value, digits) {
        const factor = 10 ** digits;
        return Math.round(Number((value * factor).toPrecision(15))) / factor;
    },
    recalculate(row) {
        if (!this.calculateStandard) return;
        const ingredient = this.ingredientItems.find(item => item.code.toLowerCase() === row.code.trim().toLowerCase());
        const batch = Number(this.selectedMaster?.batch ?? 0);
        row.is_packaging = ingredient?.is_packaging ?? false;
        if (!ingredient || batch <= 0) {
            row.standard = 0;
            return;
        }
        const value = Number(row.standard_batch || 0) / batch;
        row.standard = row.is_packaging ? this.roundTo(value, 8) : this.roundTo(value * 1000, 5);
        this.status = 'editing';
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
    async save() {
        if (this.saving || this.lookupLoading) return;
        if (!this.selectedMaster) {
            this.error = '{{ __('Pilih kode master terlebih dahulu.') }}';
            return;
        }
        const invalidRow = this.rows.find(row => !row.deleted && (row.code.trim() === '' || row.description.trim() === '' || Number(row.standard) < 0 || (this.showBatch && Number(row.standard_batch) < 0)));
        if (invalidRow) {
            if (this.splitPackaging) this.activeTab = invalidRow.tab;
            this.error = '{{ __('Lengkapi kode bahan dan standard pada setiap baris aktif.') }}';
            return;
        }
        if (this.calculateStandard && Number(this.selectedMaster.batch ?? 0) <= 0) {
            this.error = '{{ __('Batch Finished Good belum diisi. Isi Batch pada Maintenance Finished Good sebelum menyimpan formula.') }}';
            return;
        }
        this.error = '';

        if (!this.saveUrlTemplate) {
            this.saved = true;
            this.status = 'available';
            return;
        }

        this.saving = true;
        this.saved = false;

        try {
            const saveUrl = this.saveUrlTemplate
                .replace('__NOODLE__', this.selectedMaster.id)
                .replace('__MASTER__', this.selectedMaster.id);
            const response = await fetch(saveUrl, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    rows: this.rows.map(row => ({
                        id: row.id,
                        code: row.code,
                        ...(this.showBatch ? { standard_batch: row.standard_batch } : {}),
                        ...(this.calculateStandard ? {} : { standard: row.standard }),
                        deleted: row.deleted,
                    })),
                }),
            });
            const payload = await response.json();

            if (!response.ok) throw new Error(this.responseError(payload, '{{ __('Formula gagal disimpan.') }}'));

            this.rows = payload.data.items.map(row => this.hydrateRow(row));
            this.saved = true;
            this.status = this.rows.length > 0 ? 'available' : 'missing';
        } catch (error) {
            this.error = error.message;
        } finally {
            this.saving = false;
        }
    },
    responseError(payload, fallback) {
        const validationMessage = Object.values(payload.errors ?? {}).flat()[0];
        return validationMessage ?? payload.message ?? fallback;
    },
    closeDelete() {
        this.deleteOpen = false;
        this.deleteIndex = null;
    }
}" @formula-master-selected="query = $event.detail.code" @keydown.escape.window="closeDelete()">
    <section class="overflow-visible rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800 sm:px-7">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __($title) }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __($description) }}</p>
        </div>

        <div class="space-y-6 p-6 sm:p-7">
            <form @submit.prevent="lookup()" class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                <label for="{{ $listId }}-input" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __($lookupLabel) }}</label>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div id="{{ $listId }}-input" class="min-w-0 flex-1">
                        <x-form.searchable-select
                            model="selectedMasterId"
                            name="formula_master_id"
                            :options="$masterOptions"
                            :placeholder="__($lookupPlaceholder)"
                            :search-placeholder="__($lookupSearchPlaceholder)"
                            :empty-text="__('Data master tidak ditemukan.')"
                            on-select="$dispatch('formula-master-selected', { code: option.primary })"
                        />
                    </div>
                    <button type="submit" :disabled="lookupLoading || saving" class="inline-flex h-12 items-center justify-center gap-2 rounded-lg border border-brand-300 bg-transparent px-5 text-sm font-medium text-brand-600 transition hover:bg-brand-50 focus:ring-3 focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-brand-500/40 dark:text-brand-400 dark:hover:bg-brand-500/10"><svg x-show="lookupLoading" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" /><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z" /></svg><span x-text="lookupLoading ? '{{ __('Memuat...') }}' : '{{ __('Tampilkan Formula') }}'"></span></button>
                </div>
                <p class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Cari dan pilih kode dari daftar, lalu tekan Tampilkan Formula.') }}</p>
            </form>

            <div x-show="error" x-text="error" class="rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-600 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>
            <div x-show="saved" x-cloak class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ $saveUrlTemplate ? __('Formula berhasil disimpan ke database.') : __('Simulasi penyimpanan berhasil. Data belum terhubung ke database.') }}</div>

            <div x-show="selectedMaster" x-cloak class="flex flex-col gap-4 rounded-xl border border-gray-200 p-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <div><span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Data terpilih') }}</span><p class="mt-1 font-medium text-gray-800 dark:text-white/90"><span x-text="selectedMaster?.code"></span> · <span x-text="selectedMaster?.description"></span></p>@if ($showBatch)<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Batch') }}: <span class="font-medium text-gray-800 dark:text-white/90" x-text="Number(selectedMaster?.batch ?? 0).toLocaleString()"></span></p>@endif</div>
                <span class="inline-flex w-fit rounded-full px-3 py-1 text-theme-xs font-medium" :class="status === 'missing' ? 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400' : status === 'editing' ? 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400' : 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400'" x-text="status === 'missing' ? '{{ __('Formula belum ada') }}' : status === 'editing' ? '{{ __('Belum disimpan') }}' : '{{ __('Formula tersedia') }}'"></span>
            </div>

            <div x-show="selectedMaster && status === 'missing'" x-cloak class="rounded-xl border border-warning-200 bg-warning-50 px-5 py-4 text-sm text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400">{{ __('Formula belum tersedia. Klik Tambah Baris untuk membuat formula baru.') }}</div>

            <div x-show="selectedMaster" x-cloak>
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div><h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Formula Bahan') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Baris dapat diedit langsung sebelum disimpan.') }}</p></div>
                    <button type="button" @click="addRow()" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg border border-brand-300 bg-transparent px-4 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-400 dark:hover:bg-brand-500/10"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg><span x-text="splitPackaging ? (activeTab === 'packaging' ? '{{ __('Tambah Packaging') }}' : '{{ __('Tambah RM') }}') : '{{ __('Tambah Baris') }}'"></span></button>
                </div>

                @if ($splitPackaging)
                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div role="tablist" class="inline-flex w-fit rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
                            @foreach (['rm' => __('RM'), 'packaging' => __('Packaging')] as $tab => $tabLabel)
                                <button type="button" role="tab" :aria-selected="activeTab === '{{ $tab }}'" @click="activeTab = '{{ $tab }}'" class="inline-flex h-9 items-center gap-2 rounded-md px-4 text-sm font-medium transition" :class="activeTab === '{{ $tab }}' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-900 dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                                    {{ $tabLabel }}
                                    <span class="rounded-full px-2 py-0.5 text-theme-xs" :class="activeTab === '{{ $tab }}' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'" x-text="tabCount('{{ $tab }}')"></span>
                                </button>
                            @endforeach
                        </div>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400" x-text="activeTab === 'packaging' ? '{{ __('Standard Unit = Standard Batch ÷ Batch, dibulatkan 8 desimal.') }}' : '{{ __('Standard Unit = Standard Batch ÷ Batch × 1000, dibulatkan 5 desimal.') }}'"></p>
                    </div>
                @endif

                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="{{ $showBatch ? 'min-w-[960px]' : 'min-w-[820px]' }} w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="w-14 px-3 py-2.5 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('No') }}</th>
                                <th class="w-40 px-3 py-2.5 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __($ingredientCodeLabel) }}</th>
                                <th class="px-3 py-2.5 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __($ingredientDescriptionLabel) }}</th>
                                @if ($showBatch)<th class="w-48 px-3 py-2.5 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __($standardBatchLabel) }}</th>@endif
                                <th class="w-48 px-3 py-2.5 text-start text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __($standardLabel) }}</th>
                                <th class="w-24 px-3 py-2.5 text-center text-theme-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="(row, index) in visibleRows()" :key="row.key">
                                <tr :class="row.deleted ? 'bg-warning-50/70 opacity-65 dark:bg-warning-500/5' : 'bg-white dark:bg-gray-900'">
                                    <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400" x-text="index + 1"></td>
                                    <td class="px-3 py-2"><input x-model="row.code" @change="fillDescription(row)" @keyup.enter.prevent="fillDescription(row)" :list="ingredientListFor(row)" :disabled="row.deleted" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm font-medium text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed dark:border-gray-700 dark:text-white" /></td>
                                    <td class="px-3 py-2"><input x-model="row.description" readonly tabindex="-1" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 outline-hidden dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300" placeholder="{{ __('Otomatis terisi') }}" /></td>
                                    @if ($showBatch)
                                        <td class="px-3 py-2"><input x-model.number="row.standard_batch" @input="recalculate(row)" type="number" min="0" step="{{ $standardStep }}" :disabled="row.deleted" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-end text-sm tabular-nums text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed dark:border-gray-700 dark:text-white" /></td>
                                    @endif
                                    <td class="px-3 py-2">
                                        @if ($calculateStandard)
                                            <input :value="Number(row.standard ?? 0).toFixed(10)" readonly tabindex="-1" class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-end text-sm tabular-nums text-gray-600 outline-hidden dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300" />
                                        @else
                                            <input x-model.number="row.standard" type="number" min="0" step="{{ $standardStep }}" :disabled="row.deleted" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed dark:border-gray-700 dark:text-white" />
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" @click="toggleSoftDelete(row)" :title="row.deleted ? '{{ __('Batalkan Marker') }}' : '{{ __('Soft Delete') }}'" :aria-label="row.deleted ? '{{ __('Batalkan Marker') }}' : '{{ __('Soft Delete') }}'" class="inline-flex size-8 items-center justify-center rounded-lg border transition" :class="row.deleted ? 'border-success-200 bg-success-50 text-success-600 hover:bg-success-100 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400' : 'border-warning-200 bg-warning-50 text-warning-700 hover:bg-warning-100 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400'">
                                                <svg x-show="!row.deleted" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M5 12h14" /></svg>
                                                <svg x-show="row.deleted" x-cloak class="size-4 rtl:-scale-x-100" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14 4 9l5-5M4 9h10.5a5.5 5.5 0 0 1 0 11H11" /></svg>
                                            </button>
                                            <button type="button" @click="requestHardDelete(rows.indexOf(row))" title="{{ __('Hard Delete') }}" aria-label="{{ __('Hard Delete') }}" class="inline-flex size-8 items-center justify-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 7.5h15m-9-3h3m-7.5 3 .75 12h10.5l.75-12M9.5 11v5m5-5v5" /></svg></button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="visibleRows().length === 0"><tr><td colspan="{{ $showBatch ? 6 : 5 }}" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400" x-text="splitPackaging ? (activeTab === 'packaging' ? '{{ __('Belum ada baris Packaging. Klik Tambah Packaging untuk mulai mengisi.') }}' : '{{ __('Belum ada baris RM. Klik Tambah RM untuk mulai mengisi.') }}') : '{{ __('Belum ada baris formula. Klik Tambah Baris untuk mulai mengisi.') }}'"></td></tr></template>
                        </tbody>
                    </table>
                    @if ($splitPackaging)
                        <datalist id="{{ $ingredientListId }}-rm">@foreach ($ingredientItems as $item)@unless ($item['is_packaging'] ?? false)<option value="{{ $item['code'] }}">{{ $item['description'] }}</option>@endunless @endforeach</datalist>
                        <datalist id="{{ $ingredientListId }}-packaging">@foreach ($ingredientItems as $item)@if ($item['is_packaging'] ?? false)<option value="{{ $item['code'] }}">{{ $item['description'] }}</option>@endif @endforeach</datalist>
                    @else
                        <datalist id="{{ $ingredientListId }}">@foreach ($ingredientItems as $item)<option value="{{ $item['code'] }}">{{ $item['description'] }}</option>@endforeach</datalist>
                    @endif
                </div>

                <button type="button" @click="save()" :disabled="saving || lookupLoading" class="mt-5 inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl border border-brand-300 bg-transparent text-sm font-semibold text-brand-600 shadow-theme-xs transition hover:bg-brand-50 focus:ring-3 focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-brand-500/40 dark:text-brand-400 dark:hover:bg-brand-500/10"><svg x-show="!saving" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12.5 4.25 4.25L19 7" /></svg><svg x-show="saving" x-cloak class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" /><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z" /></svg><span x-text="saving ? '{{ __('Menyimpan...') }}' : '{{ __('Simpan Formula') }}'"></span></button>
            </div>
        </div>
    </section>

    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-999999 flex items-center justify-center p-4" role="alertdialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="closeDelete()"></div>
        <div x-show="deleteOpen" x-transition class="relative w-full max-w-md rounded-2xl bg-white p-7 text-center shadow-theme-xl dark:bg-gray-900">
            <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15 dark:text-error-400"><svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5m0 3.5v.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
            <h2 class="mt-5 text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Hapus Permanen Baris Formula?') }}</h2>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $saveUrlTemplate ? __('Baris akan dihapus permanen dari database saat Formula disimpan.') : __('Baris akan langsung dihilangkan dari tabel. Aksi ini tidak dapat dibatalkan setelah dikonfirmasi.') }}</p>
            <div class="mt-6 flex justify-center gap-3"><button type="button" @click="closeDelete()" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button><button type="button" @click="confirmHardDelete()" class="h-11 rounded-lg bg-error-500 px-5 text-sm font-medium text-white hover:bg-error-600">{{ __('Ya, Hapus Permanen') }}</button></div>
        </div>
    </div>
</div>
