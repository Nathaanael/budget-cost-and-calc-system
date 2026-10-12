<?php

namespace App\Providers;

use App\Support\PlantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        $this->registerBladeDirectives();
        $this->registerRequestMacros();
        $this->registerValidationRules();
    }

    /**
     * Backport the "decimal:min,max" validation rule from Laravel 10.
     */
    private function registerValidationRules(): void
    {
        Validator::extend('decimal', function ($attribute, $value, $parameters) {
            if (! is_numeric($value)) {
                return false;
            }

            $matches = [];

            if (preg_match('/^[+-]?\d*\.?(\d*)$/', (string) $value, $matches) !== 1) {
                return false;
            }

            $decimals = strlen(end($matches));

            if (! isset($parameters[1])) {
                return $decimals == $parameters[0];
            }

            return $decimals >= $parameters[0] && $decimals <= $parameters[1];
        });

        Validator::replacer('decimal', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':decimal', implode('-', $parameters), $message);
        });
    }

    /**
     * Backport Request / Stringable helpers that only exist from Laravel 9 onwards.
     */
    private function registerRequestMacros(): void
    {
        Request::macro('string', function ($key, $default = null) {
            return Str::of($this->input($key, $default));
        });

        Request::macro('integer', function ($key, $default = 0) {
            return intval($this->input($key, $default));
        });

        Stringable::macro('toString', function () {
            return (string) $this;
        });
    }

    /**
     * Backport Blade directives that only exist from Laravel 9 onwards.
     */
    private function registerBladeDirectives(): void
    {
        // Laravel 9+ marks the CSRF field autocomplete="off" so browsers never restore a stale token.
        Blade::directive('csrf', function () {
            return '<?php echo \'<input type="hidden" name="_token" value="\'.csrf_token().\'" autocomplete="off">\'; ?>';
        });

        Blade::directive('vite', function ($expression) {
            return "<?php echo app(\\App\\Support\\Vite::class)({$expression}); ?>";
        });

        foreach (['checked', 'selected', 'disabled', 'readonly'] as $attribute) {
            Blade::directive($attribute, function ($condition) use ($attribute) {
                return "<?php if({$condition}): echo '{$attribute}'; endif; ?>";
            });
        }
    }
}
