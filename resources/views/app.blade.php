<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-env" content="{{ config('app.env') }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Self-hosted fonts (avoid @import /assets/... in Vite CSS — PostCSS treats it as a disk path). -->
    <link href="{{ asset('assets/fonts/vazirmatn.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/fonts/inter/inter.css') }}" rel="stylesheet" />

    <!-- Icon Fonts -->

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div id="app"></div>
</body>
</html>

