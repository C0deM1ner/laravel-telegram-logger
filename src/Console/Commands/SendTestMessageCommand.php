<?php

namespace C0deM1ner\LaravelTelegramLogger\Console\Commands;

use Illuminate\Console\Command;

class SendTestMessageCommand extends Command
{
    protected $signature = 'telegram-log:send-test-message';

    protected $description = 'Send a test message to the Telegram chat';

    public function handle(): int
    {
        if (! config('telegram-logger.token') || ! config('telegram-logger.chat_id')) {
            $this->error('TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID must be set.');

            return self::FAILURE;
        }

        // the time keeps the message unique, so the duplicate check doesn't block a second run
        if (! telegramLog()->info('This is a test message from the console command ('.now()->toDateTimeString().')')) {
            $this->error('The message was not sent. Check storage/logs/laravel.log for the Telegram error, or wait a minute if you hit the limit of 5 messages per minute.');

            return self::FAILURE;
        }

        $this->info('Test message sent.');

        return self::SUCCESS;
    }
}
