<?php

return [
    'log_errors' => env('TELEGRAM_LOG_ERRORS', '500,502,503,504'),

    'chat_id' => env('TELEGRAM_CHAT_ID', '1234567'),

    'token' => env('TELEGRAM_BOT_TOKEN', '')
];
