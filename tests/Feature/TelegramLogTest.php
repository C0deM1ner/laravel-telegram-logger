<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true]),
    ]);
});

it('sends a formatted message to the telegram bot api', function () {
    telegramLog()->info('Hello from tests');

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
            && $request['chat_id'] === '123456'
            && $request['parse_mode'] === 'HTML'
            && str_contains($request['text'], 'Information')
            && str_contains($request['text'], 'Testing App')
            && str_contains($request['text'], 'Hello from tests');
    });
});

it('uses the token and chat id set at runtime', function () {
    telegramLog()->setToken('other-token')->setChatId('999')->error('Boom');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'botother-token')
            && $request['chat_id'] === '999';
    });
});

it('does not send the same message twice within the decay window', function () {
    telegramLog()->alert('Duplicate');
    telegramLog()->alert('Duplicate');

    Http::assertSentCount(1);
});

it('splits long messages into multiple requests', function () {
    telegramLog()->info(str_repeat('word ', 2000));

    Http::assertSentCount(3);
});
