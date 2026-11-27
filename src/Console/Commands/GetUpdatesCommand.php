<?php

namespace C0deM1ner\LaravelTelegramLogger\Console\Commands;

use C0deM1ner\LaravelTelegramLogger\Telegram\Exceptions\TelegramBotApiException;
use C0deM1ner\LaravelTelegramLogger\Telegram\TelegramBotApi;
use Illuminate\Console\Command;

class GetUpdatesCommand extends Command
{
    protected $signature = 'telegram-log:get-updates';

    protected $description = 'List chats that recently messaged the bot, to find the chat id';

    public function handle(): int
    {
        $token = config('telegram-logger.token');

        if (! $token) {
            $this->error('TELEGRAM_BOT_TOKEN is not set.');

            return self::FAILURE;
        }

        try {
            $updates = TelegramBotApi::getUpdates($token);
        } catch (TelegramBotApiException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        // every update type (message, channel_post, my_chat_member, ...) carries a "chat"
        $chats = collect($updates)
            ->map(fn (array $update) => collect($update)->first(fn ($value) => is_array($value) && isset($value['chat'])))
            ->filter()
            ->keyBy(fn (array $item) => $item['chat']['id'])
            ->map(fn (array $item) => [
                $item['chat']['id'],
                $item['chat']['type'],
                $item['chat']['title'] ?? $item['chat']['username'] ?? $item['chat']['first_name'] ?? '',
                $item['text'] ?? '',
            ]);

        if ($chats->isEmpty()) {
            $this->info('No updates. Send a message to the bot (or add it to your group) and run this again. Telegram keeps updates for 24 hours.');

            return self::SUCCESS;
        }

        $this->table(['Chat ID', 'Type', 'Name', 'Last message'], $chats->values()->all());

        return self::SUCCESS;
    }
}
