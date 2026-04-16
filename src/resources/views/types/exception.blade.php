@props([
    'exception',
    'trace',
    'requestParamsJson' => json_encode(request()->all(), JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR)
])
<b>Request URL:</b> ({{ request()->method() }}) <code>{{ request()->url() }}</code>
<b>Exception:</b> <code>{{ get_class($exception) }}</code>
<b>Message:</b> <em>{{ $exception->getMessage() }}</em>

<blockquote expandable>
<b>File:</b> <code>{{ $exception->getFile() }}</code>
<b>Line:</b> <code>{{ $exception->getLine() }}</code>
<b>User Agent:</b> <code>{{ request()->header('User-Agent') }}</code>
</blockquote>
@if (!empty(request()->all()))
@if(mb_strlen($requestParamsJson) > 600)
<b>Request parameters is too long!!</b>
@else
<b>Request Parameters:</b>
<pre>{{ $requestParamsJson }}</pre>
@endif
@endif

<b>Trace:</b>
<blockquote expandable>{{ $trace }}</blockquote>
