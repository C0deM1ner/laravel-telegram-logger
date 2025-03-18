<?php

namespace C0deM1ner\LaravelTelegramLogger\Providers;

use C0deM1ner\LaravelTelegramLogger\Console\Commands\SendTestMessageCommand;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class TelegramLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        logger()
            ->channel('telegram')
            ->debug('Query is taking too long');

        if (app()->isProduction()) {
            DB::listen(function ($query) {
                if ($query->time > 100) {
                    logger()
                        ->channel('telegram')
                        ->debug('Query is taking too long' . $query->sql, $query->bindings);
                }
            });

            app(Kernel::class)->whenRequestLifecycleIsLongerThan(
                CarbonInterval::second(4),
                function () {
                    logger()
                        ->channel('telegram')
                        ->debug('Request is taking too long' . request()->url());
                }
            );
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'telegram-logger');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/telegram-logger'),
            __DIR__ . '/../../config/telegram-logger.php' => config_path('telegram-logger.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                SendTestMessageCommand::class,
            ]);
        }
    }
}
