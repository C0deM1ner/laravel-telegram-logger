<?php

use C0deM1ner\LaravelTelegramLogger\Logger\TelegramLogger;
use C0deM1ner\LaravelTelegramLogger\Logger\TelegramLoggerHandler;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('sends log records through a custom telegram channel', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    config()->set('logging.channels.telegram', [
        'driver' => 'custom',
        'via' => TelegramLogger::class,
        'level' => 'debug',
        'token' => 'channel-token',
        'chat_id' => '42',
        'type' => 'alert',
    ]);

    Log::channel('telegram')->warning('Disk almost full');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'botchannel-token')
            && $request['chat_id'] === '42'
            && str_contains($request['text'], 'Alert')
            && str_contains($request['text'], 'Disk almost full');
    });
});

it('escapes html in log records', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
    config()->set('logging.channels.telegram', ['driver' => 'custom', 'via' => TelegramLogger::class]);

    Log::channel('telegram')->info('disk < 10% & falling');

    Http::assertSent(fn (Request $request) => str_contains($request['text'], 'disk &lt; 10% &amp; falling'));
});

it('rejects an unknown channel type', function () {
    new TelegramLoggerHandler(['type' => 'warning']);
})->throws(InvalidArgumentException::class, 'must be info, alert or error');

it('falls back to the package config when the channel only sets driver and via', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    config()->set('logging.channels.telegram', [
        'driver' => 'custom',
        'via' => TelegramLogger::class,
    ]);

    Log::channel('telegram')->debug('Minimal channel');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'bottest-token')
            && $request['chat_id'] === '123456'
            && str_contains($request['text'], 'Error')
            && str_contains($request['text'], 'Minimal channel');
    });
});
