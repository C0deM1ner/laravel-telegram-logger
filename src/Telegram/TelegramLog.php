<?php

namespace C0deM1ner\LaravelTelegramLogger\Telegram;

use Throwable;

class TelegramLog
{
    protected const MAX_MESSAGE_LENGTH = 4096;

    protected string $token;
    protected string $chatId;
    protected string $appName;


    public function __construct()
    {
        $this->token = config('telegram-logger.token');
        $this->chatId = config('telegram-logger.chat_id');
        $this->appName = config('app.name');
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param string $chatId
     * @return $this
     */
    public function setChatId(string $chatId): self
    {
        $this->chatId = $chatId;

        return $this;
    }

    /**
     * @param string $message
     * @return void
     * @throws Throwable
     */
    public function info(string $message): void
    {
        $this->send('info', $message);
    }

    /**
     * @param string $message
     * @return void
     * @throws Throwable
     */
    public function alert(string $message): void
    {
        $this->send('alert', $message);
    }

    /**
     * @param string $message
     * @return void
     * @throws Throwable
     */
    public function error(string $message): void
    {
        $this->send('error', $message);
    }

    /**
     * @param $type
     * @param $message
     * @return void
     * @throws Throwable
     */
    private function send($type, $message): void
    {
        $chunks = $this->chunkMessage(
            $this->formatText($type, $message)
        );

        foreach ($chunks as $chunk) {
            app(TelegramBotApi::class)::sendMessage(
                $this->token,
                $this->chatId,
                $chunk
            );
        }
    }

    /**
     * @param $message
     * @return array
     */
    private function chunkMessage($message): array
    {
        return str_split($message, self::MAX_MESSAGE_LENGTH);
    }

    /**
     * @param $type
     * @param $message
     * @return string
     * @throws Throwable
     */
    private function formatText($type, $message): string
    {
        return view(
            "telegram-logger::messages.{$type}",
            [
                'appName' => $this->appName,
                'message' => $message,
            ]
        )->render();
    }
}
