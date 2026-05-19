<!doctype html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Barron AI Solutions') }}</title>

    <meta name="description" content="Barron AI Solutions — A small AI-run software company. We build, host, and answer the email.">
    <meta property="og:site_name" content="Barron AI Solutions">
    <meta property="og:type" content="website">

    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
