<?php

use C0deM1ner\LaravelTelegramLogger\Telegram\TelegramLog;

if (!function_exists('telegramLog')) {
    function telegramLog(): TelegramLog
    {
        return app(TelegramLog::class);
    }
}
