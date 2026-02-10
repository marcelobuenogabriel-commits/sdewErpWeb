<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="{{ asset('vendor/adminlte/dist/css/custom.css') }}" rel="stylesheet">

    <!-- Scripts -->
    
</head>
<body style="margin: 0;">
    <div id="app">
        <main class="py-4 divContainerLogin">
            @yield('content')
        </main>
    </div>
</body>
</html>
