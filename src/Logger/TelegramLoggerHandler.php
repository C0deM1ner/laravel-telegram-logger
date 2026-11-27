<?php

namespace C0deM1ner\LaravelTelegramLogger\Logger;

use InvalidArgumentException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Throwable;

class TelegramLoggerHandler extends AbstractProcessingHandler
{
    public string $token;

    public string $chatId;

    public string $type;

    public function __construct(array $config)
    {
        parent::__construct(Logger::toMonologLevel($config['level'] ?? 'debug'));

        $this->token = (string) ($config['token'] ?? config('telegram-logger.token'));
        $this->chatId = (string) ($config['chat_id'] ?? config('telegram-logger.chat_id'));
        $this->type = $config['type'] ?? 'error';

        if (! in_array($this->type, ['info', 'alert', 'error'], true)) {
            throw new InvalidArgumentException("Telegram log channel type must be info, alert or error, [{$this->type}] given.");
        }
    }

    protected function write($record): void
    {
        $exception = $record['context']['exception'] ?? null;

        if ($exception instanceof Throwable
            && (telegramLog()->wasReported($exception) || telegramLog()->shouldIgnore($exception))) {
            return;
        }

        telegramLog()
            ->setToken($this->token)
            ->setChatId($this->chatId)
            ->{$this->type}(e($record['formatted']));
    }
}
