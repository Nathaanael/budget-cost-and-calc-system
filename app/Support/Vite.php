<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use RuntimeException;

/**
 * Minimal port of Laravel 9's Vite helper for Laravel 8.
 *
 * Reads `public/hot` while `npm run dev` is running, otherwise resolves the
 * entrypoints through the `public/build/manifest.json` written by
 * laravel-vite-plugin.
 */
class Vite
{
    /** @var string */
    protected $buildDirectory = 'build';

    /** @var array|null */
    protected $manifest;

    /**
     * @param  string|string[]  $entrypoints
     */
    public function __invoke($entrypoints): HtmlString
    {
        $entrypoints = (array) $entrypoints;

        if (is_file($hotFile = public_path('hot'))) {
            $url = rtrim(file_get_contents($hotFile));

            return new HtmlString(collect(array_merge(['@vite/client'], $entrypoints))
                ->map(function (string $entrypoint) use ($url) {
                    return $this->makeTag("{$url}/{$entrypoint}");
                })
                ->implode(''));
        }

        $manifest = $this->manifest();
        $tags = [];

        foreach ($entrypoints as $entrypoint) {
            if (! isset($manifest[$entrypoint])) {
                throw new RuntimeException("Unable to locate file in Vite manifest: {$entrypoint}.");
            }

            $chunk = $manifest[$entrypoint];

            foreach ($this->importedCss($manifest, $chunk) as $css) {
                $tags[$css] = $this->makeTag($this->assetUrl($css));
            }

            $tags[$chunk['file']] = $this->makeTag($this->assetUrl($chunk['file']));
        }

        // Stylesheets first so they load before the module scripts.
        uasort($tags, function (string $a, string $b) {
            return (int) str_starts_with($a, '<script') <=> (int) str_starts_with($b, '<script');
        });

        return new HtmlString(implode('', $tags));
    }

    protected function importedCss(array $manifest, array $chunk, array &$seen = []): array
    {
        $css = $chunk['css'] ?? [];

        foreach ($chunk['imports'] ?? [] as $import) {
            if (isset($seen[$import]) || ! isset($manifest[$import])) {
                continue;
            }

            $seen[$import] = true;
            $css = array_merge($css, $this->importedCss($manifest, $manifest[$import], $seen));
        }

        return array_values(array_unique($css));
    }

    protected function manifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        $path = public_path($this->buildDirectory.'/manifest.json');

        if (! is_file($path)) {
            throw new RuntimeException("Vite manifest not found at: {$path}. Run `npm run build` or `npm run dev`.");
        }

        return $this->manifest = json_decode(file_get_contents($path), true);
    }

    protected function assetUrl(string $file): string
    {
        return asset($this->buildDirectory.'/'.$file);
    }

    protected function makeTag(string $url): string
    {
        if (preg_match('/\.(css|less|sass|scss|styl|stylus|pcss|postcss)(\?|$)/', $url) === 1) {
            return '<link rel="stylesheet" href="'.e($url).'" />';
        }

        return '<script type="module" src="'.e($url).'"></script>';
    }
}
