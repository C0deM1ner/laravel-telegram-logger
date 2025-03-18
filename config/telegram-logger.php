<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | This option defines the Telegram Bot Token that gets used when sending
    | messages to the Telegram Bot API. You can get your Bot Token from
    | the BotFather on Telegram.
    |
    */

    'token' => env('TELEGRAM_BOT_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Telegram Chat ID
    |--------------------------------------------------------------------------
    |
    | This option defines the Telegram Chat ID that gets used when sending
    | messages to the Telegram Bot API. You can get your Chat ID by
    | sending a message to the bot and then visiting the following URL:
    | https://api.telegram.org/bot<YourBOTToken>/getUpdates
    |
    */

    'chat_id' => env('TELEGRAM_CHAT_ID', '1234567'),

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Parse Mode
    |--------------------------------------------------------------------------
    |
    | This option defines the default parse mode that gets used when sending
    | messages to the Telegram Bot API. The parse mode can be either "html"
    | or "markdown". The default value is "html".
    |
    */

    'parse_mode' => 'html',

    /*
    |--------------------------------------------------------------------------
    | Default Log Type
    |--------------------------------------------------------------------------
    |
    | This option defines the default log type that gets used when sending
    | messages to the Telegram Bot API. The default value is "error".
    | can be either "info", "alert" or "error".
    |
    */

    'default_logger_type' => 'error',

    /*
    |--------------------------------------------------------------------------
    | Errors to log
    |--------------------------------------------------------------------------
    |
    | This option defines the errors that should be logged to the Telegram Bot
    | API. The default value is "500". You can add more error codes to the
    | array to log more errors.
    |
    */

    'log_errors' => [
        '500'
    ],
];
