<?php

namespace C0deM1ner\LaravelTelegramLogger\Telegram;

use C0deM1ner\LaravelTelegramLogger\Telegram\Exceptions\TelegramBotApiException;
use Illuminate\Support\Facades\Http;
use Throwable;

class TelegramBotApi
{
    public const HOST = 'https://api.telegram.org/bot';

    /**
     * @param string $token
     * @param string $chatId
     * @param string $text
     * @return bool
     */
    public static function sendMessage(string $token, string $chatId, string $text = ''): bool
    {
        try {
            $url = self::HOST . $token . '/sendMessage';

            $response = Http::withoutVerifying()->post($url, [
                'chat_id' => $chatId,
                'text' => $text
            ])->throw()->json();

            return $response['ok'] ?? false;
        } catch (Throwable $e) {
            report(new TelegramBotApiException($e->getMessage()));

            return false;
        }
    }
}
