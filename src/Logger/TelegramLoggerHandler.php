<?php

namespace C0deM1ner\LaravelTelegramLogger\Logger;

use C0deM1ner\LaravelTelegramLogger\Telegram\TelegramBotApi;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;

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

    protected function write(LogRecord|array $record): void
    {
        app(TelegramBotApi::class)::sendMessage(
            $this->token,
            $this->chatId,
            config('app.name') . PHP_EOL .
            $record['formatted']
        );
    }
}
