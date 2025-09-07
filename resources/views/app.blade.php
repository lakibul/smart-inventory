<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <title>{{ config('app.name', 'Smart Inventory') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=public-sans:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/spa.js'])

    <style>
        .debug-info {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #333;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            z-index: 9999;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="debug-info">
        App loaded at {{ now() }}
    </div>
    <div id="app">
        <div style="padding: 20px; text-align: center; font-family: Arial;">
            <h2>Loading Vue App...</h2>
            <p>If this message persists, check browser console for errors.</p>
        </div>
    </div>

    <script>
        console.log('app.blade.php loaded at:', new Date());
        console.log('CSRF token:', document.querySelector('meta[name="csrf-token"]')?.content);
        console.log('Base URL:', document.querySelector('meta[name="base-url"]')?.content);

        // Check if Vue app mounts within 5 seconds
        setTimeout(() => {
            const appDiv = document.getElementById('app');
            if (appDiv && appDiv.innerHTML.includes('Loading Vue App...')) {
                console.error('Vue app failed to mount within 5 seconds');
                appDiv.innerHTML = '<div style="padding: 20px; color: red; text-align: center;"><h2>Vue App Failed to Load</h2><p>Check browser console for errors.</p></div>';
            }
        }, 5000);
    </script>
</body>
</html>
