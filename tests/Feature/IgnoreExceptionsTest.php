<?php

use C0deM1ner\LaravelTelegramLogger\Logger\TelegramLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    $this->app->instance('request', Request::create('/livewire/update', 'POST'));

    config()->set('telegram-logger.ignore', [
        ['exception' => TypeError::class, 'request' => 'livewire/update', 'contains' => 'Cannot assign'],
    ]);
});

it('does not send an exception matching an ignore rule but still logs it', function () {
    Log::spy();

    report(new TypeError('Cannot assign array to property Counter::$cartCount of type int'));

    Http::assertNothingSent();
    Log::shouldHaveReceived('error')->once();
});

it('sends the exception when one key of the rule does not match', function (Throwable $e, string $path) {
    $this->app->instance('request', Request::create($path, 'POST'));

    report($e);

    Http::assertSentCount(1);
})->with([
    'exception' => [new RuntimeException('Cannot assign'), '/livewire/update'],
    'request' => [new TypeError('Cannot assign'), '/checkout'],
    'contains' => [new TypeError('Something else'), '/livewire/update'],
]);

it('matches subclasses of the configured exception', function () {
    config()->set('telegram-logger.ignore', [['exception' => Error::class]]);

    report(new TypeError('anything'));

    Http::assertNothingSent();
});

it('treats missing keys and "*" as match-all', function () {
    config()->set('telegram-logger.ignore', [['exception' => '*', 'contains' => '*']]);

    report(new RuntimeException('anything'));

    Http::assertNothingSent();
});

it('applies ignore rules to the telegram log channel', function () {
    config()->set('logging.channels.telegram', [
        'driver' => 'custom',
        'via' => TelegramLogger::class,
        'level' => 'debug',
        'token' => 'channel-token',
        'chat_id' => '42',
        'type' => 'error',
    ]);

    Log::channel('telegram')->error('boom', ['exception' => new TypeError('Cannot assign')]);
    Http::assertNothingSent();

    Log::channel('telegram')->error('boom', ['exception' => new TypeError('Other')]);
    Http::assertSentCount(1);
});
