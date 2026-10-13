@extends('layouts.app')

@section('content')
    @php
        $money = fn ($value) => (float) $value == 0.0 ? '-' : number_format((float) $value, 2, ',', '.');
        $aopYear = \App\Http\Controllers\Admin\Reporting\SellUCUPController::aopYear($selectedReference);
        $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
        $columnCount = 2 + (count($blocks) * count($periods));
        $group = null;
        // Lebar kolom Kode FG dikunci (w-28) agar kolom Finished Goods bisa menempel tepat di start-28.
        // Garis pemisah memakai pseudo-element karena border tabel tidak ikut menempel saat di-scroll.
        $stickyEdge = 'after:absolute after:inset-y-0 after:end-0 after:w-px after:bg-gray-200 dark:after:bg-gray-700';
        $stickyCode = "sticky start-0 z-1 w-28 min-w-28 max-w-28 break-all {$stickyEdge}";
        $stickyName = "sticky start-28 z-1 w-72 min-w-72 max-w-72 {$stickyEdge}";
    @endphp

    <div class="space-y-6">
        <x-common.component-card
            :title="__('Report Filter')"
            :desc="__('Select a reference and Finished Good code range to prepare the selling price and unit cost report.')">
            <form method="GET" action="{{ route('admin.reporting.sell-uc-up.index') }}" class="space-y-5">
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div class="xl:col-span-2">
                        <label for="reference-id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Referensi') }}
                        </label>
                        <select id="reference-id" name="reference_id" class="{{ $inputClass }}">
                            @forelse ($references as $reference)
                                <option value="{{ $reference->id }}" @selected(optional($selectedReference)->id === $reference->id)>
                                    {{ $reference->code }} - {{ $reference->period_description }} - {{ $reference->description_1 }}
                                </option>
                            @empty
                                <option value="">{{ __('No reference available') }}</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label for="code-from" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Kode FG Dari') }}
                        </label>
                        <input id="code-from" name="code_from" value="{{ $filters['codeFrom'] }}" type="text" maxlength="30" placeholder="{{ __('Starting code') }}" class="{{ $inputClass }} uppercase placeholder:normal-case" />
                    </div>

                    <div>
                        <label for="code-to" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Kode FG Sampai') }}
                        </label>
                        <input id="code-to" name="code_to" value="{{ $filters['codeTo'] }}" type="text" maxlength="30" placeholder="{{ __('Ending code') }}" class="{{ $inputClass }} uppercase placeholder:normal-case" />
                    </div>

                    @foreach (['product_type_1' => ['label' => __('Product Type 1'), 'filter' => 'productType1', 'options' => $productTypes1], 'product_type_2' => ['label' => __('Product Type 2'), 'filter' => 'productType2', 'options' => $productTypes2]] as $name => $meta)
                        <div>
                            <label for="{{ str_replace('_', '-', $name) }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $meta['label'] }}
                            </label>
                            <select id="{{ str_replace('_', '-', $name) }}" name="{{ $name }}" class="{{ $inputClass }}">
                                <option value="">{{ __('Semua Product Type') }}</option>
                                @foreach ($meta['options'] as $option)
                                    <option value="{{ $option }}" @selected($filters[$meta['filter']] === (string) $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-4 border-t border-gray-100 pt-5 dark:border-gray-800 lg:flex-row lg:items-end lg:justify-between">
                    <div class="w-full lg:max-w-md">
                        <label for="report-search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Cari Finished Good') }}
                        </label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" />
                            </svg>
                            <input id="report-search" name="search" value="{{ $filters['search'] }}" type="search" placeholder="{{ __('Code or description') }}" class="{{ $inputClass }} ps-11 pe-4" />
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('admin.reporting.sell-uc-up.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            {{ __('Reset') }}
                        </a>
                        <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/20">
                            <svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 12h16m-6-6 6 6-6 6" /></svg>
                            {{ __('Show Report') }}
                        </button>
                    </div>
                </div>
            </form>
        </x-common.component-card>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <x-common.batched-excel-download route="admin.reporting.sell-uc-up.excel" :stage="__('Mengambil data FG...')" filename="selling-price-unit-cost.xlsx" class="border-b border-gray-200 dark:border-gray-800">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Selling Price & Unit Cost') }}</h1>
                    @if ($aopYear)
                        <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                            {{ __('AOP Year :year', ['year' => $aopYear]) }}
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($selectedReference)
                        {{ $selectedReference->description_1 }}{{ $selectedReference->description_2 ? ' · '.$selectedReference->description_2 : '' }}
                    @else
                        {{ __('Select or create a Reference to display report header.') }}
                    @endif
                </p>
                <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">
                    {{ __('Finished Goods shown: :count', ['count' => number_format($finishedGoods->total())]) }}
                    · {{ __('Values come from the last U.Cost+U.Price calculation.') }}
                </p>
            </x-common.batched-excel-download>

            <div class="custom-scrollbar overflow-x-auto">
                <table class="min-w-[3400px] border-collapse">
                    <thead class="bg-gray-50 text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            {{-- Kode FG dan Finished Goods tetap terlihat saat tabel digeser ke samping. --}}
                            <th rowspan="2" class="{{ $stickyCode }} bg-gray-50 px-5 py-3 text-start dark:bg-gray-800">{{ __('Kode FG') }}</th>
                            <th rowspan="2" class="{{ $stickyName }} bg-gray-50 px-5 py-3 text-start dark:bg-gray-800">{{ __('Finished Goods') }}</th>
                            @foreach ($blocks as $block)
                                <th colspan="{{ count($periods) }}" class="border-e border-gray-200 px-4 py-3 text-center text-brand-600 last:border-e-0 dark:border-gray-700 dark:text-brand-400">{{ __($block['label']) }}</th>
                            @endforeach
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            @foreach ($blocks as $block)
                                @foreach ($periods as $label)
                                    <th class="w-28 border-e border-gray-200 px-3 py-2 text-end last:border-e-0 dark:border-gray-700">{{ __($label) }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($finishedGoods as $finishedGood)
                            @php($currentGroup = $finishedGood->product_type_1.'/'.$finishedGood->product_type_2)
                            @if ($currentGroup !== $group)
                                @php($group = $currentGroup)
                                <tr class="bg-gray-50 dark:bg-gray-800/40">
                                    <td colspan="{{ $columnCount }}" class="px-5 py-2 text-theme-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        <span class="sticky start-5">{{ __('Product Type :type1 / :type2', ['type1' => $finishedGood->product_type_1, 'type2' => $finishedGood->product_type_2]) }}</span>
                                    </td>
                                </tr>
                            @endif
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="{{ $stickyCode }} bg-white px-5 py-3 text-sm font-semibold text-gray-800 group-hover:bg-gray-50 dark:bg-gray-900 dark:text-white/90 dark:group-hover:bg-gray-800">{{ $finishedGood->code }}</td>
                                <td class="{{ $stickyName }} bg-white px-5 py-3 text-sm text-gray-700 group-hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:group-hover:bg-gray-800">{{ $finishedGood->description }}</td>
                                @foreach ($blocks as $block)
                                    @foreach (array_keys($periods) as $period)
                                        @php($value = $finishedGood->{$block['prefix'].'_'.$period})
                                        <td class="border-e border-gray-100 px-3 py-3 text-end text-sm tabular-nums last:border-e-0 dark:border-gray-800 {{ (float) $value == 0.0 ? 'text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $money($value) }}</td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $columnCount }}" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 5h16v14H4V5Zm0 5h16M9 5v14" /></svg>
                                    </div>
                                    <p class="mt-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ __('No Finished Good with unit cost or selling price found') }}</p>
                                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Run U.Cost+U.Price first, or adjust the code range or search filter.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($finishedGoods->hasPages())
                <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                    {{ $finishedGoods->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
