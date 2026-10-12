@props(['page', 'total', 'perPage' => 5, 'label'])

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-theme-xs text-gray-500 dark:text-gray-400" aria-live="polite">
        {{ __('Showing') }} <span x-text="{{ $total }} === 0 ? 0 : ({{ $page }} - 1) * {{ $perPage }} + 1"></span>–<span x-text="Math.min({{ $page }} * {{ $perPage }}, {{ $total }})"></span>
        {{ __('of') }} <span x-text="{{ $total }}"></span> {{ __('records') }}
    </p>
    <nav aria-label="{{ $label }}" class="flex flex-wrap items-center gap-2">
        <button type="button" @click="{{ $page }} = Math.max(1, {{ $page }} - 1)" :disabled="{{ $page }} <= 1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Previous') }}</button>
        <span class="px-2 text-sm text-gray-600 dark:text-gray-300">{{ __('Page') }} <span x-text="{{ $page }}"></span> / <span x-text="Math.max(1, Math.ceil({{ $total }} / {{ $perPage }}))"></span></span>
        <button type="button" @click="{{ $page }} = Math.min(Math.max(1, Math.ceil({{ $total }} / {{ $perPage }})), {{ $page }} + 1)" :disabled="{{ $page }} >= Math.ceil({{ $total }} / {{ $perPage }})" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Next') }}</button>
    </nav>
</div>
