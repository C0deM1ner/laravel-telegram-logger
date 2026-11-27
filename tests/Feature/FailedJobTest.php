<?php

use C0deM1ner\LaravelTelegramLogger\Logger\TelegramLogger;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Http\Client\Request;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    $this->job = Mockery::mock(Job::class, [
        'resolveName' => 'App\Jobs\SendInvoice',
        'getQueue' => 'emails',
        'attempts' => 3,
    ]);
});

it('sends failed jobs with the job details', function () {
    event(new JobFailed('redis', $this->job, new RuntimeException('Mail server down')));

    Http::assertSent(function (Request $request) {
        return str_contains($request['text'], 'App\Jobs\SendInvoice')
            && str_contains($request['text'], 'emails')
            && str_contains($request['text'], 'redis')
            && str_contains($request['text'], 'Mail server down')
            && ! str_contains($request['text'], 'Request URL');
    });
});

it('does not send failed jobs', function (Closure $setup, string $connection) {
    $setup();

    event(new JobFailed($connection, $this->job, new RuntimeException('Mail server down')));

    Http::assertNothingSent();
})->with([
    'on the sync connection' => [fn () => null, 'sync'],
    'matching an ignore rule' => [fn () => config()->set('telegram-logger.ignore', [['contains' => 'Mail server']]), 'redis'],
    'when log_failed_jobs is off' => [fn () => config()->set('telegram-logger.log_failed_jobs', false), 'redis'],
]);

it('does not send the final attempt again through the log channel', function () {
    config()->set('logging.channels.telegram', ['driver' => 'custom', 'via' => TelegramLogger::class]);
    $exception = new RuntimeException('Mail server down');

    event(new JobFailed('redis', $this->job, $exception));
    Log::channel('telegram')->error('Mail server down', ['exception' => $exception]);

    Http::assertSentCount(1);
});
