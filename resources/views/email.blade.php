<!DOCTYPE html>
<html>
<head>
    <title>Error Exception</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <style type="text/css">
        body{font-family:sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}.break-long-words{word-wrap:break-word;overflow-wrap:break-word;-webkit-hyphens:auto;-moz-hyphens:auto;hyphens:auto;min-width:0}table,table tr,table tr td{border-width:0;background:#fff;color:#595959}table tr td,table tr th{overflow-wrap:break-word;border-width:1px;padding:10px}table tr th{text-align:left;font-weight:600;color:#b0413e}table tr td>table td,table tr td>table th{border-width:0;padding:5px 0}table tr td>table td{overflow-wrap:anywhere}.block{display:block}table.trace td,table.trace th{border-top:1px dashed #eb817e;padding-top:8px;padding-bottom:8px}.trace-line{font-size:14px;position:relative}.trace-file-path,.trace-file-path *{font-size:13px}.trace-type{padding:0 2px}.trace-method{color:#b0413e;font-weight:700}.trace-arguments{color:#777;font-weight:400;padding-left:2px}
    </style>
</head>
<body style="padding: 0;margin: 0;font-family: sans-serif">
<table style="width: 100%; border-width:0; margin: 0; ">
    <tr>
        <td>
            <table style="width: 100%; margin: 0;">
                <tr>
                    <th colspan="2" style="text-align: center; font-size: 20px">{{ $title }}</th>
                </tr>
                <tr>
                    <th>Message:</th>
                    <td> {{ $exception->getMessage() }}</td>
                </tr>
                <tr>
                    <th>File:</th>
                    <td> {{ $exception->getFile() }}:{{ $exception->getLine() }}</td>
                </tr>
                <tr>
                    <th>Request:</th>
                    <td><span style="margin-right: 3px">{{ strtolower(request()->getMethod()) }}:</span>&nbsp;{{ request()->url() }}</td>
                </tr>
                <tr>
                    <th>Date:</th>
                    <td>{{ Carbon\Carbon::now()->toDateTimeString() }}</td>
                </tr>
                <tr>
                    <th>User:</th>
                    <td> {{ auth()->check() ? auth()->user()->id : 'Not logged in' }}</td>
                </tr>
                <tr>
                    <th>IP:</th>
                    <td> {{ request()->ip() }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <h3 style="color: #b0413e;font-size: 16px;margin: 0 0 10px;">Stack trace:</h3>
            <table style="width: 100%; margin: 0;" class="table trace">
                @foreach($exception->getTrace() as $trace)
                    @if($loop->index >= 40)
                        @break
                    @endif

                    @php
                        $function = $trace['function'] ?? '';
                        $class = $trace['class'] ?? '';
                        $type = $trace['type'] ?? '';
                        $args = $trace['args'] ?? '';
                        $file = $trace['file'] ?? '';
                        $line = $trace['line'] ?? '';
                    @endphp

                    <tr>
                        <th valign="top" style="vertical-align: top">#{{$loop->index+1}}</th>
                        <td>
                            <div class="trace-line break-long-words">
                                @if($function)
                                    @if($class)
                                        <span class="trace-class">{{ $class }}</span>
                                        @if($type)
                                            <span class="trace-type">{{ $type }}</span>
                                        @endif
                                    @endif
                                    <span class="trace-method">
                                        {!! $function !!}(<span class="trace-arguments">{!! $exception->formatArgs($args) !!}</span>)
                                    </span>
                                @endif
                                @if($file)
                                    <span class="block trace-file-path">in {{ $file }} (line: {{ $line }})</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>
</body>
</html>