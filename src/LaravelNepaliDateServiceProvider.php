<?php

declare(strict_types=1);

namespace RakeshRai\LaravelNepaliDate;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use RakeshRai\LaravelNepaliDate\Http\Middleware\AutoConvertAdDatesToBs;

final class LaravelNepaliDateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nepali-date.php', 'nepali-date');

        $this->app->singleton(NepaliDateConverter::class, function ($app) {
            return new NepaliDateConverter((array) $app['config']->get('nepali-date', []));
        });

        $this->app->singleton('laravel-nepali-date', function ($app) {
            return new LaravelNepaliDate($app->make(NepaliDateConverter::class));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/nepali-date.php' => config_path('nepali-date.php'),
        ], 'nepali-date-config');

        Blade::directive('bs', function (string $expression): string {
            return "<?php echo e(bs_date({$expression})); ?>";
        });

        if ($this->app->bound('router')) {
            /** @var Router $router */
            $router = $this->app->make('router');
            $router->aliasMiddleware('auto.bs.date', AutoConvertAdDatesToBs::class);

            if ((bool) config('nepali-date.auto_register_web_middleware', true)) {
                $router->pushMiddlewareToGroup('web', AutoConvertAdDatesToBs::class);
            }
        }
    }
}
