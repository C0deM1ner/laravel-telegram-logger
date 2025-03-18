<?php

namespace C0deM1ner\LaravelTelegramLogger\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class TelegramLoggerHandler extends AbstractProcessingHandler
{
    public string $token;
    public string $chatId;

    public function __construct(array $config)
    {
        $level = Logger::toMonologLevel($config['level']);

        parent::__construct($level);

        $this->token = $config['token'];
        $this->chatId = $config['chat_id'];
    }

    protected function write($record): void
    {
        $type = config('telegram-logger.default_logger_type', 'error');

        telegramLog()
            ->setToken($this->token)
            ->setChatId($this->chatId)
            ->$type($record['formatted']);
    }
}
