<?php

use C0deM1ner\LaravelTelegramLogger\Logger\TelegramLogger;
use C0deM1ner\LaravelTelegramLogger\Telegram\Exceptions\TelegramBotApiException;
use C0deM1ner\LaravelTelegramLogger\Telegram\TelegramBotApi;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
});

it('hides passwords and the csrf token from request parameters', function () {
    $this->app->instance('request', HttpRequest::create('/login', 'POST', [
        'email' => 'user@example.com',
        'password' => 'hunter2',
        'password_confirmation' => 'hunter2',
        'current_password' => 'old-secret',
        '_token' => 'csrf-token-value',
    ]));

    report(new RuntimeException('Login failed'));

    Http::assertSent(function (Request $request) {
        return str_contains($request['text'], 'user@example.com')
            && ! str_contains($request['text'], 'hunter2')
            && ! str_contains($request['text'], 'old-secret')
            && ! str_contains($request['text'], 'csrf-token-value');
    });
});

it('keeps the bot token out of reported connection errors', function () {
    Exceptions::fake();
    Http::fake(fn () => throw new ConnectionException('cURL error 28 for https://api.telegram.org/bottest-token/sendMessage'));

    expect(TelegramBotApi::sendMessage('test-token', '1', 'hi'))->toBeFalse();

    Exceptions::assertReported(fn (TelegramBotApiException $e) => str_contains($e->getMessage(), 'bot***/sendMessage')
        && ! str_contains($e->getMessage(), 'test-token'));
});

it('treats a plain class name in ignore as an exception rule, not match-all', function () {
    config()->set('telegram-logger.ignore', [TypeError::class]);

    report(new TypeError('ignored'));
    Http::assertNothingSent();

    report(new RuntimeException('still sent'));
    Http::assertSentCount(1);
});

it('sends a web exception once when the telegram channel is in the log stack', function () {
    config()->set('logging.channels.telegram', ['driver' => 'custom', 'via' => TelegramLogger::class]);
    config()->set('logging.channels.stack', ['driver' => 'stack', 'channels' => ['telegram']]);
    config()->set('logging.default', 'stack');

    report(new RuntimeException('once please'));

    Http::assertSentCount(1);
});

it('never sends its own api errors, so a failing telegram can not loop', function () {
    report(new TelegramBotApiException('Telegram is down'));

    Http::assertNothingSent();
});

it('does not log from the rate limiter', function () {
    telegramLog()->info('Same message');
    Log::spy();

    expect(telegramLog()->info('Same message'))->toBeFalse();

    Log::shouldNotHaveReceived('info');
});

it('does not call the api without a token or chat id', function (string $key) {
    config()->set("telegram-logger.{$key}", '');

    expect(telegramLog()->info('hi'))->toBeFalse();

    Http::assertNothingSent();
})->with(['token', 'chat_id']);
