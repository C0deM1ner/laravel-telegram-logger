<?php

namespace C0deM1ner\LaravelTelegramLogger\Types;

use Illuminate\Queue\Events\JobFailed;
use Throwable;

class FormatExceptionForTelegramType
{
    public const HIDDEN_PARAMS = ['_token', 'password', 'password_confirmation', 'current_password'];

    /**
     * @throws Throwable
     */
    public function execute(Throwable $exception, ?JobFailed $event = null): string
    {
        return view('telegram-logger::types.exception', [
            'exception' => $exception,
            'event' => $event,
            'params' => $event ? [] : request()->except(self::HIDDEN_PARAMS),
            'trace' => $this->formatTrace($exception),
        ])->render();
    }

    protected function formatTrace(Throwable $exception): string
    {
        return collect($exception->getTrace())
            ->take(3)
            ->map(function ($trace, $index) {
                $file = $trace['file'] ?? '[internal function]';
                $line = $trace['line'] ?? '';
                $function = $trace['function'] ?? '';

                return ($index + 1).". {$file}:{$line} - {$function}";
            })
            ->implode("\n");
    }
}
