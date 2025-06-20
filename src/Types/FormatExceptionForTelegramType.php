<?php

namespace C0deM1ner\LaravelTelegramLogger\Types;

use Throwable;

class FormatExceptionForTelegramType
{
    /**
     * @throws Throwable
     */
    public function execute(Throwable $exception, $additionalData = [], $requestParameters = []): string
    {
        return view('telegram-logger::types.exception', [
            'exception' => $exception,
            'additionalData' => $additionalData,
            'requestParameters' => $requestParameters,
            'trace' => $this->formatTrace($exception),
        ])->render();
    }

    protected function formatTrace($exception): string
    {
        return collect($exception->getTrace())
            ->map(function ($trace, $index) {
                $file = $trace['file'] ?? '[internal function]';
                $line = $trace['line'] ?? '';
                $function = $trace['function'] ?? '';
                return ($index + 1) . ". `{$file}:{$line}` - {$function}";
            })
            ->take(5)
            ->implode("\n");
    }
}
