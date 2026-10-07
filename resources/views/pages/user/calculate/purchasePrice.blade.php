@extends('layouts.app')

@section('content')
    @php
        $periods = [
            'current' => __('Current'),
            'le' => __('LE'),
            'qtr_1' => __('Quarter 1'),
            'qtr_2' => __('Quarter 2'),
            'qtr_3' => __('Quarter 3'),
            'qtr_4' => __('Quarter 4'),
        ];
        $rateFields = [
            'current' => 'rate_current',
            'le' => 'rate_le',
            'qtr_1' => 'rate_1',
            'qtr_2' => 'rate_2',
            'qtr_3' => 'rate_3',
            'qtr_4' => 'rate_4',
        ];
    @endphp

    <div
        x-data="{
            references: @js($references),
            materials: @js($rawMaterials),
            selectedReferenceId: '',
            selectedPeriod: 'current',
            search: '',
            calculating: false,
            error: '',
            success: '',
            summary: null,
            rateFields: @js($rateFields),
            get selectedReference() {
                return this.references.find((reference) => String(reference.id) === String(this.selectedReferenceId)) ?? null;
            },
            get selectedRate() {
                if (!this.selectedReference) return 0;
                return Number(this.selectedReference[this.rateFields[this.selectedPeriod]] || 0);
            },
            get rows() {
                const keyword = this.search.trim().toLowerCase();
                return this.materials
                    .filter((material) => keyword === '' || material.code.toLowerCase().includes(keyword) || material.description.toLowerCase().includes(keyword) || (material.material_id ?? '').toLowerCase().includes(keyword))
                    .map((material) => {
                        const price = material.prices[this.selectedPeriod] ?? { usd: 0, rupiah: 0 };
                        const usd = Number(price.usd || 0);
                        const oldRupiah = Number(price.rupiah || 0);
                        const newRupiah = usd * this.selectedRate;
                        return { ...material, usd, oldRupiah, newRupiah, difference: newRupiah - oldRupiah, ready: usd > 0 && this.selectedRate > 0 };
                    });
            },
            get readyCount() { return this.rows.filter((row) => row.ready).length; },
            get skippedCount() { return this.rows.filter((row) => !row.ready).length; },
            get totalReadyCount() {
                if (!this.selectedReference) return 0;
                return this.materials.reduce((total, material) => total + Object.keys(this.rateFields).filter((period) => Number(material.prices[period]?.usd || 0) > 0 && Number(this.selectedReference[this.rateFields[period]] || 0) > 0).length, 0);
            },
            formatAmount(value) {
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value) || 0);
            },
            formatRate(value) {
                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value) || 0);
            },
            resetMessage() {
                this.error = '';
                this.success = '';
                this.summary = null;
            },
            async calculateAndSave() {
                if (!this.selectedReference || this.totalReadyCount === 0) return;
                if (!window.confirm('{{ __('Hitung dan simpan harga Rupiah untuk seluruh periode RM USD?') }}')) return;

                this.calculating = true;
                this.resetMessage();
                try {
                    const response = await fetch('{{ route('admin.calculate.purchase-price.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ reference_id: this.selectedReference.id }),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(Object.values(payload.errors ?? {}).flat()[0] ?? payload.message);
                    this.materials = payload.raw_materials;
                    this.summary = payload.summary;
                    this.success = payload.message;
                } catch (error) {
                    this.error = error.message || '{{ __('Purchase Price gagal dihitung.') }}';
                } finally {
                    this.calculating = false;
                }
            }
        }">
        <div x-show="success" x-cloak class="mb-5 rounded-xl border border-success-200 bg-success-50 px-5 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">
            <p x-text="success"></p>
            <p x-show="summary" class="mt-1 text-theme-xs" x-text="summary ? `{{ __('RM diperbarui') }}: ${summary.updated_materials}, {{ __('periode diperbarui') }}: ${summary.updated_prices}, {{ __('periode dilewati') }}: ${summary.skipped_prices}` : ''"></p>
        </div>
        <div x-show="error" x-cloak x-text="error" class="mb-5 rounded-xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400"></div>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Calculate Purchase Price') }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Preview konversi harga RM berjenis USD menjadi Rupiah berdasarkan rate Reference.') }}</p>
                </div>
                <button type="button" @click="calculateAndSave()" :disabled="!selectedReference || totalReadyCount === 0 || calculating" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg x-show="!calculating" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 12h16M12 4v16" /></svg>
                    <svg x-show="calculating" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/></svg>
                    <span x-text="calculating ? '{{ __('Menghitung...') }}' : '{{ __('Calculate & Save') }}'"></span>
                </button>
            </div>

            <div class="grid gap-5 p-6 xl:grid-cols-2">
                <div>
                    <label for="purchase-reference" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Reference') }} <span class="text-error-500">*</span></label>
                    <select id="purchase-reference" x-model="selectedReferenceId" @change="resetMessage()" :disabled="calculating" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">{{ __('Pilih Reference') }}</option>
                        @foreach ($references as $reference)
                            <option value="{{ $reference->id }}">{{ $reference->code }} - {{ $reference->period_description }} - {{ $reference->description_1 }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="purchase-period" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Periode Harga') }}</label>
                    <select id="purchase-period" x-model="selectedPeriod" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @foreach ($periods as $period => $label)
                            <option value="{{ $period }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div x-show="!selectedReference" class="mx-6 mb-6 rounded-xl border border-warning-200 bg-warning-50 px-5 py-4 text-sm text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400">
                {{ __('Pilih Reference untuk menampilkan rate dan hasil kalkulasi.') }}
            </div>

            <div x-show="selectedReference" x-cloak>
                <div class="border-y border-gray-200 bg-gray-50 px-6 py-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="`${selectedReference?.code} - ${selectedReference?.description_1}`"></p>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400" x-text="`${selectedReference?.period_description} · ${selectedReference?.description_2}`"></p>
                        </div>
                        <span class="mt-2 inline-flex w-fit rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400 sm:mt-0" x-text="selectedReference?.period"></span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                        @foreach ($periods as $period => $label)
                            <div class="rounded-lg border px-4 py-3 transition" :class="selectedPeriod === '{{ $period }}' ? 'border-brand-300 bg-brand-50 dark:border-brand-500/40 dark:bg-brand-500/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900'">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90" x-text="formatRate(selectedReference?.{{ $rateFields[$period] }})"></p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 border-b border-gray-200 p-6 dark:border-gray-800 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('RM Currency USD') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="materials.length"></p>
                    </div>
                    <div class="rounded-xl border border-success-200 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/10">
                        <p class="text-theme-xs font-medium uppercase tracking-wide text-success-600 dark:text-success-400">{{ __('Siap Dihitung') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-success-700 dark:text-success-300" x-text="readyCount"></p>
                    </div>
                    <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-500/30 dark:bg-warning-500/10">
                        <p class="text-theme-xs font-medium uppercase tracking-wide text-warning-600 dark:text-warning-400">{{ __('Dilewati') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-warning-700 dark:text-warning-300" x-text="skippedCount"></p>
                    </div>
                    <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-500/30 dark:bg-brand-500/10">
                        <p class="text-theme-xs font-medium uppercase tracking-wide text-brand-600 dark:text-brand-400">{{ __('Rate Terpilih') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-brand-700 dark:text-brand-300" x-text="formatRate(selectedRate)"></p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Preview Purchase Price') }}</h2>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Rupiah Baru = Harga USD × Rate periode terpilih.') }}</p>
                    </div>
                    <div class="relative w-full sm:w-72">
                        <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg>
                        <input x-model="search" type="search" placeholder="{{ __('Cari Code RM, description, atau ID...') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-white ps-11 pe-4 text-sm text-gray-800 outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                <div class="max-h-[520px] overflow-auto">
                    <table class="min-w-[1100px] divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="sticky top-0 z-10 bg-gray-50 text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3 text-start">{{ __('Code RM') }}</th>
                                <th class="px-5 py-3 text-start">{{ __('Description') }}</th>
                                <th class="px-5 py-3 text-end">{{ __('Harga USD') }}</th>
                                <th class="px-5 py-3 text-end">{{ __('Rate') }}</th>
                                <th class="px-5 py-3 text-end">{{ __('Rupiah Lama') }}</th>
                                <th class="px-5 py-3 text-end">{{ __('Rupiah Baru') }}</th>
                                <th class="px-5 py-3 text-end">{{ __('Selisih') }}</th>
                                <th class="px-5 py-3 text-center">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="row in rows" :key="row.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90" x-text="row.code"></td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300"><span x-text="row.description"></span><span class="mt-0.5 block text-theme-xs text-gray-400" x-text="row.material_id"></span></td>
                                    <td class="px-5 py-4 text-end text-sm text-gray-600 dark:text-gray-300" x-text="formatAmount(row.usd)"></td>
                                    <td class="px-5 py-4 text-end text-sm text-gray-600 dark:text-gray-300" x-text="formatRate(selectedRate)"></td>
                                    <td class="px-5 py-4 text-end text-sm text-gray-600 dark:text-gray-300" x-text="formatAmount(row.oldRupiah)"></td>
                                    <td class="px-5 py-4 text-end text-sm font-semibold text-brand-600 dark:text-brand-400" x-text="formatAmount(row.newRupiah)"></td>
                                    <td class="px-5 py-4 text-end text-sm" :class="row.difference >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400'" x-text="`${row.difference >= 0 ? '+' : ''}${formatAmount(row.difference)}`"></td>
                                    <td class="px-5 py-4 text-center"><span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium" :class="row.ready ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400'" x-text="row.ready ? '{{ __('Siap') }}' : '{{ __('Dilewati') }}'"></span></td>
                                </tr>
                            </template>
                            <template x-if="rows.length === 0">
                                <tr><td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Tidak ada Raw Material USD yang cocok.') }}</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                    {{ __('Calculate & Save memproses seluruh periode Current, LE, dan Quarter 1–4 sekaligus. Harga atau rate bernilai nol akan dilewati.') }}
                </div>
            </div>
        </section>
    </div>
@endsection
