<?php

namespace C0deM1ner\LaravelTelegramLogger\Telegram;

use C0deM1ner\LaravelTelegramLogger\Telegram\Exceptions\TelegramBotApiException;
use Illuminate\Support\Facades\Http;
use Throwable;

class TelegramBotApi
{
    public const HOST = 'https://api.telegram.org/bot';

    public static function getUpdates(string $token): array
    {
        try {
            return Http::timeout(10)->get(self::HOST.$token.'/getUpdates')->throw()->json('result', []);
        } catch (Throwable $e) {
            throw new TelegramBotApiException(self::hideToken($token, $e->getMessage()));
        }
    }

    public static function sendMessage(string $token, string $chatId, string $text = ''): bool
    {
        try {
            $response = Http::timeout(10)->post(self::HOST.$token.'/sendMessage', [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ])->throw()->json();

            return $response['ok'] ?? false;
        } catch (Throwable $e) {
            report(new TelegramBotApiException(self::hideToken($token, $e->getMessage())));

            return false;
        }
    }

    // connection errors include the request URL, which contains the token
    private static function hideToken(string $token, string $message): string
    {
        return $token === '' ? $message : str_replace($token, '***', $message);
    }
}
