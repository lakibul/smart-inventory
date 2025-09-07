<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Smart Inventory</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #007bff; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Smart Inventory System - Debug Page</h1>
        <p class="success">✓ Laravel is running correctly!</p>
    </div>

    <div class="card">
        <h2>Environment Information</h2>
        <ul>
            <li><strong>Laravel Version:</strong> {{ app()->version() }}</li>
            <li><strong>PHP Version:</strong> {{ PHP_VERSION }}</li>
            <li><strong>Environment:</strong> {{ app()->environment() }}</li>
            <li><strong>App URL:</strong> {{ config('app.url') }}</li>
            <li><strong>Current Time:</strong> {{ now() }}</li>
        </ul>
    </div>

    <div class="card">
        <h2>Vite Assets</h2>
        <p>Checking if Vite assets are being loaded...</p>
        <div id="vite-test">
            <p class="error">Vite assets not loaded yet...</p>
        </div>
    </div>

    <div class="card">
        <h2>Database Status</h2>
        <p>
            @php
                try {
                    DB::connection()->getPdo();
                    echo '<span class="success">✓ Database connection successful</span>';
                } catch (Exception $e) {
                    echo '<span class="error">✗ Database connection failed: ' . $e->getMessage() . '</span>';
                }
            @endphp
        </p>
    </div>

    <div class="card">
        <h2>API Test</h2>
        <button onclick="testAPI()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Test API Connection</button>
        <div id="api-result" style="margin-top: 10px;"></div>
    </div>

    <div class="card">
        <h2>Next Steps</h2>
        <p>If everything above shows green checkmarks, you can access the main application:</p>
        <p><a href="/" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Go to Smart Inventory App</a></p>
    </div>

    @vite(['resources/css/app.css', 'resources/js/spa.js'])

    <script>
        // Test if Vite assets loaded
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('vite-test').innerHTML = '<p class="success">✓ Vite assets loaded successfully!</p>';
        });

        // Test API connection
        async function testAPI() {
            const resultDiv = document.getElementById('api-result');
            resultDiv.innerHTML = '<p class="info">Testing API...</p>';

            try {
                const response = await fetch('/api/test', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    resultDiv.innerHTML = '<p class="success">✓ API connection successful!</p>';
                } else {
                    resultDiv.innerHTML = '<p class="error">✗ API returned status: ' + response.status + '</p>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<p class="error">✗ API connection failed: ' + error.message + '</p>';
            }
        }
    </script>
</body>
</html>
