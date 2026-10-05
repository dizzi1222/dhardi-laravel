<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ $pageProps['meta']['title'] ?? config('app.name') }}</title>
    <meta name="description" content="{{ $pageProps['meta']['description'] ?? '' }}">

    @foreach (config('tenancy.locales') as $locale => $config)
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ route('home', ['lang' => $locale]) }}">
    @endforeach
    <link rel="canonical" href="{{ route('home') }}">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @inertiaHead
</head>
<body class="bg-paper text-ink antialiased">
    @inertia
</body>
</html>