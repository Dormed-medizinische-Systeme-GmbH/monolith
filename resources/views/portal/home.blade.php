<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $applicationContext?->label() ?? config('app.name') }}</title>
</head>
<body>
    <h1>{{ $applicationContext->label() }}</h1>
    <p>Application context: <code>{{ $applicationContext->value }}</code> ({{ request()->getHost() }})</p>
    <p>Platzhalter-Landing. Kunden-Portal-Oberfläche folgt in einer späteren Phase.</p>
</body>
</html>
