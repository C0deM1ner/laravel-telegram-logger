<?php

namespace C0deM1ner\LaravelTelegramLogger\Logger;

use Monolog\Logger;

class TelegramLogger
{
    /**
     * Create a custom Monolog instance.
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('telegram');
        $logger->pushHandler(new TelegramLoggerHandler($config));

        return $logger;
    }
}
