<?php

use Illuminate\Support\Facades\Http;

it('lists each chat once with its latest message', function () {
    Http::fake(['api.telegram.org/bottest-token/getUpdates' => Http::response(['ok' => true, 'result' => [
        ['update_id' => 1, 'message' => ['text' => 'hi', 'chat' => ['id' => 111, 'type' => 'private', 'first_name' => 'Alex']]],
        ['update_id' => 2, 'message' => ['text' => 'again', 'chat' => ['id' => 111, 'type' => 'private', 'first_name' => 'Alex']]],
        ['update_id' => 3, 'my_chat_member' => ['chat' => ['id' => -100222, 'type' => 'supergroup', 'title' => 'Alerts']]],
    ]])]);

    $this->artisan('telegram-log:get-updates')
        ->expectsTable(['Chat ID', 'Type', 'Name', 'Last message'], [
            [111, 'private', 'Alex', 'again'],
            [-100222, 'supergroup', 'Alerts', ''],
        ])
        ->assertExitCode(0);
});

it('explains what to do when there are no updates', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);

    $this->artisan('telegram-log:get-updates')
        ->expectsOutputToContain('Send a message to the bot')
        ->assertExitCode(0);
});

it('fails with the telegram error', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Unauthorized'], 401)]);

    $this->artisan('telegram-log:get-updates')
        ->expectsOutputToContain('Unauthorized')
        ->assertExitCode(1);
});

it('fails when no token is configured', function () {
    config()->set('telegram-logger.token', '');

    $this->artisan('telegram-log:get-updates')
        ->expectsOutputToContain('TELEGRAM_BOT_TOKEN is not set')
        ->assertExitCode(1);
});
