<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LesenController;
use App\Http\Controllers\HoerenController;
use App\Http\Controllers\SchreibenController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\AccessRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TopicImportController;
use App\Http\Controllers\Admin\AccessRequestController as AdminAccessRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Admin ──────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Access requests management
    Route::prefix('access')->name('access.')->group(function () {
        Route::get('/', [AdminAccessRequestController::class, 'index'])->name('index');
        Route::post('/{accessRequest}/approve', [AdminAccessRequestController::class, 'approve'])->name('approve');
        Route::post('/{accessRequest}/deny',    [AdminAccessRequestController::class, 'deny'])->name('deny');
    });

    // Users management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::post('/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('toggleAdmin');
    });

    // Lesen management
    Route::prefix('lesen')->name('lesen.')->group(function () {
        Route::get('/', [AdminController::class, 'lesenIndex'])->name('index');
        Route::delete('/{topic}', [AdminController::class, 'lesenDestroy'])->name('destroy');
        Route::patch('/{topic}/toggle', [AdminController::class, 'lesenToggle'])->name('toggle');
    });

    // Hören management
    Route::prefix('hoeren')->name('hoeren.')->group(function () {
        Route::get('/', [AdminController::class, 'hoerenIndex'])->name('index');
        Route::delete('/{topic}', [AdminController::class, 'hoerenDestroy'])->name('destroy');
    });

    // Import (lesen or hoeren)
    Route::get('/import/{type}', [TopicImportController::class, 'showImport'])->name('import.show');
    Route::post('/import/{type}', [TopicImportController::class, 'handleImport'])->name('import.handle');
    Route::post('/import/preview-json', [TopicImportController::class, 'previewJson'])->name('import.preview');
});

// ── Authenticated user routes ─────────────────────────────────
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');

    // Access requests (user-facing)
    Route::get('/access/new',     [AccessRequestController::class, 'create'])->name('access.create');
    Route::post('/access/new',    [AccessRequestController::class, 'store'])->middleware('throttle:5,1')->name('access.store');
    Route::get('/access/pending', [AccessRequestController::class, 'pending'])->name('access.pending');

    // Auto-welcome polling — JS hits poll every ~20s, markWelcomed once user dismisses.
    Route::get('/access/poll',     [AccessRequestController::class, 'poll'])->middleware('throttle:60,1')->name('access.poll');
    Route::post('/access/welcomed', [AccessRequestController::class, 'markWelcomed'])->name('access.welcomed');
});

// ── Content (auth + approved access required) ────────────────
Route::middleware(['auth', 'has.access'])->group(function () {

    Route::prefix('lesen')->name('lesen.')->group(function () {
        Route::get('/', [LesenController::class, 'index'])->name('index');
        Route::get('/{slug}/{teil}/pdf', [LesenController::class, 'pdf'])
            ->where('teil', 'teil1|teil2|teil3|sprachbausteine1|sprachbausteine2')
            ->name('pdf');
        Route::get('/{slug}', [LesenController::class, 'topic'])->name('topic');
        Route::post('/{slug}/submit', [LesenController::class, 'submit'])
            ->middleware('throttle:30,1')
            ->name('submit');
        Route::get('/{slug}/result', [LesenController::class, 'result'])->name('result');
    });

    Route::prefix('hoeren')->name('hoeren.')->group(function () {
        Route::get('/', [HoerenController::class, 'index'])->name('index');
        Route::get('/{slug}', [HoerenController::class, 'topic'])->name('topic');
        Route::post('/{slug}/submit', [HoerenController::class, 'submit'])
            ->middleware('throttle:30,1')
            ->name('submit');
    });

    Route::prefix('schreiben')->name('schreiben.')->group(function () {
        Route::get('/', [SchreibenController::class, 'index'])->name('index');
        Route::get('/{slug}/pdf', [SchreibenController::class, 'pdf'])->name('pdf');
        Route::get('/{slug}', [SchreibenController::class, 'topic'])->name('topic');
        Route::post('/grade', [SchreibenController::class, 'grade'])
            ->middleware('throttle:10,1')
            ->name('grade');
        Route::post('/generate', [SchreibenController::class, 'generate'])
            ->middleware('throttle:8,1')
            ->name('generate');
    });

    Route::get('/plan', [PlanController::class, 'index'])->name('plan');

    Route::prefix('simulation')->name('simulation.')->group(function () {
        Route::get('/', [SimulationController::class, 'index'])->name('index');
        Route::post('/start', [SimulationController::class, 'start'])
            ->middleware('throttle:20,1')
            ->name('start');
    });
});

// ── Public ────────────────────────────────────────────────────
Route::get('/billing', fn () => view('billing'))->name('billing');

// ── Auth ──────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware(['guest', 'throttle:5,1']);
