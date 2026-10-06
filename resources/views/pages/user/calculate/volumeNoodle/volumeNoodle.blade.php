@extends('layouts.app')

@section('content')
    @php
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    @endphp
    <div class="min-w-0 space-y-6" x-data="{
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
        closeScope() { if (this.saving) return; this.$refs.scope.close(); this.$refs.scopeTrigger.focus(); },
        pageUrl(url) { const target = new URL(url); target.searchParams.set('period', this.period); return target.toString(); }
    }">
        <x-common.page-breadcrumb :pageTitle="__('Calculate Volume Noodle')" />

        @if (session('success'))
            <div role="status" class="rounded-xl border border-success-200 bg-success-50 p-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($sourceCounts as $label => $count)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __($label) }}</p>
                    <p class="mt-2 text-title-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format($count) }}</p>
                </div>
            @endforeach
        </div>

        <x-common.component-card :title="__('Calculate FG requirements')" :desc="__('Select an area. All its noodle inputs are calculated for AOP and LE.')">
            <div class="flex flex-wrap items-center gap-3">
                <label for="calculation-area" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Area Code') }}</label>
                <select id="calculation-area" x-model="selectedArea" @change="search = ''; loadInputs(1)" :disabled="loading || saving" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">{{ __('Select an area') }}</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->code }} — {{ $area->description }}</option>
                    @endforeach
                </select>
            </div>
            <form @submit.prevent="loadInputs(1)" class="flex flex-wrap items-center gap-3">
                <label for="input-search" class="sr-only">{{ __('Search area or noodle') }}</label>
                <input id="input-search" type="search" x-model="search" :disabled="saving || loading" placeholder="{{ __('Search area or noodle') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <button type="submit" :disabled="loading || saving" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300">{{ __('Search') }}</button>
                <label for="input-period" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Display period') }}</label>
                <select id="input-period" x-model="inputPeriod" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="aop">{{ __('AOP · January–December') }}</option>
                    <option value="le">{{ __('LE · July–December') }}</option>
                </select>
            </form>
            <p x-show="inputError" x-cloak x-text="inputError" role="alert" class="text-sm text-error-600 dark:text-error-400"></p>
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800" :aria-busy="loading">
                <table class="w-full whitespace-nowrap text-sm">
                    <thead class="bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            @foreach (['Area Code', 'Noodle Code', 'Description'] as $label)
                                <th scope="col" class="px-4 py-3 text-start font-medium">{{ __($label) }}</th>
                            @endforeach
                            @foreach ($months as $index => $month)
                                <th scope="col" @if ($index < 6) x-show="inputPeriod === 'aop'" @endif class="px-4 py-3 text-end font-medium">{{ __($month) }}</th>
                            @endforeach
                            <th scope="col" class="px-4 py-3 text-end font-medium"><span x-show="inputPeriod === 'aop'">{{ __('Total AOP') }}</span><span x-show="inputPeriod === 'le'" x-cloak>{{ __('Total LE') }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 dark:divide-gray-800 dark:text-gray-300">
                        <template x-for="row in inputs.data" :key="row.id">
                            <tr>
                                <td class="px-4 py-3" x-text="row.area_noodle?.code"></td>
                                <td class="px-4 py-3" x-text="row.noodle?.code"></td>
                                <td class="px-4 py-3" x-text="row.noodle?.description"></td>
                                @foreach ($months as $index => $month)
                                    @php($field = strtolower($month))
                                    <td @if ($index < 6) x-show="inputPeriod === 'aop'" @endif class="px-4 py-3 text-end tabular-nums" x-text="amount(row[inputPeriod === 'le' ? 'le_{{ $field }}' : '{{ $field }}'])"></td>
                                @endforeach
                                <td class="px-4 py-3 text-end font-semibold tabular-nums" x-text="amount(row[inputPeriod === 'le' ? 'total_le' : 'total_aop'])"></td>
                            </tr>
                        </template>
                        <template x-if="inputs.data.length === 0"><tr><td :colspan="inputPeriod === 'aop' ? 16 : 10" class="px-4 py-8 text-center">{{ __('No volume inputs found.') }}</td></tr></template>
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Showing') }} <span x-text="inputs.from"></span>–<span x-text="inputs.to"></span> {{ __('of') }} <span x-text="inputs.total"></span> {{ __('records') }}</p>
                <nav class="flex gap-2" aria-label="{{ __('Volume input pagination') }}">
                    <button type="button" @click="loadInputs(inputs.current_page - 1)" :disabled="loading || saving || inputs.current_page <= 1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">{{ __('Previous') }}</button>
                    <span class="px-2 py-2 text-sm text-gray-500 dark:text-gray-400"><span x-text="inputs.current_page"></span> / <span x-text="inputs.last_page"></span></span>
                    <button type="button" @click="loadInputs(inputs.current_page + 1)" :disabled="loading || saving || inputs.current_page >= inputs.last_page" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">{{ __('Next') }}</button>
                </nav>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Only the selected area is recalculated. Other areas remain unchanged.') }}
                    <p class="mt-1">{{ __('Search and pagination only filter this table; all noodle inputs in the selected area are calculated.') }}</p>
                </div>
                <button x-ref="scopeTrigger" type="button" @click="if (!loading && selectedArea && !inputError) { $refs.scope.showModal(); $nextTick(() => $refs.closeScope.focus()); }" :disabled="saving || loading || !selectedArea || !!inputError" aria-haspopup="dialog" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-brand-600 dark:text-white">{{ __('Calculate & Save') }}</button>
            </div>
        </x-common.component-card>

        <x-common.component-card :title="__('FG volume results')" :desc="__('Results are grouped by area and FG. Switching the display period does not change the calculation scope.')">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800" role="group" aria-label="{{ __('Display period') }}">
                    @foreach (['aop' => 'AOP · January–December', 'le' => 'LE · July–December'] as $key => $label)
                        <button type="button" @click="period = '{{ $key }}'" :aria-pressed="period === '{{ $key }}'" :class="period === '{{ $key }}' ? 'bg-white text-brand-600 shadow-theme-xs dark:bg-gray-900 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400'" class="rounded-md px-4 py-2.5 text-sm font-medium">{{ __($label) }}</button>
                    @endforeach
                </div>
                <form method="GET" action="{{ route('admin.calculate.volume-noodle.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="period" :value="period">
                    <label for="volume-per-page" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Rows per page') }}</label>
                    <select id="volume-per-page" name="per_page" @change="$el.form.requestSubmit()" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        @foreach ([5, 10, 20] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                @if ($calculation?->calculated_at)
                    {{ __('Last area calculation') }}: {{ \Carbon\Carbon::parse($calculation->calculated_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB.
                    {{ __('Recalculate the affected area after changing inputs or formulas.') }}
                @else
                    {{ __('Not calculated yet') }}
                @endif
            </p>
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full whitespace-nowrap text-sm">
                        <caption class="sr-only">{{ __('FG requirements per area and month') }}</caption>
                        <thead class="bg-gray-50 text-theme-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                @foreach (['Area Code', 'Code FG', 'Description', 'Product Type 1', 'Product Type 2'] as $label)
                                    <th scope="col" class="px-4 py-3 text-start font-medium">{{ __($label) }}</th>
                                @endforeach
                                @foreach ($months as $index => $month)
                                    <th scope="col" @if ($index < 6) x-show="period === 'aop'" @endif class="px-4 py-3 text-end font-medium">{{ __($month) }}</th>
                                @endforeach
                                <th scope="col" class="px-4 py-3 text-end font-medium">
                                    <span x-show="period === 'aop'">{{ __('Total AOP') }}</span>
                                    <span x-show="period === 'le'" x-cloak>{{ __('Total LE') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white text-gray-700 dark:divide-gray-800 dark:bg-gray-900 dark:text-gray-300">
                            @foreach ($results as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    @foreach (['area_code', 'fg_code', 'description', 'product_type_1', 'product_type_2'] as $field)
                                        <td class="px-4 py-3">{{ $row->{$field} }}</td>
                                    @endforeach
                                    @foreach ($months as $index => $month)
                                        @php($field = strtolower($month))
                                        <td @if ($index < 6) x-show="period === 'aop'" @endif class="px-4 py-3 text-end tabular-nums">
                                            @if ($index < 6)
                                                {{ number_format($row->{$field}, 2, ',', '.') }}
                                            @else
                                                <span x-show="period === 'aop'">{{ number_format($row->{$field}, 2, ',', '.') }}</span>
                                                <span x-show="period === 'le'" x-cloak>{{ number_format($row->{'le_'.$field}, 2, ',', '.') }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-end font-semibold tabular-nums">
                                        <span x-show="period === 'aop'">{{ number_format($row->total_aop, 2, ',', '.') }}</span>
                                        <span x-show="period === 'le'" x-cloak>{{ number_format($row->total_le, 2, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($results->isEmpty())
                    <div class="border-t border-gray-200 px-6 py-10 text-center dark:border-gray-800">
                        <p class="font-medium text-gray-800 dark:text-white/90">{{ __('No FG volume results to display.') }}</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Review volume inputs and noodle formulas, then run Calculate & Save.') }}</p>
                    </div>
                @endif
            </div>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Showing') }} {{ $results->firstItem() ?? 0 }}–{{ $results->lastItem() ?? 0 }} {{ __('of') }} {{ $results->total() }} {{ __('records') }}</p>
                <nav class="flex flex-wrap items-center gap-2" aria-label="{{ __('FG volume pagination') }}">
                    @if ($results->onFirstPage())
                        <span aria-disabled="true" class="px-3 py-2 text-sm text-gray-400 dark:text-gray-600">{{ __('Previous') }}</span>
                    @else
                        <a href="{{ $results->previousPageUrl() }}" :href="pageUrl(@js($results->previousPageUrl()))" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Previous') }}</a>
                    @endif
                    @for ($page = max(1, min($results->currentPage() - 2, $results->lastPage() - 4)); $page <= min($results->lastPage(), max(5, $results->currentPage() + 2)); $page++)
                        <a href="{{ $results->url($page) }}" :href="pageUrl(@js($results->url($page)))" @if ($page === $results->currentPage()) aria-current="page" @endif class="rounded-lg border px-3 py-2 text-sm {{ $page === $results->currentPage() ? 'border-brand-500 bg-brand-500 text-white dark:border-brand-600 dark:bg-brand-600 dark:text-white' : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300' }}">{{ $page }}</a>
                    @endfor
                    @if ($results->hasMorePages())
                        <a href="{{ $results->nextPageUrl() }}" :href="pageUrl(@js($results->nextPageUrl()))" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Next') }}</a>
                    @else
                        <span aria-disabled="true" class="px-3 py-2 text-sm text-gray-400 dark:text-gray-600">{{ __('Next') }}</span>
                    @endif
                </nav>
            </div>
        </x-common.component-card>

        <dialog x-ref="scope" @cancel.prevent="closeScope()" @click="if ($event.target === $refs.scope) closeScope()" aria-labelledby="volume-scope-title" class="fixed inset-0 m-auto max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 text-gray-800 shadow-theme-xl backdrop:bg-gray-950/50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90">
            <h2 id="volume-scope-title" class="text-xl font-semibold">{{ __('Review calculation scope') }}</h2>
            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ __('Calculate all noodle inputs for area') }} <strong x-text="areaLabel()"></strong>?
                {{ __('All AOP and LE months are included.') }}</p>
            <p class="mt-4 rounded-xl bg-warning-50 p-4 text-sm text-warning-800 dark:bg-warning-500/10 dark:text-warning-300">{{ __('Only results for the selected area will be replaced. Other areas and original inputs remain unchanged.') }}</p>
            <form method="POST" action="{{ route('admin.calculate.volume-noodle.store') }}" @submit="if (saving || loading || !selectedArea || !!inputError) { $event.preventDefault(); return; } saving = true" class="mt-6 flex justify-end gap-3">
                @csrf
                <input type="hidden" name="confirm" value="1">
                <input type="hidden" name="area_id" :value="selectedArea">
                <button x-ref="closeScope" type="button" @click="closeScope()" :disabled="saving" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm text-gray-700 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300">{{ __('Cancel') }}</button>
                <button type="submit" :disabled="saving" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 dark:bg-brand-600 dark:text-white dark:hover:bg-brand-700">
                    <span x-show="!saving">{{ __('Confirm & Save') }}</span>
                    <span x-show="saving" x-cloak role="status">{{ __('Saving...') }}</span>
                </button>
            </form>
        </dialog>
    </div>
@endsection
