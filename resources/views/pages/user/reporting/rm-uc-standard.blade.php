@extends('layouts.app')

@section('content')
    @php
        $periods = [
            'current' => ['label' => __('Current'), 'price' => 'D', 'cost' => 'E'],
            'le' => ['label' => __('LE'), 'price' => 'F', 'cost' => 'G'],
            'qtr_1' => ['label' => __('Kuartal 1'), 'price' => 'H', 'cost' => 'I'],
            'qtr_2' => ['label' => __('Kuartal 2'), 'price' => 'J', 'cost' => 'K'],
            'qtr_3' => ['label' => __('Kuartal 3'), 'price' => 'L', 'cost' => 'M'],
            'qtr_4' => ['label' => __('Kuartal 4'), 'price' => 'N', 'cost' => 'O'],
        ];
        $qty = fn ($value) => number_format((float) $value, 6, ',', '.');
        $money = fn ($value) => number_format((float) $value, 2, ',', '.');
        $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
    @endphp

    <div class="space-y-6">
        <x-common.component-card
            :title="__('Report Filter')"
            :desc="__('Select a reference and Finished Good code range to prepare the RM unit cost standard report.')">
            <form method="GET" action="{{ route('admin.reporting.rm-uc-standard.index') }}" class="space-y-5">
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
                        <a href="{{ route('admin.reporting.rm-uc-standard.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
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

        <section
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            x-data="{
                query: @js((object) request()->except('page')),
                calculating: false,
                error: '',
                progress: { total: 0, done: 0, stage: '' },
                get progressPercent() {
                    return this.progress.total > 0 ? Math.floor((this.progress.done / this.progress.total) * 100) : 0;
                },
                url(base, extra = {}) {
                    return `${base}?${new URLSearchParams({ ...this.query, ...extra })}`;
                },
                async request(url, options = {}) {
                    const response = await fetch(url, { ...options, headers: { 'Accept': 'application/json', ...(options.headers ?? {}) } });
                    if (!response.ok) {
                        const payload = await response.json().catch(() => ({}));
                        const validation = Object.values(payload.errors ?? {}).flat()[0];
                        throw new Error(validation || payload.message || '{{ __('File Excel gagal dibuat.') }}');
                    }
                    return response;
                },
                // Unduh bertahap: FG dihitung per batch di server, progress bar naik per batch, lalu file diunduh tanpa memuat ulang halaman.
                async download() {
                    if (this.calculating) return;

                    this.calculating = true;
                    this.error = '';
                    this.progress = { total: 0, done: 0, stage: '{{ __('Menyiapkan data...') }}' };

                    try {
                        const plan = await (await this.request(this.url('{{ route('admin.reporting.rm-uc-standard.excel.plan') }}'))).json();
                        const ids = plan.finished_good_ids ?? [];
                        let batches = 0;
                        this.progress.total = ids.length;
                        this.progress.stage = '{{ __('Menghitung FG...') }}';

                        for (let index = 0; index < ids.length; index += plan.batch_size) {
                            const chunk = ids.slice(index, index + plan.batch_size);
                            await this.request('{{ route('admin.reporting.rm-uc-standard.excel.batch') }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ token: plan.token, index: batches, finished_good_ids: chunk }),
                            });
                            batches++;
                            this.progress.done += chunk.length;
                        }

                        this.progress.stage = '{{ __('Menyusun file Excel...') }}';
                        const response = await this.request(this.url('{{ route('admin.reporting.rm-uc-standard.excel') }}', { token: plan.token, batches }));
                        const filename = (response.headers.get('Content-Disposition') ?? '').match(/filename=\W*([\w.-]+)/)?.[1] ?? 'unit-cost-standar-rm.xlsx';
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(await response.blob());
                        link.download = filename;
                        link.click();
                        setTimeout(() => URL.revokeObjectURL(link.href), 1000);
                    } catch (error) {
                        this.error = error.message || '{{ __('File Excel gagal dibuat.') }}';
                    } finally {
                        this.calculating = false;
                    }
                },
            }"
            @beforeunload.window="if (calculating) { $event.preventDefault(); $event.returnValue = ''; }">
            <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Raw Material Unit Cost Standard') }}</h1>
                        @if ($selectedReference)
                            <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ $selectedReference->period_description ?: $selectedReference->period }}
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
                    </p>
                </div>
                <button type="button" @click="download()" :disabled="calculating" class="inline-flex h-10 items-center justify-center gap-2 self-start rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M5 19h14" /></svg>
                    <span x-text="calculating ? '{{ __('Menyiapkan Excel...') }}' : '{{ __('Unduh Excel') }}'">{{ __('Unduh Excel') }}</span>
                </button>
            </div>

            <x-common.calculation-progress unit="FG" class="border-t" />

            <div x-show="error" x-cloak class="border-y border-error-200 bg-error-50 px-6 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300" x-text="error"></div>
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 text-theme-xs text-gray-500 dark:border-gray-800 dark:bg-gray-800/40 dark:text-gray-400">
                {{ __('C = A + (A × B). Unit Cost = C × RM Price for Packaging (has ID), or C × RM Price / 1000 for RM (no ID).') }}
            </div>
        </section>

        @forelse ($finishedGoods as $finishedGood)
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-wrap items-center gap-3 border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <span class="inline-flex rounded-md bg-gray-100 px-2.5 py-1 text-sm font-semibold text-gray-800 dark:bg-gray-800 dark:text-white/90">{{ $finishedGood['code'] }}</span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $finishedGood['description'] }}</h2>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __(':count materials', ['count' => count($finishedGood['rows'])]) }}</span>
                </div>

                <div class="custom-scrollbar overflow-x-auto">
                    <table class="min-w-[2200px] border-collapse">
                        <thead class="bg-gray-50 text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <th rowspan="2" class="w-12 border-e border-gray-200 px-4 py-3 text-center dark:border-gray-700">{{ __('No') }}</th>
                                <th rowspan="2" class="w-80 border-e border-gray-200 px-5 py-3 text-start dark:border-gray-700">{{ __('Materials') }}</th>
                                <th class="w-36 border-e border-gray-200 px-4 py-3 text-end dark:border-gray-700">{{ __('Standard Qty Usage (Zero Waste)') }}</th>
                                <th class="w-32 border-e border-gray-200 px-4 py-3 text-end dark:border-gray-700">{{ __('Budget Wastage Allowance') }}</th>
                                <th class="w-36 border-e border-gray-200 px-4 py-3 text-end dark:border-gray-700">{{ __('Budget Qty Usage') }}</th>
                                @foreach ($periods as $meta)
                                    <th colspan="2" class="border-e border-gray-200 px-4 py-3 text-center text-brand-600 last:border-e-0 dark:border-gray-700 dark:text-brand-400">{{ $meta['label'] }}</th>
                                @endforeach
                            </tr>
                            <tr class="border-b border-gray-200 normal-case dark:border-gray-800">
                                <th class="border-e border-gray-200 px-4 py-2 text-end dark:border-gray-700">(A)</th>
                                <th class="border-e border-gray-200 px-4 py-2 text-end dark:border-gray-700">(B)</th>
                                <th class="border-e border-gray-200 px-4 py-2 text-end dark:border-gray-700">C = A + (A × B)</th>
                                @foreach ($periods as $meta)
                                    <th class="w-32 border-e border-gray-200 px-4 py-2 text-end dark:border-gray-700">{{ __('RM Price') }} ({{ $meta['price'] }})</th>
                                    <th class="w-32 border-e border-gray-200 px-4 py-2 text-end last:border-e-0 dark:border-gray-700">{{ __('Unit Cost') }} ({{ $meta['cost'] }} = C × {{ $meta['price'] }})</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($finishedGood['rows'] as $row)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                    <td class="border-e border-gray-100 px-4 py-3 text-center text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ $loop->iteration }}</td>
                                    <td class="border-e border-gray-100 px-5 py-3 dark:border-gray-800">
                                        @if ($row['missing'])
                                            <span class="inline-flex rounded-full bg-error-50 px-2.5 py-0.5 text-theme-xs font-medium text-error-700 dark:bg-error-500/15 dark:text-error-400">{{ __('Raw Material not found') }}</span>
                                        @else
                                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                                <span class="font-semibold">{{ $row['code'] }}</span> · {{ $row['description'] }}
                                            </p>
                                            <p class="mt-0.5 text-theme-xs text-gray-400">
                                                {{ $row['is_packaging'] ? __('Packaging').' · '.$row['material_id'] : __('RM') }} · {{ $row['unit'] }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="border-e border-gray-100 px-4 py-3 text-end text-sm tabular-nums text-gray-700 dark:border-gray-800 dark:text-gray-300">{{ $qty($row['standard']) }}</td>
                                    <td class="border-e border-gray-100 px-4 py-3 text-end text-sm tabular-nums text-gray-700 dark:border-gray-800 dark:text-gray-300">{{ $qty($row['waste']) }}</td>
                                    <td class="border-e border-gray-100 px-4 py-3 text-end text-sm font-medium tabular-nums text-gray-800 dark:border-gray-800 dark:text-white/90">{{ $qty($row['standard_with_waste']) }}</td>
                                    @foreach (array_keys($periods) as $period)
                                        @php($value = $row['periods'][$period])
                                        <td class="border-e border-gray-100 px-4 py-3 text-end text-sm tabular-nums dark:border-gray-800 {{ $value['price'] == 0.0 ? 'text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $money($value['price']) }}</td>
                                        <td class="border-e border-gray-100 px-4 py-3 text-end text-sm tabular-nums last:border-e-0 dark:border-gray-800 {{ $value['cost'] == 0.0 ? 'text-gray-400' : 'font-medium text-gray-800 dark:text-white/90' }}">{{ $money($value['cost']) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-200 bg-brand-50/60 dark:border-gray-700 dark:bg-brand-500/10">
                            <tr>
                                <td colspan="5" class="border-e border-gray-200 px-5 py-3 text-sm font-semibold text-gray-800 dark:border-gray-700 dark:text-white/90">{{ __('Unit Cost Per Pack') }}</td>
                                @foreach (array_keys($periods) as $period)
                                    <td class="border-e border-gray-200 dark:border-gray-700"></td>
                                    <td class="border-e border-gray-200 px-4 py-3 text-end text-sm font-semibold tabular-nums text-brand-700 last:border-e-0 dark:border-gray-700 dark:text-brand-400">{{ $money($finishedGood['totals'][$period]) }}</td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @empty
            <section class="rounded-2xl border border-gray-200 bg-white px-6 py-16 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 5h16v14H4V5Zm0 5h16M9 5v14" /></svg>
                </div>
                <p class="mt-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ __('No Finished Good with formula found') }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Adjust the code range or search filter and try again.') }}</p>
            </section>
        @endforelse

        @if ($finishedGoods->hasPages())
            <div class="rounded-2xl border border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900">
                {{ $finishedGoods->links() }}
            </div>
        @endif
    </div>
@endsection
