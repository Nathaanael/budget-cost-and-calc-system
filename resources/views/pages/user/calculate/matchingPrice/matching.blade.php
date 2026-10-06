@extends('layouts.app')

@section('content')
    @php
        $periods = ['le' => __('LE'), 'qtr_1' => __('Quarter 1'), 'qtr_2' => __('Quarter 2'), 'qtr_3' => __('Quarter 3'), 'qtr_4' => __('Quarter 4')];
        $statuses = ['ready' => __('Ready to match'), 'unchanged' => __('Unchanged'), 'missing' => __('Missing price')];
    @endphp

    <x-common.page-breadcrumb :pageTitle="__('Matching Price')" />

    <div class="space-y-6" x-data="{
        samples: @js($materials), statuses: @js($statuses),
        factory: @js(session('matching_factory', old('factory', 'cikampek'))), period: 'le', search: '', status: '', saving: false,
        previewPage: 1, selectedIds: [], confirmOpen: false,
        openConfirmation() {
            if (this.saving || this.selectedIds.length === 0) return;
            this.confirmOpen = true;
            this.$refs.matchConfirmation.showModal();
            this.$nextTick(() => this.$refs.cancelMatch.focus());
        },
        closeConfirmation() {
            if (this.saving) return;
            this.confirmOpen = false;
            this.$refs.matchConfirmation.close();
            this.$refs.matchTrigger.focus();
        },
        confirmMatching() {
            if (!this.confirmOpen || this.saving || this.selectedIds.length === 0) return;
            this.saving = true;
            this.$refs.matchForm.submit();
        },
        init() {
            ['search', 'status', 'factory', 'period'].forEach(field => this.$watch(field, () => { this.previewPage = 1; this.selectedIds = []; }));
        },
        get paginatedRows() { return this.filteredRows.slice((this.previewPage - 1) * 5, this.previewPage * 5); },
        get rows() {
            return this.samples.map(row => {
                const source = row.sources[this.factory]?.[this.period];
                const price = source == null ? null : Number(source);
                const old = row.prices[this.period] == null ? null : Number(row.prices[this.period]);
                return { ...row, old, price, difference: price === null || old === null ? null : price - old,
                    status: price === null ? 'missing' : price === old ? 'unchanged' : 'ready' };
            });
        },
        get filteredRows() {
            const keyword = this.search.trim().toLowerCase();
            return this.rows.filter(row => (!this.status || row.status === this.status)
                &amp;&amp; `${row.rm} ${row.fg} ${row.name}`.toLowerCase().includes(keyword));
        },
        count(status) { return this.rows.filter(row => row.status === status).length; },
        amount(value) { return value === null ? '—' : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value); },
        reset() { this.search = ''; this.status = ''; }
    }">
        @if (session('matching_success'))
            <div role="status" class="rounded-xl bg-success-50 p-4 text-sm text-success-700 dark:bg-success-500/15 dark:text-success-400">{{ session('matching_success') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl bg-error-50 p-4 text-sm text-error-700 dark:bg-error-500/15 dark:text-error-400">{{ $errors->first() }}</div>
        @endif

        <x-common.component-card :title="__('Price source')" :desc="__('Preview Finished Good prices for Raw Materials linked through Synonim.')">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="matching-factory" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Source factory') }}</label>
                    <select id="matching-factory" x-model="factory" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach (['cikampek' => 'Cikampek', 'semarang' => 'Semarang', 'surabaya' => 'Surabaya', 'palembang' => 'Palembang'] as $value => $label)
                            <option value="{{ $value }}">{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="matching-period" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Preview period') }}</label>
                    <select id="matching-period" x-model="period" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach ($periods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex flex-col gap-3 rounded-xl bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300 sm:flex-row sm:items-center sm:justify-between">
                <p>{{ __('FG source price → Synonim → RM Rupiah price') }}</p>
                <span class="w-fit rounded-full border border-gray-200 bg-white px-3 py-1 text-theme-xs dark:border-gray-700 dark:bg-gray-900">{{ __('Current remains unchanged') }}</span>
            </div>
        </x-common.component-card>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-live="polite">
            @foreach ([['label' => __('Mapped materials'), 'value' => 'rows.length'], ['label' => __('Ready to match'), 'value' => "count('ready')"], ['label' => __('Unchanged'), 'value' => "count('unchanged')"], ['label' => __('Missing price'), 'value' => "count('missing')"]] as $metric)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $metric['label'] }}</p>
                    <p class="mt-2 text-title-sm font-semibold text-gray-800 dark:text-white/90" x-text="{{ $metric['value'] }}"></p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Selected factory and period') }}</p>
                </div>
            @endforeach
        </div>

        <x-common.component-card :title="__('Matching preview')" :desc="__('Compare existing RM prices with FG source prices in IDR. Prices are copied directly without unit conversion.')">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                <div class="flex-1">
                    <label for="matching-search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search materials') }}</label>
                    <input id="matching-search" type="search" x-model="search" placeholder="{{ __('Search RM, FG, or description...') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500" />
                </div>
                <div class="lg:w-52">
                    <label for="matching-status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Status') }}</label>
                    <select id="matching-status" x-model="status" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" @click="reset()" class="h-11 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Reset filters') }}</button>
            </div>

            <div class="custom-scrollbar overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="w-full min-w-[950px] text-sm">
                    <thead class="bg-gray-50 text-theme-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-5 py-4 text-start font-medium">{{ __('Select') }}</th>
                            @foreach (['Raw Material', 'Source Finished Good', 'Existing RM price', 'Proposed RM price', 'Difference', 'Status'] as $heading)
                                <th scope="col" class="px-5 py-4 font-medium {{ in_array($heading, ['Existing RM price', 'Proposed RM price', 'Difference']) ? 'text-end' : 'text-start' }}">{{ __($heading) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="row in paginatedRows" :key="row.rm">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-5 py-4">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" x-model="selectedIds" :value="String(row.id)" :disabled="saving" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900" />
                                        <span class="sr-only">{{ __('Select material') }} <span x-text="row.rm"></span></span>
                                    </label>
                                </td>
                                <td class="px-5 py-4"><p class="font-medium text-gray-800 dark:text-white/90" x-text="row.rm"></p><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400" x-text="row.name"></p></td>
                                <td class="px-5 py-4"><p class="font-medium text-gray-800 dark:text-white/90" x-text="row.fg"></p><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400" x-text="row.fg_name"></p></td>
                                <td class="px-5 py-4 text-end tabular-nums text-gray-600 dark:text-gray-300" x-text="amount(row.old)"></td>
                                <td class="px-5 py-4 text-end font-semibold tabular-nums text-brand-600 dark:text-brand-400" x-text="amount(row.price)"></td>
                                <td class="px-5 py-4 text-end tabular-nums text-gray-600 dark:text-gray-300" x-text="(row.difference > 0 ? '+' : '') + amount(row.difference)"></td>
                                <td class="px-5 py-4"><span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-theme-xs font-medium" :class="{
                                    'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400': row.status === 'ready',
                                    'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300': row.status === 'unchanged',
                                    'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400': row.status === 'missing'
                                }" x-text="statuses[row.status]"></span></td>
                            </tr>
                        </template>
                        <template x-if="filteredRows.length === 0">
                            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">{{ __('No materials match your filters. Try another code or status.') }}</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('5 records per page') }}</p>
                <a href="{{ route('admin.maintenance.synonim.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">{{ __('Manage Synonim') }} <span aria-hidden="true" class="inline-block rtl:rotate-180">→</span></a>
            </div>
            <x-common.client-pagination page="previewPage" total="filteredRows.length" :label="__('Matching Preview pagination')" />
            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-300" aria-live="polite">
                <p><span x-text="selectedIds.length"></span> {{ __('materials selected across pages') }}</p>
                <button type="button" @click="selectedIds = []" :disabled="saving || selectedIds.length === 0" class="text-brand-600 disabled:opacity-40 dark:text-brand-400">{{ __('Clear selection') }}</button>
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Changing filters, factory, or period clears the selection.') }}</p>
            </div>
        </x-common.component-card>

        <x-common.component-card :title="__('Matching History')" :desc="__('Saved matching history, including unchanged and zero prices.')">
            <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">{{ __('Matching History') }}</span>
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('All factories and periods · Times in WIB') }}</p>
            </div>
            <div class="custom-scrollbar overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="w-full min-w-[1150px] text-sm">
                    <thead class="bg-gray-50 text-theme-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            @foreach (['Matched at', 'Raw Material', 'Source Finished Good', 'Source factory', 'Period', 'Price before', 'Matched price', 'Matched by', 'Status'] as $heading)
                                <th scope="col" class="whitespace-nowrap px-5 py-4 font-medium {{ in_array($heading, ['Price before', 'Matched price']) ? 'text-end' : 'text-start' }}">{{ __($heading) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($history as $entry)
                            <tr class="text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800/50">
                                <td class="whitespace-nowrap px-5 py-4">{{ \Carbon\Carbon::parse($entry->matched_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">{{ $entry->rm_code }}</td>
                                <td class="px-5 py-4">{{ $entry->fg_code }}</td>
                                <td class="px-5 py-4">{{ __(ucfirst($entry->factory)) }}</td>
                                <td class="whitespace-nowrap px-5 py-4">{{ __($periods[$entry->period]) }}</td>
                                <td class="px-5 py-4 text-end tabular-nums">{{ $entry->price_before === null ? '—' : number_format($entry->price_before, 2, ',', '.') }}</td>
                                <td class="px-5 py-4 text-end font-semibold tabular-nums text-gray-800 dark:text-white/90">{{ number_format($entry->price_after, 2, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-4">{{ $entry->user_name }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">{{ __('Matched') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Historical amounts are snapshots in IDR and do not change with the preview filters.') }}</p>
            @if ($history->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No matching history yet.') }}</p>
            @endif
            {{ $history->links() }}
            </div>
        </x-common.component-card>

        <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ __('Matching scope: LE and Quarter 1–4') }}</p>
                <p id="matching-save-note" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Only selected materials will be matched for LE and Quarter 1–4, including zero prices. Current remains unchanged.') }}</p>
            </div>
            <form x-ref="matchForm" method="POST" action="{{ route('admin.calculate.matching-price.store') }}" @submit.prevent="openConfirmation()">
                @csrf
                <input type="hidden" name="factory" :value="factory" />
                <template x-for="id in selectedIds" :key="id"><input type="hidden" name="material_ids[]" :value="id" /></template>
                <button x-ref="matchTrigger" type="submit" :disabled="saving || selectedIds.length === 0" aria-haspopup="dialog" aria-describedby="matching-save-note" class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-brand-600 dark:text-white dark:hover:bg-brand-700">{{ __('Match & Save') }} (<span x-text="selectedIds.length"></span>)</button>
            </form>
        </div>
        <dialog x-ref="matchConfirmation" x-cloak
            @cancel.prevent="closeConfirmation()"
            @keydown.escape.window="if (confirmOpen) closeConfirmation()"
            @click="if ($event.target === $refs.matchConfirmation) closeConfirmation()"
            aria-labelledby="matching-confirm-title" aria-describedby="matching-confirm-description"
            class="fixed inset-0 m-auto max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-0 text-gray-800 shadow-theme-xl backdrop:bg-gray-950/50 backdrop:backdrop-blur-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white/90">
            <div class="p-6 sm:p-8">
                <h2 id="matching-confirm-title" class="text-xl font-semibold">{{ __('Confirm Matching Price') }}</h2>
                <p id="matching-confirm-description" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Review the selection before updating RM prices.') }}</p>
                <dl class="mt-6 space-y-3 rounded-xl bg-gray-50 p-4 text-sm dark:bg-gray-800">
                    <div class="flex items-center justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">{{ __('Selected materials') }}</dt><dd class="font-semibold" x-text="selectedIds.length"></dd></div>
                    <div class="flex items-center justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">{{ __('Source factory') }}</dt><dd class="font-semibold capitalize" x-text="factory"></dd></div>
                    <div class="flex items-center justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">{{ __('Period') }}</dt><dd class="font-semibold">{{ __('LE and Quarter 1–4') }}</dd></div>
                </dl>
                <p class="mt-4 rounded-xl bg-warning-50 p-4 text-sm text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">{{ __('Selected RM prices will be replaced with FG prices, including zero prices. Current and USD prices remain unchanged.') }}</p>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button x-ref="cancelMatch" type="button" @click="closeConfirmation()" :disabled="saving" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Cancel') }}</button>
                    <button type="button" @click="confirmMatching()" :disabled="saving || selectedIds.length === 0" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-brand-600 dark:text-white dark:hover:bg-brand-700">
                        <span x-show="!saving">{{ __('Confirm & Save') }}</span><span x-show="saving" x-cloak role="status">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </div>
        </dialog>
    </div>
@endsection
