@extends('layouts.app')

@section('content')
    @php
        $periods = [
            'current' => ['label' => __('Current'), 'rate' => 'rate_current'],
            'le' => ['label' => __('LE'), 'rate' => 'rate_le'],
            'qtr_1' => ['label' => __('Kuartal 1'), 'rate' => 'rate_1'],
            'qtr_2' => ['label' => __('Kuartal 2'), 'rate' => 'rate_2'],
            'qtr_3' => ['label' => __('Kuartal 3'), 'rate' => 'rate_3'],
            'qtr_4' => ['label' => __('Kuartal 4'), 'rate' => 'rate_4'],
        ];
    @endphp

    <!-- <div class="print:hidden">
        <x-common.page-breadcrumb :pageTitle="__('RM Price Budget')" />
    </div> -->

    <div class="space-y-6">
        <x-common.component-card
            class="print:hidden"
            :title="__('Report Filter')"
            :desc="__('Select a reference and Raw Material code range to prepare the budget price report.')">
            <form method="GET" action="{{ route('admin.reporting.rm-price-budget.index') }}" class="space-y-5">
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div class="xl:col-span-2">
                        <label for="reference-id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Referensi') }}
                        </label>
                        <select id="reference-id" name="reference_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @forelse ($references as $reference)
                                <option value="{{ $reference->id }}" @selected($selectedReference?->id === $reference->id)>
                                    {{ $reference->code }} - {{ $reference->period_description }} - {{ $reference->description_1 }}
                                </option>
                            @empty
                                <option value="">{{ __('No reference available') }}</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label for="code-from" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Kode RM Dari') }}
                        </label>
                        <input id="code-from" name="code_from" value="{{ $filters['codeFrom'] }}" type="text" maxlength="30" placeholder="{{ __('Starting code') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm uppercase text-gray-800 shadow-theme-xs outline-hidden placeholder:normal-case placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label for="code-to" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Kode RM Sampai') }}
                        </label>
                        <input id="code-to" name="code_to" value="{{ $filters['codeTo'] }}" type="text" maxlength="30" placeholder="{{ __('Ending code') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm uppercase text-gray-800 shadow-theme-xs outline-hidden placeholder:normal-case placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                <div class="flex flex-col gap-4 border-t border-gray-100 pt-5 dark:border-gray-800 lg:flex-row lg:items-end lg:justify-between">
                    <div class="w-full lg:max-w-md">
                        <label for="report-search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Cari Raw Material') }}
                        </label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" />
                            </svg>
                            <input id="report-search" name="search" value="{{ $filters['search'] }}" type="search" placeholder="{{ __('Code, description, or material ID') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent ps-11 pe-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <label for="filled-only" class="inline-flex cursor-pointer items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                            <input id="filled-only" name="filled_only" value="1" type="checkbox" @checked($filters['filledOnly']) class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900" />
                            <span>{{ __('Only show materials with prices') }}</span>
                        </label>
                        <div class="flex gap-3">
                            <a href="{{ route('admin.reporting.rm-price-budget.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                {{ __('Reset') }}
                            </a>
                            <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/20">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 12h16m-6-6 6 6-6 6" /></svg>
                                {{ __('Show Report') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </x-common.component-card>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="px-6 py-5">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('RM Budget Price') }}</h1>
                        @if ($selectedReference)
                            <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ $selectedReference->period_description }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($selectedReference)
                            {{ $selectedReference->description_1 }}{{ $selectedReference->description_2 ? ' · '.$selectedReference->description_2 : '' }}
                        @else
                            {{ __('Select or create a Reference to display report rates.') }}
                        @endif
                    </p>
                </div>
            </div>

            @if ($selectedReference)
                <div class="grid gap-px border-t border-gray-200 bg-gray-200 dark:border-gray-800 dark:bg-gray-800 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    @foreach ($periods as $period => $meta)
                        <div class="bg-gray-50 px-5 py-4 dark:bg-gray-900">
                            <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $meta['label'] }}</p>
                            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                                Rp {{ number_format((float) $selectedReference->{$meta['rate']}, 2, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Daftar Raw Material') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Materials shown: :count', ['count' => number_format($materials->total())]) }}
                    </p>
                </div>
                <a href="{{ route('admin.reporting.rm-price-budget.excel', request()->query()) }}" class="inline-flex h-10 items-center justify-center gap-2 self-start rounded-lg border border-success-500 px-4 text-sm font-medium text-success-700 shadow-theme-xs hover:bg-success-50 dark:border-success-500/50 dark:text-success-400 dark:hover:bg-success-500/10 sm:self-auto">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M5 19h14" /></svg>
                    {{ __('Unduh Excel') }}
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[2100px] border-collapse">
                    <thead class="bg-gray-50 text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th rowspan="2" class="w-32 border-e border-gray-200 px-5 py-3 text-start dark:border-gray-700">{{ __('Kode RM') }}</th>
                            <th rowspan="2" class="w-72 border-e border-gray-200 px-5 py-3 text-start dark:border-gray-700">{{ __('Bahan Baku') }}</th>
                            <th colspan="6" class="border-e border-gray-200 px-5 py-3 text-center text-brand-600 dark:border-gray-700 dark:text-brand-400">{{ __('USD Price') }}</th>
                            <th colspan="6" class="px-5 py-3 text-center text-success-600 dark:text-success-400">{{ __('Rupiah Price') }}</th>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            @foreach ($periods as $meta)
                                <th class="w-32 border-e border-gray-200 px-4 py-3 text-end dark:border-gray-700">{{ $meta['label'] }}</th>
                            @endforeach
                            @foreach ($periods as $meta)
                                <th class="w-36 border-e border-gray-200 px-4 py-3 text-end last:border-e-0 dark:border-gray-700">{{ $meta['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($materials as $material)
                            @php($prices = $material->prices->keyBy('period'))
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="border-e border-gray-100 px-5 py-4 align-top text-sm font-semibold text-gray-800 dark:border-gray-800 dark:text-white/90">{{ $material->code }}</td>
                                <td class="border-e border-gray-100 px-5 py-4 align-top dark:border-gray-800">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $material->description }}</p>
                                    <p class="mt-1 text-theme-xs text-gray-400">{{ $material->material_id }} · {{ $material->unit }} · {{ $material->currency_type }}</p>
                                </td>
                                @foreach (array_keys($periods) as $period)
                                    @php($amount = (float) ($prices->get($period)?->usd_amount ?? 0))
                                    <td class="border-e border-gray-100 px-4 py-4 text-end text-sm tabular-nums dark:border-gray-800 {{ $amount == 0.0 ? 'text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ number_format($amount, 2, ',', '.') }}
                                    </td>
                                @endforeach
                                @foreach (array_keys($periods) as $period)
                                    @php($amount = (float) ($prices->get($period)?->rupiah_amount ?? 0))
                                    <td class="border-e border-gray-100 px-4 py-4 text-end text-sm tabular-nums last:border-e-0 dark:border-gray-800 {{ $amount == 0.0 ? 'text-gray-400' : 'font-medium text-gray-800 dark:text-white/90' }}">
                                        {{ number_format($amount, 2, ',', '.') }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 5h16v14H4V5Zm0 5h16M9 5v14" /></svg>
                                    </div>
                                    <p class="mt-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ __('No Raw Material found') }}</p>
                                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Adjust the code range or search filter and try again.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($materials->hasPages())
                <div class="border-t border-gray-200 px-6 py-4 print:hidden dark:border-gray-800">
                    {{ $materials->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
