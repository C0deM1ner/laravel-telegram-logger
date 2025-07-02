<b>Type:</b> <code>{{ get_class($exception) }}</code>
<b>Message:</b> <i>{{ $exception->getMessage() }}</i>
<b>File:</b> <code>{{ $exception->getFile() }}</code>
<b>Line:</b> <code>{{ $exception->getLine() }}</code>


@if (!empty($additionalData))
<code>_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-</code>

<b>Additional Data:</b>
    @foreach ($additionalData as $key => $value)
    <b>{{ $key }}:</b> <code>{{ $value }}</code>
    @endforeach
@endif


@if (!empty(request()->all()))
<code>_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-</code>

<b>Request Parameters:</b>
<pre>{{ json_encode(request()->all(), JSON_PRETTY_PRINT) }}</pre>
@endif

@if (!empty(session()->all()))
<code>_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-</code>

<b>Session Data:</b>
<pre>{{ json_encode(session()->all(), JSON_PRETTY_PRINT) }}</pre>
@endif

<code>_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-</code>

<b>Trace:</b>
<pre>{{ $trace }}</pre>
