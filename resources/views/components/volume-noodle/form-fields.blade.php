@props(['model' => 'form', 'areaOptions' => collect(), 'noodleOptions' => collect()])

@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white';
    $leFields = [
        'le_june' => 'LE Juni', 'le_august' => 'LE Agustus', 'le_september' => 'LE September',
        'le_october' => 'LE Oktober', 'le_november' => 'LE November', 'le_december' => 'LE Desember',
    ];
    $monthFields = [
        'january' => 'Januari', 'february' => 'Februari', 'march' => 'Maret', 'april' => 'April',
        'may' => 'Mei', 'june' => 'Juni', 'july' => 'Juli', 'august' => 'Agustus',
        'september' => 'September', 'october' => 'Oktober', 'november' => 'November', 'december' => 'Desember',
    ];
@endphp

<section class="rounded-xl border border-gray-200 p-5 dark:border-gray-800">
    <div class="mb-5"><h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Data Produk') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Pilih area dan noodle dari data master.') }}</p></div>
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Area Code') }} <span class="text-error-500">*</span></label>
            <select x-model="{{ $model }}.area_code" @change="{{ $model }}.area_description = $event.target.selectedOptions[0]?.dataset.description || ''" required class="{{ $inputClass }}">
                <option value="">{{ __('Pilih Area') }}</option>
                @foreach ($areaOptions as $area)
                    <option value="{{ $area['code'] }}" data-description="{{ $area['description'] }}">{{ $area['code'] }} - {{ $area['description'] }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400" x-show="{{ $model }}.area_description"><span class="font-medium">{{ __('Area') }}:</span> <span x-text="{{ $model }}.area_description"></span></p>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Noodle Code') }} <span class="text-error-500">*</span></label>
            <select x-model="{{ $model }}.noodle_code" @change="{{ $model }}.noodle_description = $event.target.selectedOptions[0]?.dataset.description || ''" required class="{{ $inputClass }}">
                <option value="">{{ __('Pilih Noodle') }}</option>
                @foreach ($noodleOptions as $noodle)
                    <option value="{{ $noodle['code'] }}" data-description="{{ $noodle['description'] }}">{{ $noodle['code'] }} - {{ $noodle['description'] }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400" x-show="{{ $model }}.noodle_description"><span class="font-medium">{{ __('Noodle') }}:</span> <span x-text="{{ $model }}.noodle_description"></span></p>
        </div>
    </div>
</section>

<section class="mt-5 rounded-xl border border-gray-200 p-5 dark:border-gray-800">
    <div class="mb-5"><h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('LE') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Masukkan estimasi volume untuk periode LE yang tersedia.') }}</p></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($leFields as $field => $label)
            <div><label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400">{{ $label }}</label><input x-model.number="{{ $model }}.{{ $field }}" type="number" min="0" step="1" placeholder="0" class="{{ $inputClass }}" /></div>
        @endforeach
    </div>
    <div class="mt-5 flex justify-end border-t border-gray-200 pt-4 dark:border-gray-800">
        <div class="min-w-52 rounded-lg bg-brand-50 px-4 py-3 text-end dark:bg-brand-500/10">
            <p class="text-theme-xs font-medium uppercase tracking-wide text-brand-600 dark:text-brand-400">{{ __('Total LE') }}</p>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90" x-text="new Intl.NumberFormat('id-ID').format(Number({{ $model }}.le_june || 0) + Number({{ $model }}.le_august || 0) + Number({{ $model }}.le_september || 0) + Number({{ $model }}.le_october || 0) + Number({{ $model }}.le_november || 0) + Number({{ $model }}.le_december || 0))"></p>
        </div>
    </div>
</section>

<section class="mt-5 rounded-xl border border-gray-200 p-5 dark:border-gray-800">
    <div class="mb-5"><h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('AOP') }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Masukkan volume Januari sampai Desember.') }}</p></div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($monthFields as $field => $label)
            <div><label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400">{{ $label }}</label><input x-model.number="{{ $model }}.{{ $field }}" type="number" min="0" step="1" placeholder="0" class="{{ $inputClass }}" /></div>
        @endforeach
    </div>
    <div class="mt-5 flex justify-end border-t border-gray-200 pt-4 dark:border-gray-800">
        <div class="min-w-52 rounded-lg bg-gray-100 px-4 py-3 text-end dark:bg-gray-800">
            <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Total AOP') }}</p>
            <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90" x-text="new Intl.NumberFormat('id-ID').format(Number({{ $model }}.january || 0) + Number({{ $model }}.february || 0) + Number({{ $model }}.march || 0) + Number({{ $model }}.april || 0) + Number({{ $model }}.may || 0) + Number({{ $model }}.june || 0) + Number({{ $model }}.july || 0) + Number({{ $model }}.august || 0) + Number({{ $model }}.september || 0) + Number({{ $model }}.october || 0) + Number({{ $model }}.november || 0) + Number({{ $model }}.december || 0))"></p>
        </div>
    </div>
</section>
