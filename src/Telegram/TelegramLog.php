<?php

namespace C0deM1ner\LaravelTelegramLogger\Telegram;

class TelegramLog
{
    public string $token;
    public string $chatId;
    public string $message;


    public function __construct($message)
    {
        $this->token = config('telegram-logger.token');
        $this->chatId = config('telegram-logger.chat_id');
        $this->message = $message;
    }

    public function info(string $message): void
    {
        $this->send('info', $message);
    }

    public function alert(string $message): void
    {
        $this->send('alert', $message);
    }

    private function send($type, $message): void
    {
        TelegramBotApi::sendMessage('token', 'chat_id', $message);
    }
}
