@props([
    'model',
    'name',
    'options' => [],
    'placeholder' => __('Pilih data'),
    'searchPlaceholder' => __('Cari kode atau deskripsi...'),
    'emptyText' => __('Data tidak ditemukan.'),
    'onSelect' => '',
])

<div
    class="relative"
    x-data="{
        open: false,
        query: '',
        activeIndex: 0,
        options: @js($options),
        get selectedOption() {
            return this.options.find((option) => String(option.value) === String({{ $model }}));
        },
        get filteredOptions() {
            const keyword = this.query.trim().toLocaleLowerCase('id-ID');
            const matches = keyword === ''
                ? this.options
                : this.options.filter((option) => option.search.toLocaleLowerCase('id-ID').includes(keyword));

            return matches.slice(0, 100);
        },
        toggle() {
            this.open = !this.open;
            this.query = '';
            this.activeIndex = 0;

            if (this.open) this.$nextTick(() => this.$refs.searchInput.focus());
        },
        close() {
            this.open = false;
            this.query = '';
            this.activeIndex = 0;
        },
        move(step) {
            if (!this.open) {
                this.toggle();
                return;
            }

            const lastIndex = Math.max(this.filteredOptions.length - 1, 0);
            this.activeIndex = Math.min(Math.max(this.activeIndex + step, 0), lastIndex);
        },
        choose(option) {
            if (!option) return;
            {{ $model }} = option.value;
            {!! $onSelect !!}
            this.close();
        },
    }"
    @click.outside="close()"
    @keydown.escape.stop="close()"
>
    <input type="hidden" name="{{ $name }}" :value="{{ $model }}">

    <button
        type="button"
        @click="toggle()"
        @keydown.arrow-down.prevent="move(1)"
        @keydown.arrow-up.prevent="move(-1)"
        :aria-expanded="open"
        class="flex h-12 w-full items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-4 text-start text-sm shadow-theme-xs outline-hidden transition focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900"
        aria-haspopup="listbox"
    >
        <span
            class="min-w-0 truncate"
            :class="selectedOption ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'"
            x-text="selectedOption?.label || @js($placeholder)"
        ></span>
        <svg class="size-4 shrink-0 text-gray-400 transition" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.origin.top
        class="absolute inset-x-0 top-full z-999 mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-700 dark:bg-gray-900"
    >
        <div class="border-b border-gray-200 p-3 dark:border-gray-800">
            <div class="relative">
                <svg class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m20 20-4.35-4.35m1.35-5.4A6.75 6.75 0 1 1 3.5 10.25a6.75 6.75 0 0 1 13.5 0Z" /></svg>
                <input
                    x-ref="searchInput"
                    x-model="query"
                    @input="activeIndex = 0"
                    @keydown.arrow-down.prevent="move(1)"
                    @keydown.arrow-up.prevent="move(-1)"
                    @keydown.enter.prevent="choose(filteredOptions[activeIndex])"
                    type="search"
                    placeholder="{{ $searchPlaceholder }}"
                    autocomplete="off"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent ps-9 pe-3 text-sm text-gray-800 outline-hidden placeholder:text-gray-400 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white"
                >
            </div>
        </div>

        <div class="custom-scrollbar max-h-72 overflow-y-auto p-1.5" role="listbox">
            <template x-for="(option, index) in filteredOptions" :key="option.value">
                <button
                    type="button"
                    @click="choose(option)"
                    @mouseenter="activeIndex = index"
                    class="flex w-full items-start justify-between gap-3 rounded-lg px-3 py-2.5 text-start text-sm transition"
                    :class="index === activeIndex ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'"
                    :aria-selected="String(option.value) === String({{ $model }})"
                    role="option"
                >
                    <span class="min-w-0">
                        <span class="block font-medium" x-text="option.primary"></span>
                        <span class="mt-0.5 block truncate text-theme-xs text-gray-500 dark:text-gray-400" x-text="option.secondary"></span>
                    </span>
                    <svg x-show="String(option.value) === String({{ $model }})" class="mt-0.5 size-4 shrink-0 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12.5 4.25 4.25L19 7" /></svg>
                </button>
            </template>

            <p x-show="filteredOptions.length === 0" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ $emptyText }}</p>
            <p x-show="query === '' && options.length > 100" class="px-3 py-2 text-center text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Menampilkan 100 data pertama. Gunakan pencarian untuk hasil lainnya.') }}</p>
        </div>
    </div>
</div>
