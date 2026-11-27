<?php

return [

    /*
     * Bot token from @BotFather and the chat to send messages to.
     * Find the chat id with: php artisan telegram-log:get-updates
     */

    'token' => env('TELEGRAM_BOT_TOKEN', ''),

    'chat_id' => env('TELEGRAM_CHAT_ID', ''),

    /*
     * HTTP status codes that are reported automatically.
     * Exceptions without a status code count as 500. Use [] to turn it off.
     */

    'log_errors' => [500],

    /*
     * Report queued jobs that fail after their last attempt.
     */

    'log_failed_jobs' => true,

    /*
     * Exceptions that are never sent to Telegram (they still go to your other logs).
     * Every key is optional and "*" wildcards are allowed:
     *
     * [
     *     'exception' => TypeError::class,    // subclasses match too
     *     'request'   => 'livewire/update',   // request path
     *     'contains'  => 'Cannot assign',     // text in the exception message
     * ],
     */

    'ignore' => [
        //
    ],

    /*
     * The same message is sent at most `max_attempts` times per `decay_seconds`.
     * By default: once a day.
     */

    'max_attempts' => 1,

    'decay_seconds' => 60 * 60 * 24,

];
