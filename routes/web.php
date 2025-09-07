<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Test route to verify Laravel is working
Route::get('/test', function () {
    return response()->json([
        'message' => 'Laravel is working!',
        'timestamp' => now()
    ]);
});

// Debug route
Route::get('/debug', function () {
    return view('debug');
});

// Simple HTML test route
Route::get('/simple', function () {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Smart Inventory - Simple Test</title>
        <style>
            body { font-family: Arial; padding: 40px; background: #f5f5f5; }
            .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .success { color: #28a745; }
            .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1 class="success">✅ Laravel is Working!</h1>
            <p>Smart Inventory System - Simple Test Page</p>
            <p><strong>Time:</strong> ' . now() . '</p>
            <div>
                <a href="/" class="btn">Go to Main App</a>
                <a href="/debug" class="btn">Debug Page</a>
                <a href="/login" class="btn">Login Page</a>
            </div>
        </div>
    </body>
    </html>';
});

// Main SPA route - catch all routes and let Vue handle routing
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
