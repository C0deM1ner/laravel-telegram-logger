<?php

use Illuminate\Support\Facades\Http;

it('succeeds when telegram accepts the message, also on a second run', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    $this->artisan('telegram-log:send-test-message')->expectsOutputToContain('Test message sent')->assertExitCode(0);

    $this->travel(1)->seconds();

    $this->artisan('telegram-log:send-test-message')->assertExitCode(0);
    Http::assertSentCount(2);
});

it('fails when telegram rejects the message', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Unauthorized'], 401)]);

    $this->artisan('telegram-log:send-test-message')->expectsOutputToContain('was not sent')->assertExitCode(1);
});

it('fails without a token', function () {
    config()->set('telegram-logger.token', '');

    $this->artisan('telegram-log:send-test-message')->expectsOutputToContain('must be set')->assertExitCode(1);
});
