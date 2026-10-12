@props(['model' => 'form', 'rawMaterialOptions' => collect(), 'finishedGoodOptions' => collect()])

@php
    $inputClass = 'h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white';
    $rawMaterialSearchOptions = $rawMaterialOptions->map(fn ($rawMaterial) => [
        'value' => $rawMaterial->code,
        'label' => "{$rawMaterial->code} - {$rawMaterial->description}",
        'primary' => $rawMaterial->code,
        'secondary' => $rawMaterial->description,
        'description' => $rawMaterial->description,
        'search' => "{$rawMaterial->code} {$rawMaterial->description}",
    ])->values();
    $finishedGoodSearchOptions = $finishedGoodOptions->map(fn ($finishedGood) => [
        'value' => $finishedGood->id,
        'label' => "{$finishedGood->code} - {$finishedGood->description} (Plant {$finishedGood->plant?->code})",
        'primary' => "{$finishedGood->code} · Plant {$finishedGood->plant?->code}",
        'secondary' => $finishedGood->description,
        'code' => $finishedGood->code,
        'description' => $finishedGood->description,
        'search' => "{$finishedGood->code} {$finishedGood->description} {$finishedGood->plant?->code} {$finishedGood->plant?->description}",
    ])->values();
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-5 rounded-xl border border-gray-200 p-5 dark:border-gray-800">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Raw Material') }}</h3>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pilih dari data master Raw Material.') }}</p>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('RM Code') }} <span class="text-error-500">*</span></label>
            <x-form.searchable-select
                :model="$model.'.rm_code'"
                name="rm_code"
                :options="$rawMaterialSearchOptions"
                :placeholder="__('Pilih Raw Material')"
                :search-placeholder="__('Cari kode atau deskripsi Raw Material...')"
                :empty-text="__('Raw Material tidak ditemukan.')"
                :on-select="$model.'.rm_description = option.description;'"
            />
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
            <x-form.searchable-select
                :model="$model.'.fg_id'"
                name="fg_id"
                :options="$finishedGoodSearchOptions"
                :placeholder="__('Pilih Finished Good')"
                :search-placeholder="__('Cari kode atau deskripsi Finished Good...')"
                :empty-text="__('Finished Good tidak ditemukan.')"
                :on-select="$model.'.fg_code = option.code; '.$model.'.fg_description = option.description;'"
            />
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('FG Description') }}</label>
            <input x-model="{{ $model }}.fg_description" name="fg_description" readonly placeholder="{{ __('Terisi otomatis') }}" class="{{ $inputClass }} bg-gray-50 text-gray-600 dark:bg-gray-800/50 dark:text-gray-300" />
        </div>
    </div>
</div>
