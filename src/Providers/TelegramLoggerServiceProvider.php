<?php

namespace C0deM1ner\LaravelTelegramLogger\Providers;

use C0deM1ner\LaravelTelegramLogger\Console\Commands\SendTestMessageCommand;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Throwable;

class TelegramLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     * @throws Throwable
     */
    public function register(): void
    {
        $errorCodes = config('telegram-logger.log_errors');

        if (count($errorCodes) > 0 && !$this->app->runningInConsole()) {
            app(ExceptionHandler::class)->reportable(function (Throwable $e) use ($errorCodes) {
                if (method_exists($e, 'getStatusCode')) {
                    $code = $e->getStatusCode();

                } else {
                    $code = 500;
                }


                if (in_array($code, $errorCodes)) {
                    telegramLog()->error($e->getMessage());
                }
            });
        }
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
