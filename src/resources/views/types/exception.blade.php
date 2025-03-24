<b>Type:</b> <code>{{ get_class($exception) }}</code>
<b>Message:</b> <i>{{ $exception->getMessage() }}</i>
<b>File:</b> <code>{{ $exception->getFile() }}</code>
<b>Line:</b> <code>{{ $exception->getLine() }}</code>

@if (!empty($additionalData))
<b>Additional Data:</b>
    @foreach ($additionalData as $key => $value)
    <b>{{ $key }}:</b> <code>{{ $value }}</code>
    @endforeach
@endif


<b>Trace:</b>
<pre>{{ $trace }}</pre>
