@props(['model' => 'form', 'rawMaterialOptions' => collect(), 'finishedGoodOptions' => collect()])

@php
    $inputClass = 'h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white';
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-5 rounded-xl border border-gray-200 p-5 dark:border-gray-800">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Raw Material') }}</h3>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pilih dari data master Raw Material.') }}</p>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('RM Code') }} <span class="text-error-500">*</span></label>
            <select x-model="{{ $model }}.rm_code" @change="{{ $model }}.rm_description = $event.target.selectedOptions[0]?.dataset.description || ''" name="rm_code" required class="{{ $inputClass }}">
                <option value="">{{ __('Pilih Raw Material') }}</option>
                @foreach ($rawMaterialOptions as $rawMaterial)
                    <option value="{{ $rawMaterial['code'] }}" data-description="{{ $rawMaterial['description'] }}">{{ $rawMaterial['code'] }} - {{ $rawMaterial['description'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('RM Description') }}</label>
            <input x-model="{{ $model }}.rm_description" name="rm_description" readonly placeholder="{{ __('Terisi otomatis') }}" class="{{ $inputClass }} bg-gray-50 text-gray-600 dark:bg-gray-800/50 dark:text-gray-300" />
        </div>
    </div>

    <div class="space-y-5 rounded-xl border border-gray-200 p-5 dark:border-gray-800">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Finished Good') }}</h3>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pilih dari data master Finished Good.') }}</p>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('FG Code') }} <span class="text-error-500">*</span></label>
            <select x-model="{{ $model }}.fg_code" @change="{{ $model }}.fg_description = $event.target.selectedOptions[0]?.dataset.description || ''" name="fg_code" required class="{{ $inputClass }}">
                <option value="">{{ __('Pilih Finished Good') }}</option>
                @foreach ($finishedGoodOptions as $finishedGood)
                    <option value="{{ $finishedGood['code'] }}" data-description="{{ $finishedGood['description'] }}">{{ $finishedGood['code'] }} - {{ $finishedGood['description'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('FG Description') }}</label>
            <input x-model="{{ $model }}.fg_description" name="fg_description" readonly placeholder="{{ __('Terisi otomatis') }}" class="{{ $inputClass }} bg-gray-50 text-gray-600 dark:bg-gray-800/50 dark:text-gray-300" />
        </div>
    </div>
</div>
