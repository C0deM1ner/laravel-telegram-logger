@php($paramsJson = json_encode($params, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE))
@if ($event)
<b>Job:</b> <code>{{ $event->job->resolveName() }}</code>
<b>Queue:</b> <code>{{ $event->job->getQueue() }}</code> (<code>{{ $event->connectionName }}</code>)
<b>Attempts:</b> {{ $event->job->attempts() }}
@else
<b>Request URL:</b> ({{ request()->method() }}) <code>{{ request()->url() }}</code>
@endif
<b>Exception:</b> <code>{{ get_class($exception) }}</code>
<b>Message:</b> <em>{{ $exception->getMessage() }}</em>

<blockquote expandable>
<b>File:</b> <code>{{ $exception->getFile() }}</code>
<b>Line:</b> <code>{{ $exception->getLine() }}</code>
@unless ($event)
<b>User Agent:</b> <code>{{ request()->header('User-Agent') }}</code>
@endunless
</blockquote>
@if (! empty($params))
@if (mb_strlen($paramsJson) > 600)
<b>Request parameters are too long to show.</b>
@else
<b>Request Parameters:</b>
<pre>{{ $paramsJson }}</pre>
@endif
@endif

<b>Trace:</b>
<blockquote expandable>{{ $trace }}</blockquote>
