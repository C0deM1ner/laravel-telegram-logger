<b>Type:</b> <code>{{ get_class($exception) }}</code>
<b>Message:</b> <i>{{ $exception->getMessage() }}</i>
<b>File:</b> <code>{{ $exception->getFile() }}</code>
<b>Line:</b> <code>{{ $exception->getLine() }}</code>

<code>_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-</code>

@if (!empty($additionalData))
<b>Additional Data:</b>
    @foreach ($additionalData as $key => $value)
    <b>{{ $key }}:</b> <code>{{ $value }}</code>
    @endforeach
@endif

---
<code>_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-</code>

@if (!empty($requestParameters))
<b>Request Parameters:</b>
<pre>{{ json_encode($requestParameters, JSON_PRETTY_PRINT) }}</pre>
@endif

<code>_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-</code>

<b>Trace:</b>
<pre>{{ $trace }}</pre>
