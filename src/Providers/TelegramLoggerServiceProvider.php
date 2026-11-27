<?php

namespace C0deM1ner\LaravelTelegramLogger\Providers;

use C0deM1ner\LaravelTelegramLogger\Console\Commands\GetUpdatesCommand;
use C0deM1ner\LaravelTelegramLogger\Console\Commands\SendTestMessageCommand;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Throwable;

class TelegramLoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/telegram-logger.php', 'telegram-logger');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'telegram-logger');

        $this->publishes([
            __DIR__.'/../../config/telegram-logger.php' => config_path('telegram-logger.php'),
        ], 'telegram-logger-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/telegram-logger'),
        ], 'telegram-logger-views');

        $this->commands([
            GetUpdatesCommand::class,
            SendTestMessageCommand::class,
        ]);

        $errorCodes = config('telegram-logger.log_errors');

        if (count($errorCodes) > 0 && ! $this->app->runningInConsole()) {
            app(ExceptionHandler::class)->reportable(function (Throwable $e) use ($errorCodes) {
                $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                if (in_array($code, $errorCodes) && ! telegramLog()->shouldIgnore($e)) {
                    telegramLog()->reportException($e);
                }
            });
        }

        Queue::failing(function (JobFailed $event) {
            // sync jobs run inside the request, so the reportable callback above already sends them
            if (! config('telegram-logger.log_failed_jobs')
                || $event->connectionName === 'sync'
                || telegramLog()->shouldIgnore($event->exception)) {
                return;
            }

            telegramLog()->reportException($event->exception, $event);
        });
    }
}
