@extends('layouts.app')

@section('content')
    @php
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $periodOptions = ['aop' => __('AOP · January–December'), 'le' => __('LE · July–December')];
        $statStyles = [
            ['card' => 'border-gray-200 dark:border-gray-800', 'label' => 'text-gray-500 dark:text-gray-400', 'value' => 'text-gray-800 dark:text-white/90'],
            ['card' => 'border-brand-200 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10', 'label' => 'text-brand-600 dark:text-brand-400', 'value' => 'text-brand-700 dark:text-brand-300'],
            ['card' => 'border-success-200 bg-success-50 dark:border-success-500/30 dark:bg-success-500/10', 'label' => 'text-success-600 dark:text-success-400', 'value' => 'text-success-700 dark:text-success-300'],
        ];
        $selectClass = 'h-12 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
        $pagerButtonClass = 'rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800';
    @endphp

    <div class="min-w-0 max-w-full space-y-6" x-data="{
        period: @js($period),
        saving: false, loading: false, inputError: '', search: '', selectedArea: '', inputPeriod: 'aop',
        inputs: @js($inputs),
        areas: @js($areas),
        areaLabel() { const area = this.areas.find(area => String(area.id) === this.selectedArea); return area ? area.code + ' — ' + area.description : ''; },
        amount(value) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value) || 0); },
        async loadInputs(page) {
            if (this.loading || this.saving) return;
            this.loading = true; this.inputError = '';
            try {
                const params = new URLSearchParams({ search: this.search, input_page: page, area_id: this.selectedArea });
                const response = await fetch(@js(route('admin.calculate.volume-noodle.inputs')) + '?' + params, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error();
                this.inputs = await response.json();
            } catch (error) { this.inputError = @js(__('Data gagal dimuat.')); }
            finally { this.loading = false; }
        },
        inputPages() {
            const last = Number(this.inputs.last_page) || 1;
            let start = Math.max(1, (Number(this.inputs.current_page) || 1) - 2);
            start = Math.min(start, Math.max(1, last - 4));
            const end = Math.min(last, start + 4);
            return Array.from({ length: end - start + 1 }, (_, index) => start + index);
        },
        openScope() {
            if (this.loading || this.saving || !this.selectedArea || this.inputError) return;
            this.$refs.scope.showModal();
            this.$nextTick(() => this.$refs.closeScope.focus());
        },
        closeScope() { if (this.saving) return; this.$refs.scope.close(); this.$refs.scopeTrigger.focus(); },
        pageUrl(url) { const target = new URL(url); target.searchParams.set('period', this.period); return target.toString(); }
    }">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Calculate Volume Noodle') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Select an area. All its noodle inputs are calculated for AOP and LE.') }}</p>
                </div>
                <button x-ref="scopeTrigger" type="button" @click="openScope()" :disabled="saving || loading || !selectedArea || !!inputError" aria-haspopup="dialog" class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60">
                    <svg class="size-5" :class="saving && 'animate-spin'" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" /></svg>
                    <span x-text="saving ? @js(__('Saving...')) : @js(__('Calculate & Save'))"></span>
                </button>
            </div>

            @if (session('success'))
                <div role="status" class="border-b border-success-200 bg-success-50 px-6 py-3 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="border-b border-error-200 bg-error-50 px-6 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">{{ $errors->first() }}</div>
            @endif
            <div x-show="inputError" x-cloak role="alert" class="border-b border-error-200 bg-error-50 px-6 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300" x-text="inputError"></div>

            <div class="grid gap-4 border-b border-gray-200 p-6 dark:border-gray-800 lg:grid-cols-3">
                <div>
                    <label for="calculation-area" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Area Code') }}</label>
                    <select id="calculation-area" x-model="selectedArea" @change="search = ''; loadInputs(1)" :disabled="loading || saving" class="{{ $selectClass }}">
                        <option value="">{{ __('Select an area') }}</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->code }} — {{ $area->description }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <span class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Display period') }}</span>
                    <div class="grid grid-cols-2 gap-2 rounded-lg bg-gray-100 p-1 dark:bg-gray-800" role="group" aria-label="{{ __('Display period') }}">
                        @foreach ($periodOptions as $key => $label)
                            <button type="button" @click="inputPeriod = '{{ $key }}'" :aria-pressed="inputPeriod === '{{ $key }}'" :class="inputPeriod === '{{ $key }}' ? 'bg-white text-brand-600 shadow-theme-xs dark:bg-gray-900 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400'" class="h-10 rounded-md text-sm font-medium transition">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
                <form @submit.prevent="loadInputs(1)">
                    <label for="input-search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search area or noodle') }}</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg>
                        <input id="input-search" type="search" x-model="search" :disabled="saving || loading" placeholder="{{ __('Search area or noodle') }}" class="h-12 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:opacity-60 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </form>
            </div>

            <div class="grid gap-4 border-b border-gray-200 p-6 dark:border-gray-800 sm:grid-cols-3">
                @foreach ($sourceCounts as $label => $count)
                    @php($style = $statStyles[$loop->index % count($statStyles)])
                    <div class="rounded-xl border p-4 {{ $style['card'] }}">
                        <p class="text-theme-xs font-medium uppercase tracking-wide {{ $style['label'] }}">{{ __($label) }}</p>
                        <p class="mt-2 text-2xl font-semibold {{ $style['value'] }}">{{ number_format($count) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="px-6 pt-5">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Calculate FG requirements') }}</h2>
                <p class="mt-1 mb-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Search and pagination only filter this table; all noodle inputs in the selected area are calculated.') }}</p>
            </div>

            <div class="relative overflow-x-auto border-t border-gray-200 dark:border-gray-800" :aria-busy="loading">
                <div x-show="loading" x-cloak class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 text-sm font-medium text-gray-600 dark:bg-gray-900/70 dark:text-gray-300">{{ __('Memuat data...') }}</div>
                <table class="w-full divide-y divide-gray-200 whitespace-nowrap dark:divide-gray-800">
                    <thead class="bg-gray-50 text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            @foreach (['Area Code', 'Noodle Code', 'Description'] as $label)
                                <th scope="col" class="px-5 py-3 text-start">{{ __($label) }}</th>
                            @endforeach
                            @foreach ($months as $index => $month)
                                <th scope="col" @if ($index < 6) x-show="inputPeriod === 'aop'" @endif class="px-5 py-3 text-end">{{ __($month) }}</th>
                            @endforeach
                            <th scope="col" class="px-5 py-3 text-end"><span x-show="inputPeriod === 'aop'">{{ __('Total AOP') }}</span><span x-show="inputPeriod === 'le'" x-cloak>{{ __('Total LE') }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="row in inputs.data" :key="row.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90" x-text="row.area_noodle?.code"></td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90" x-text="row.noodle?.code"></td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300" x-text="row.noodle?.description"></td>
                                @foreach ($months as $index => $month)
                                    @php($field = strtolower($month))
                                    <td @if ($index < 6) x-show="inputPeriod === 'aop'" @endif class="px-5 py-4 text-end text-sm tabular-nums text-gray-600 dark:text-gray-300" x-text="amount(row[inputPeriod === 'le' ? 'le_{{ $field }}' : '{{ $field }}'])"></td>
                                @endforeach
                                <td class="px-5 py-4 text-end text-sm font-semibold tabular-nums text-brand-600 dark:text-brand-400" x-text="amount(row[inputPeriod === 'le' ? 'total_le' : 'total_aop'])"></td>
                            </tr>
                        </template>
                        <template x-if="inputs.data.length === 0"><tr><td :colspan="inputPeriod === 'aop' ? 16 : 10" class="px-6 py-14 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No volume inputs found.') }}</td></tr></template>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="inputs.total ? `{{ __('Menampilkan') }} ${inputs.from}–${inputs.to} {{ __('dari') }} ${inputs.total} {{ __('data') }}` : '{{ __('Tidak ada data') }}'"></p>
                <nav class="flex items-center gap-1" aria-label="{{ __('Volume input pagination') }}">
                    <button type="button" @click="loadInputs(inputs.current_page - 1)" :disabled="loading || saving || inputs.current_page <= 1" class="{{ $pagerButtonClass }}">{{ __('Sebelumnya') }}</button>
                    <template x-for="page in inputPages()" :key="page"><button type="button" @click="loadInputs(page)" :disabled="loading || saving" :aria-current="page === inputs.current_page ? 'page' : null" :class="page === inputs.current_page ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800'" class="size-9 rounded-lg border text-sm" x-text="page"></button></template>
                    <button type="button" @click="loadInputs(inputs.current_page + 1)" :disabled="loading || saving || inputs.current_page >= inputs.last_page" class="{{ $pagerButtonClass }}">{{ __('Berikutnya') }}</button>
                </nav>
            </div>

            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                <p>{{ __('Only the selected area is recalculated. Other areas remain unchanged.') }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('FG volume results') }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Results are grouped by area and FG. Switching the display period does not change the calculation scope.') }}</p>
            </div>

            <div class="grid gap-4 border-b border-gray-200 p-6 dark:border-gray-800 lg:grid-cols-3">
                <div>
                    <span class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Display period') }}</span>
                    <div class="grid grid-cols-2 gap-2 rounded-lg bg-gray-100 p-1 dark:bg-gray-800" role="group" aria-label="{{ __('Display period') }}">
                        @foreach ($periodOptions as $key => $label)
                            <button type="button" @click="period = '{{ $key }}'" :aria-pressed="period === '{{ $key }}'" :class="period === '{{ $key }}' ? 'bg-white text-brand-600 shadow-theme-xs dark:bg-gray-900 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400'" class="h-10 rounded-md text-sm font-medium transition">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
                <form method="GET" action="{{ route('admin.calculate.volume-noodle.index') }}" class="contents">
                    <input type="hidden" name="period" :value="period">
                    <div>
                        <label for="result-area" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Filter by Area Code') }}</label>
                        <select id="result-area" name="result_area" @change="$el.form.requestSubmit()" class="{{ $selectClass }}">
                            <option value="">{{ __('All Area Codes') }}</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->code }}" @selected($resultArea === $area->code)>{{ $area->code }} — {{ $area->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="volume-per-page" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Rows per page') }}</label>
                        <select id="volume-per-page" name="per_page" @change="$el.form.requestSubmit()" class="{{ $selectClass }}">
                            @foreach ([5, 10, 20] as $size)
                                <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div class="px-6 pt-5">
                <p class="mb-4 text-theme-xs text-gray-500 dark:text-gray-400">
                    @if ($calculation?->calculated_at)
                        {{ __('Last area calculation') }}: <span class="font-medium text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($calculation->calculated_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>.
                        {{ __('Recalculate the affected area after changing inputs or formulas.') }}
                    @else
                        {{ __('Not calculated yet') }}
                    @endif
                </p>
            </div>

            <div class="overflow-x-auto custom-scrollbar border-t border-gray-200 dark:border-gray-800">
                <table class="w-full divide-y divide-gray-200 whitespace-nowrap dark:divide-gray-800">
                    <caption class="sr-only">{{ __('FG requirements per area and month') }}</caption>
                    <thead class="bg-gray-50 text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            @foreach (['Area Code', 'Code FG', 'Description', 'Product Type 1', 'Product Type 2'] as $label)
                                <th scope="col" class="px-5 py-3 text-start">{{ __($label) }}</th>
                            @endforeach
                            @foreach ($months as $index => $month)
                                <th scope="col" @if ($index < 6) x-show="period === 'aop'" @endif class="px-5 py-3 text-end">{{ __($month) }}</th>
                            @endforeach
                            <th scope="col" class="px-5 py-3 text-end">
                                <span x-show="period === 'aop'">{{ __('Total AOP') }}</span>
                                <span x-show="period === 'le'" x-cloak>{{ __('Total LE') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($results as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                @foreach (['area_code', 'fg_code', 'description', 'product_type_1', 'product_type_2'] as $field)
                                    <td class="px-5 py-4 text-sm {{ in_array($field, ['area_code', 'fg_code']) ? 'font-medium text-gray-800 dark:text-white/90' : 'text-gray-600 dark:text-gray-300' }}">{{ $row->{$field} }}</td>
                                @endforeach
                                @foreach ($months as $index => $month)
                                    @php($field = strtolower($month))
                                    <td @if ($index < 6) x-show="period === 'aop'" @endif class="px-5 py-4 text-end text-sm tabular-nums text-gray-600 dark:text-gray-300">
                                        @if ($index < 6)
                                            {{ number_format($row->{$field}, 2, ',', '.') }}
                                        @else
                                            <span x-show="period === 'aop'">{{ number_format($row->{$field}, 2, ',', '.') }}</span>
                                            <span x-show="period === 'le'" x-cloak>{{ number_format($row->{'le_'.$field}, 2, ',', '.') }}</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-5 py-4 text-end text-sm font-semibold tabular-nums text-brand-600 dark:text-brand-400">
                                    <span x-show="period === 'aop'">{{ number_format($row->total_aop, 2, ',', '.') }}</span>
                                    <span x-show="period === 'le'" x-cloak>{{ number_format($row->total_le, 2, ',', '.') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($results->isEmpty())
                <div class="border-t border-gray-200 px-6 py-14 text-center dark:border-gray-800">
                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ __('No FG volume results to display.') }}</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Review volume inputs and noodle formulas, then run Calculate & Save.') }}</p>
                </div>
            @endif

            <div class="flex flex-col gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if ($results->total())
                        {{ __('Menampilkan') }} {{ $results->firstItem() }}–{{ $results->lastItem() }} {{ __('dari') }} {{ $results->total() }} {{ __('data') }}
                    @else
                        {{ __('Tidak ada data') }}
                    @endif
                </p>
                <nav class="flex flex-wrap items-center gap-1" aria-label="{{ __('FG volume pagination') }}">
                    @if ($results->onFirstPage())
                        <span aria-disabled="true" class="{{ $pagerButtonClass }} cursor-not-allowed opacity-40">{{ __('Sebelumnya') }}</span>
                    @else
                        <a href="{{ $results->previousPageUrl() }}" :href="pageUrl(@js($results->previousPageUrl()))" class="{{ $pagerButtonClass }}">{{ __('Sebelumnya') }}</a>
                    @endif
                    @for ($page = max(1, min($results->currentPage() - 2, $results->lastPage() - 4)); $page <= min($results->lastPage(), max(5, $results->currentPage() + 2)); $page++)
                        <a href="{{ $results->url($page) }}" :href="pageUrl(@js($results->url($page)))" @if ($page === $results->currentPage()) aria-current="page" @endif class="inline-flex size-9 items-center justify-center rounded-lg border text-sm {{ $page === $results->currentPage() ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800' }}">{{ $page }}</a>
                    @endfor
                    @if ($results->hasMorePages())
                        <a href="{{ $results->nextPageUrl() }}" :href="pageUrl(@js($results->nextPageUrl()))" class="{{ $pagerButtonClass }}">{{ __('Berikutnya') }}</a>
                    @else
                        <span aria-disabled="true" class="{{ $pagerButtonClass }} cursor-not-allowed opacity-40">{{ __('Berikutnya') }}</span>
                    @endif
                </nav>
            </div>
        </section>

        <dialog x-ref="scope" x-cloak @cancel.prevent="closeScope()" @click="if ($event.target === $refs.scope) closeScope()" aria-labelledby="volume-scope-title" aria-describedby="volume-scope-description" class="fixed inset-0 m-auto max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-0 text-gray-800 shadow-theme-xl backdrop:bg-gray-950/50 backdrop:backdrop-blur-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white/90">
            <div class="p-6 sm:p-8">
                <h2 id="volume-scope-title" class="text-xl font-semibold">{{ __('Review calculation scope') }}</h2>
                <p id="volume-scope-description" class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ __('Calculate all noodle inputs for area') }} <strong x-text="areaLabel()"></strong>?
                    {{ __('All AOP and LE months are included.') }}</p>
                <p class="mt-4 rounded-xl bg-warning-50 p-4 text-sm text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">{{ __('Only results for the selected area will be replaced. Other areas and original inputs remain unchanged.') }}</p>
                <form method="POST" action="{{ route('admin.calculate.volume-noodle.store') }}" @submit="if (saving || loading || !selectedArea || !!inputError) { $event.preventDefault(); return; } saving = true" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    @csrf
                    <input type="hidden" name="confirm" value="1">
                    <input type="hidden" name="area_id" :value="selectedArea">
                    <button x-ref="closeScope" type="button" @click="closeScope()" :disabled="saving" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Cancel') }}</button>
                    <button type="submit" :disabled="saving" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 dark:bg-brand-600 dark:text-white dark:hover:bg-brand-700">
                        <span x-show="!saving">{{ __('Confirm & Save') }}</span>
                        <span x-show="saving" x-cloak role="status">{{ __('Saving...') }}</span>
                    </button>
                </form>
            </div>
        </dialog>
    </div>
@endsection
