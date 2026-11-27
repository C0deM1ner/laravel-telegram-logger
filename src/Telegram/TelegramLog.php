<?php

namespace C0deM1ner\LaravelTelegramLogger\Telegram;

use C0deM1ner\LaravelTelegramLogger\Actions\ChunkTelegramMessageAction;
use C0deM1ner\LaravelTelegramLogger\Telegram\Exceptions\TelegramBotApiException;
use C0deM1ner\LaravelTelegramLogger\Types\FormatExceptionForTelegramType;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class TelegramLog
{
    // Laravel reports an exception to the reportable callbacks before logging it, so the log
    // channel can skip the one automatic reporting just sent instead of sending it twice.
    protected static ?Throwable $lastReported = null;

    protected string $token;

    protected string $chatId;

    protected string $appName;

    public function __construct()
    {
        $this->token = (string) config('telegram-logger.token');
        $this->chatId = (string) config('telegram-logger.chat_id');
        $this->appName = (string) config('app.name');
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function setChatId(string $chatId): self
    {
        $this->chatId = $chatId;

        return $this;
    }

    public function shouldIgnore(Throwable $e): bool
    {
        if ($e instanceof TelegramBotApiException) {
            return true;
        }

        foreach (config('telegram-logger.ignore', []) as $rule) {
            $rule = is_string($rule) ? ['exception' => $rule] : $rule;
            $exception = $rule['exception'] ?? '*';

            if (($exception === '*' || $e instanceof $exception)
                && request()->is($rule['request'] ?? '*')
                && Str::is('*'.($rule['contains'] ?? '*').'*', $e->getMessage())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Throwable
     */
    public function reportException(Throwable $e, ?JobFailed $event = null): bool
    {
        static::$lastReported = $e;

        return $this->error((new FormatExceptionForTelegramType)->execute($e, $event));
    }

    public function wasReported(Throwable $e): bool
    {
        return static::$lastReported === $e;
    }

    protected function rateLimiter($message): bool
    {
        $key = md5('telegram_logger'.$message);
        $decaySeconds = config('telegram-logger.decay_seconds', 60 * 60 * 24);
        $maxAttempts = config('telegram-logger.max_attempts', 1);

        // no logging here: if the telegram channel is in the log stack, it would loop back into this class
        if (RateLimiter::tooManyAttempts($key, $maxAttempts) || RateLimiter::tooManyAttempts('telegram_logger', 5)) {
            return false;
        }

        RateLimiter::hit($key, $decaySeconds);
        RateLimiter::hit('telegram_logger');

        return true;
    }

    /**
     * @throws Throwable
     */
    public function info(string $message): bool
    {
        return $this->send('info', $message);
    }

    /**
     * @throws Throwable
     */
    public function alert(string $message): bool
    {
        return $this->send('alert', $message);
    }

    /**
     * @throws Throwable
     */
    public function error(string $message): bool
    {
        return $this->send('error', $message);
    }

    /**
     * @throws Throwable
     */
    private function send($type, $message): bool
    {
        if ($this->token === '' || $this->chatId === '' || ! $this->rateLimiter($message)) {
            return false;
        }

        $chunks = app(ChunkTelegramMessageAction::class)->execute($this->formatText($type, $message));

        $sent = true;

        foreach ($chunks as $chunk) {
            $sent = TelegramBotApi::sendMessage($this->token, $this->chatId, $chunk) && $sent;
        }

        return $sent;
    }

    /**
     * @throws Throwable
     */
    private function formatText($type, $message): string
    {
        return view("telegram-logger::messages.{$type}", [
            'appName' => $this->appName,
            'message' => $message,
        ])->render();
    }
}
