<div
    class="relative"
    x-data="{ isOpen: false }"
    @click.outside="isOpen = false"
    @keydown.escape.window="isOpen = false">
    <button
        type="button"
        class="flex items-center gap-3 rounded-lg p-1.5 transition hover:bg-gray-100 dark:hover:bg-white/5"
        @click="isOpen = !isOpen"
        :aria-expanded="isOpen"
        aria-haspopup="menu"
        aria-label="{{ __('Buka menu profil') }}">
        <span class="size-11 animate-pulse rounded-full bg-gray-200 dark:bg-gray-700"></span>

        <span class="hidden text-theme-sm font-medium text-gray-700 sm:block dark:text-gray-300">
            {{ auth()->user()->name }}
        </span>

        <svg
            class="size-[18px] stroke-gray-500 transition-transform duration-200 dark:stroke-gray-400"
            :class="isOpen ? 'rotate-180' : ''"
            viewBox="0 0 18 18"
            fill="none"
            aria-hidden="true">
            <path d="M4.5 6.75 9 11.25l4.5-4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <div
        x-show="isOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="absolute z-50 mt-3 w-48 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg ltr:right-0 rtl:left-0 dark:border-gray-800 dark:bg-gray-dark"
        role="menu">
        <div class="border-b border-gray-200 px-3 py-2.5 dark:border-gray-800">
            <span class="block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Role') }}</span>
            <span class="mt-1 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                {{ \Illuminate\Support\Str::headline(auth()->user()->role) }}
            </span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                data-loading-label="{{ __('Keluar...') }}"
                class="group mt-2 flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                role="menuitem">
                <svg class="size-5 text-gray-500 transition group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m17 16 4-4m0 0-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1" />
                </svg>
                {{ __('Keluar') }}
            </button>
        </form>
    </div>
</div>
