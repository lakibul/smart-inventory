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

// Simple HTML test (no Vue)
Route::get('/html-test', function () {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <title>HTML Test</title>
        <style>
            body { font-family: Arial; padding: 20px; background: #f0f0f0; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .success { color: #28a745; }
            .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="success">✅ Laravel Route Working!</h1>
            <div class="info">
                <strong>URL:</strong> ' . request()->fullUrl() . '<br>
                <strong>Time:</strong> ' . now() . '<br>
                <strong>Environment:</strong> ' . app()->environment() . '
            </div>
            <p>This confirms that Laravel routing is working correctly.</p>
            <p><a href="/">Go to Main App</a> | <a href="/vue-cdn">Vue CDN Test</a></p>
        </div>
    </body>
    </html>';
});

// Debug route
Route::get('/debug', function () {
    return view('debug');
});

// App debug route - loads app.blade.php with debug info
Route::get('/app-debug', function () {
    return view('app');
});

// Simple Vue CDN test
Route::get('/vue-cdn', function () {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Vue CDN Test</title>
        <meta name="csrf-token" content="' . csrf_token() . '">
        <style>
            body { font-family: Arial; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
            .login-form { max-width: 400px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
            input { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
            .error { color: red; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div id="app">
                <h1>Smart Inventory System</h1>
                <div v-if="currentView === \'login\'">
                    <div class="login-form">
                        <h2>Login</h2>
                        <form @submit.prevent="login">
                            <input v-model="form.email" type="email" placeholder="Email" required>
                            <input v-model="form.password" type="password" placeholder="Password" required>
                            <button type="submit" :disabled="loading">
                                {{ loading ? "Logging in..." : "Login" }}
                            </button>
                        </form>
                        <div v-if="error" class="error">{{ error }}</div>
                    </div>
                </div>
                <div v-else>
                    <h2>Dashboard</h2>
                    <p>Welcome {{ user.name }}!</p>
                    <button @click="logout">Logout</button>
                </div>
            </div>
        </div>

        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
        <script>
            const { createApp } = Vue

            // Set axios defaults
            axios.defaults.baseURL = "/api"
            axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest"
            axios.defaults.headers.common["Accept"] = "application/json"

            const token = document.querySelector("meta[name=csrf-token]")
            if (token) {
                axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content
            }

            createApp({
                data() {
                    return {
                        currentView: "login",
                        form: {
                            email: "admin@example.com",
                            password: "password"
                        },
                        user: null,
                        loading: false,
                        error: null
                    }
                },
                async mounted() {
                    console.log("App mounted successfully!")
                    // Check if user is already logged in
                    const token = localStorage.getItem("token")
                    if (token) {
                        await this.checkAuth()
                    }
                },
                methods: {
                    async login() {
                        this.loading = true
                        this.error = null

                        try {
                            const response = await axios.post("/auth/login", this.form)
                            localStorage.setItem("token", response.data.token)
                            axios.defaults.headers.common["Authorization"] = `Bearer ${response.data.token}`
                            this.user = response.data.user
                            this.currentView = "dashboard"
                        } catch (error) {
                            this.error = error.response?.data?.message || "Login failed"
                        } finally {
                            this.loading = false
                        }
                    },
                    async checkAuth() {
                        try {
                            const token = localStorage.getItem("token")
                            axios.defaults.headers.common["Authorization"] = `Bearer ${token}`
                            const response = await axios.get("/auth/user")
                            this.user = response.data
                            this.currentView = "dashboard"
                        } catch (error) {
                            localStorage.removeItem("token")
                            delete axios.defaults.headers.common["Authorization"]
                        }
                    },
                    logout() {
                        localStorage.removeItem("token")
                        delete axios.defaults.headers.common["Authorization"]
                        this.user = null
                        this.currentView = "login"
                    }
                }
            }).mount("#app")
        </script>
    </body>
    </html>';
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
