<!DOCTYPE html>
<html>
<head>
    <title>Entrants updated for {{ config('app.name') }}</title>
</head>
<body>
    <h1>Hello,</h1>
    <p>{{ $messageBody }}</p>
    <a href="{{config('app.event_url')}}" target="_blank">View Entrants</a>
</body>
</html>
