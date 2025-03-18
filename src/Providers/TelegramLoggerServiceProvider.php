<?php

namespace C0deM1ner\LaravelTelegramLogger\Providers;

use C0deM1ner\LaravelTelegramLogger\Console\Commands\SendTestMessageCommand;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/telegram-logger.php', 'telegram-logger'
        );

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'telegram-logger');

        $this->publishes([
            __DIR__ . '/../../config/telegram-logger.php' => config_path('telegram-logger.php'),
        ], 'telegram-logger-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/telegram-logger'),
        ], 'telegram-logger-views');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SendTestMessageCommand::class,
            ]);
        }
    }
}
