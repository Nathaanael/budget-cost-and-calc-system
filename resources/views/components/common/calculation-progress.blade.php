@props([
    // Nama satuan yang dihitung, mis. "FG", "RM", "area".
    'unit' => 'data',
])

{{-- Memakai state Alpine milik halaman: calculating, progress { total, done, stage }, progressPercent. --}}
<div x-show="calculating" x-cloak role="progressbar" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="progressPercent" {{ $attributes->merge(['class' => 'border-b border-brand-200 bg-brand-50 px-6 py-4 dark:border-brand-500/30 dark:bg-brand-500/10']) }}>
    <div class="mb-2 flex items-center justify-between gap-4 text-sm">
        <span class="flex min-w-0 items-center gap-2 font-medium text-brand-700 dark:text-brand-300">
            <svg class="size-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" /><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z" /></svg>
            <span class="truncate" x-text="progress.stage"></span>
        </span>
        <span class="shrink-0 tabular-nums text-brand-700 dark:text-brand-300"><span class="font-semibold" x-text="`${progressPercent}%`"></span> · <span x-text="progress.done"></span>/<span x-text="progress.total"></span> {{ $unit }}</span>
    </div>
    <div class="h-2.5 w-full overflow-hidden rounded-full bg-brand-100 dark:bg-brand-500/20">
        {{-- Lebar dinamis tidak bisa memakai kelas Tailwind statis. --}}
        <div class="h-full rounded-full bg-brand-500 transition-all duration-300" :style="`width: ${progressPercent}%`"></div>
    </div>
    <p class="mt-2 text-theme-xs text-brand-700/80 dark:text-brand-300/80">{{ __('Jangan tutup atau pindah halaman sampai proses selesai.') }}</p>
</div>
