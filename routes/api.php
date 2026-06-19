<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LesenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (Flutter client) — Sanctum token auth
|--------------------------------------------------------------------------
| Entirely separate from the web (Blade/session) routes. All content
| endpoints require an authenticated token AND mirror the web's access
| gating (approved AccessRequest + matching language/level).
*/

// Public auth endpoints.
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Account
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me/attempts', [LesenController::class, 'attempts']);

    // Telc Lesen (MVP: Teil 3)
    Route::get('/lesen/telc', [LesenController::class, 'index']);
    Route::get('/lesen/telc/{slug}', [LesenController::class, 'show']);
    Route::post('/lesen/telc/{slug}/submit', [LesenController::class, 'submit']);
});
