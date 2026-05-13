<!doctype html>
<html lang="en" class="bg-ink antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Barron AI Solutions') }}</title>

    <meta name="description" content="Enterprise-grade software, shipped in hours.">
    <meta property="og:site_name" content="Barron AI Solutions">
    <meta property="og:type" content="website">

    {{-- Fontsource variable fonts, served from /vendor below via Vite --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=fraunces:400,500,600,700,800&family=jetbrains-mono:400,500&display=swap" rel="stylesheet">

    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @inertiaHead
</head>
<body class="bg-ink text-bone font-sans">
    @inertia
</body>
</html>
