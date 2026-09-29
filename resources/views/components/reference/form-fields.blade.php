@props(['model' => 'form', 'readonly' => false])

@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 read-only:bg-gray-50 read-only:text-gray-700 dark:border-gray-700 dark:text-white dark:read-only:bg-gray-800/50 dark:read-only:text-gray-300';
    $rates = [
        'rate_current' => __('Rate Current'),
        'rate_le' => __('Rate LE'),
        'rate_1' => __('Rate 1'),
        'rate_2' => __('Rate 2'),
        'rate_3' => __('Rate 3'),
        'rate_4' => __('Rate 4'),
    ];
    $plants = ['ckp' => __('PE Ckp'), 'smg' => __('PE Smg'), 'sby' => __('PE Sby')];
    $periods = ['le' => __('PE LE'), '1' => __('PE 1'), '2' => __('PE 2'), '3' => __('PE 3'), '4' => __('PE 4')];
@endphp

<div class="grid gap-x-4 gap-y-4 md:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Code') }} <span class="text-error-500">*</span></label>
        <input x-model="{{ $model }}.code" name="code" required placeholder="{{ __('Masukkan code...') }}" class="{{ $inputClass }}" @readonly($readonly) />
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Periode') }} <span class="text-error-500">*</span></label>
        <input x-model="{{ $model }}.period" name="period" required placeholder="{{ __('Contoh: 2026-01') }}" class="{{ $inputClass }}" @readonly($readonly) />
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description 1') }} <span class="text-error-500">*</span></label>
        <input x-model="{{ $model }}.description_1" name="description_1" required class="{{ $inputClass }}" @readonly($readonly) />
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Periode Desc') }} <span class="text-error-500">*</span></label>
        <input x-model="{{ $model }}.period_description" name="period_description" required class="{{ $inputClass }}" @readonly($readonly) />
    </div>
    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description 2') }} <span class="text-error-500">*</span></label>
        <input x-model="{{ $model }}.description_2" name="description_2" required class="{{ $inputClass }}" @readonly($readonly) />
    </div>
</div>

<div class="my-5 border-t border-gray-200 dark:border-gray-800"></div>

<div>
    <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Rate') }}</h3>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($rates as $field => $label)
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400">{{ $label }}</label>
                @if ($readonly)
                    <input :value="formatPrice({{ $model }}.{{ $field }})" type="text" class="{{ $inputClass }}" readonly />
                @else
                    <input :value="formatPrice({{ $model }}.{{ $field }})" @input="updatePrice($event, {{ $model }}, '{{ $field }}')" name="{{ $field }}" type="text" inputmode="numeric" placeholder="0" class="{{ $inputClass }}" />
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="my-5 border-t border-gray-200 dark:border-gray-800"></div>

<div>
    <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('PE per plant') }}</h3>
    <div class="overflow-x-auto">
        <div class="min-w-180">
            <div class="grid grid-cols-[5rem_repeat(3,minmax(0,1fr))] gap-x-3 border-b border-gray-200 pb-2 dark:border-gray-800">
                <span></span>
                @foreach ($plants as $plant)
                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $plant }}</span>
                @endforeach
            </div>
            @foreach ($periods as $suffix => $label)
                <div class="grid grid-cols-[5rem_repeat(3,minmax(0,1fr))] items-center gap-x-3 border-b border-gray-100 py-2 last:border-0 dark:border-gray-800">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $label }}</span>
                    @foreach ($plants as $location => $plant)
                        <input x-model="{{ $model }}.pe_{{ $location }}_{{ $suffix }}" name="pe_{{ $location }}_{{ $suffix }}" type="number" min="0" step="0.01" placeholder="0.00" class="{{ $inputClass }}" aria-label="{{ $plant }} {{ $label }}" @readonly($readonly) />
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
