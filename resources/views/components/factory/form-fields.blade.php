@props(['model' => 'form', 'areaOptions' => collect(), 'readonly' => false, 'values' => []])

@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 read-only:bg-gray-50 read-only:text-gray-700 disabled:bg-gray-50 disabled:text-gray-700 dark:border-gray-700 dark:text-white dark:read-only:bg-gray-800/50 dark:read-only:text-gray-300 dark:disabled:bg-gray-800/50 dark:disabled:text-gray-300';
    $areaLabels = [
        1 => '#01st Area', 2 => '#02nd Area', 3 => '#03rd Area', 4 => '#04th Area', 5 => '#05th Area',
        6 => '#06th Area', 7 => '#07th Area', 8 => '#08th Area', 9 => '#09th Area', 10 => '#10th Area',
    ];
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Factory Code') }} <span class="text-error-500">*</span></label>
        <input x-model="{{ $model }}.code" name="code" value="{{ $values['code'] ?? '' }}" required maxlength="2" placeholder="{{ __('Contoh: S1') }}" class="{{ $inputClass }}" @readonly($readonly) />
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }} <span class="text-error-500">*</span></label>
        <input x-model="{{ $model }}.description" name="description" value="{{ $values['description'] ?? '' }}" required maxlength="20" placeholder="{{ __('Masukkan description...') }}" class="{{ $inputClass }}" @readonly($readonly) />
    </div>
</div>

<div class="my-5 border-t border-gray-200 dark:border-gray-800"></div>

<div>
    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Area Factory') }}</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Pilih kode area berdasarkan data master Area Noodle.') }}</p>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach ($areaLabels as $position => $label)
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400">{{ $label }}</label>
                <select x-model="{{ $model }}.area_{{ $position }}" name="area_{{ $position }}" class="{{ $inputClass }}" @disabled($readonly)>
                    <option value="">{{ __('Pilih area') }}</option>
                    @foreach ($areaOptions as $area)
                        <option value="{{ $area['code'] }}" @selected(($values["area_{$position}"] ?? '') === $area['code'])>{{ $area['code'] }} - {{ $area['description'] }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</div>
