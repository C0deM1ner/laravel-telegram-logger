<?php

namespace C0deM1ner\LaravelTelegramLogger\Tests;

use C0deM1ner\LaravelTelegramLogger\Providers\TelegramLoggerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            TelegramLoggerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.name', 'Testing App');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('telegram-logger.token', 'test-token');
        $app['config']->set('telegram-logger.chat_id', '123456');
    }
}
