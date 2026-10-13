@props([
    // Nama route excel; route "<route>.plan" dan "<route>.batch" disediakan oleh trait BatchesExcelExport.
    'route',
    'stage' => __('Menghitung FG...'),
    'filename' => 'report.xlsx',
])

{{--
    Header report dengan tombol Unduh Excel bertahap: plan → batch (progress bar naik per batch) → file Excel.
    Isi kiri header diberikan lewat slot. File diunduh lewat fetch sehingga halaman tidak dimuat ulang.
--}}
<div
    x-data="{
        query: @js((object) request()->except('page')),
        calculating: false,
        error: '',
        progress: { total: 0, done: 0, stage: '' },
        get progressPercent() {
            return this.progress.total > 0 ? Math.floor((this.progress.done / this.progress.total) * 100) : 0;
        },
        url(base, extra = {}) {
            return `${base}?${new URLSearchParams({ ...this.query, ...extra })}`;
        },
        async request(url, options = {}) {
            const response = await fetch(url, { ...options, headers: { 'Accept': 'application/json', ...(options.headers ?? {}) } });
            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));
                const validation = Object.values(payload.errors ?? {}).flat()[0];
                throw new Error(validation || payload.message || '{{ __('File Excel gagal dibuat.') }}');
            }
            return response;
        },
        async download() {
            if (this.calculating) return;

            this.calculating = true;
            this.error = '';
            this.progress = { total: 0, done: 0, stage: '{{ __('Menyiapkan data...') }}' };

            try {
                const plan = await (await this.request(this.url(@js(route($route.'.plan'))))).json();
                const ids = plan.finished_good_ids ?? [];
                let batches = 0;
                this.progress.total = ids.length;
                this.progress.stage = @js($stage);

                for (let index = 0; index < ids.length; index += plan.batch_size) {
                    const chunk = ids.slice(index, index + plan.batch_size);
                    await this.request(@js(route($route.'.batch')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ token: plan.token, index: batches, finished_good_ids: chunk }),
                    });
                    batches++;
                    this.progress.done += chunk.length;
                }

                this.progress.stage = '{{ __('Menyusun file Excel...') }}';
                const response = await this.request(this.url(@js(route($route)), { token: plan.token, batches }));
                const link = document.createElement('a');
                link.href = URL.createObjectURL(await response.blob());
                link.download = (response.headers.get('Content-Disposition') ?? '').match(/filename=\W*([\w.-]+)/)?.[1] ?? @js($filename);
                link.click();
                setTimeout(() => URL.revokeObjectURL(link.href), 1000);
            } catch (error) {
                this.error = error.message || '{{ __('File Excel gagal dibuat.') }}';
            } finally {
                this.calculating = false;
            }
        },
    }"
    @beforeunload.window="if (calculating) { $event.preventDefault(); $event.returnValue = ''; }"
    {{ $attributes }}>
    <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
        <div>{{ $slot }}</div>
        <button type="button" @click="download()" :disabled="calculating" class="inline-flex h-10 items-center justify-center gap-2 self-start rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M5 19h14" /></svg>
            <span x-text="calculating ? '{{ __('Menyiapkan Excel...') }}' : '{{ __('Unduh Excel') }}'">{{ __('Unduh Excel') }}</span>
        </button>
    </div>

    <x-common.calculation-progress unit="FG" class="border-t" />

    <div x-show="error" x-cloak class="border-y border-error-200 bg-error-50 px-6 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300" x-text="error"></div>
</div>
