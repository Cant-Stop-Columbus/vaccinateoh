<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <!-- Scripts -->
        @routes
        <script src="{{ mix('js/app.js') }}" defer></script>
        <!-- Global site tag (gtag.js) - Google Analytics -->
@env('production')
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-JS5FH1S5NW"></script>
@endenv
        <script>
@if(!App::environment('production'))
            /* Google Analytics is disabled if not in production
@endif
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-JS5FH1S5NW');
@if(!App::environment('production'))
            */
@endif
        </script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
