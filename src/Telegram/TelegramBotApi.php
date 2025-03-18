<?php

namespace C0deM1ner\LaravelTelegramLogger\Telegram;

use C0deM1ner\LaravelTelegramLogger\Telegram\Exceptions\TelegramBotApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
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
        $key = 'telegram_logger';

        if (RateLimiter::tooManyAttempts($key, 5)) {
            info('Telegram rate limit exceeded');
            return false;
        }

        RateLimiter::hit($key);

        try {
            $url = self::HOST . $token . '/sendMessage';

            $response = Http::withoutVerifying()->post($url, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ])->throw()->json();

            return $response['ok'] ?? false;
        } catch (Throwable $e) {
            report(new TelegramBotApiException($e->getMessage()));

            return false;
        }
    }
}
