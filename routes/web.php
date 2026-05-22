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
use App\Http\Controllers\GoetheB1\LesenController as GoetheB1LesenController;
use App\Http\Controllers\Mundlich\B2PlanningController as MundlichB2PlanningController;

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
        Route::post('/{user}/toggle-admin',   [AdminUserController::class, 'toggleAdmin'])->name('toggleAdmin');
        Route::post('/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('resetPassword');
    });

    // Lesen management
    Route::prefix('lesen')->name('lesen.')->group(function () {
        Route::get('/', [AdminController::class, 'lesenIndex'])->name('index');
        Route::delete('/{topic}', [AdminController::class, 'lesenDestroy'])->name('destroy');
        Route::patch('/{topic}/toggle', [AdminController::class, 'lesenToggle'])->name('toggle');
    });

    // Hören management (rebuilt — modules + codes + exams + statements)
    Route::prefix('hoeren')->name('hoeren.')->group(function () {
        Route::get('/',         [AdminController::class, 'hoerenIndex'])->name('index');
        Route::get('/import',   [AdminController::class, 'hoerenImportShow'])->name('import.show');
        Route::post('/import',  [AdminController::class, 'hoerenImportHandle'])->name('import.handle');
        Route::post('/exams/{exam}/audio',   [AdminController::class, 'hoerenExamAudioUpload'])->name('exam.audio.upload');
        Route::delete('/exams/{exam}/audio', [AdminController::class, 'hoerenExamAudioDelete'])->name('exam.audio.delete');
    });

    // Goethe B1 Lesen management
    Route::prefix('goethe-b1/lesen')->name('goethe-b1.lesen.')->group(function () {
        Route::get('/', [AdminController::class, 'goetheB1LesenIndex'])->name('index');
        Route::get('/import', [AdminController::class, 'goetheB1LesenImportShow'])->name('import.show');
        Route::post('/import', [AdminController::class, 'goetheB1LesenImportHandle'])->name('import.handle');
        Route::post('/import/preview', [AdminController::class, 'goetheB1LesenImportPreview'])->name('import.preview');
        Route::delete('/{topic}', [AdminController::class, 'goetheB1LesenDestroy'])->name('destroy');
        Route::patch('/{topic}/toggle', [AdminController::class, 'goetheB1LesenToggle'])->name('toggle');
    });

    // Telc B2 Mündlich Teil 3 — Gemeinsam etwas planen
    Route::prefix('mundlich/b2-planning')->name('mundlich.b2-planning.')->group(function () {
        Route::get('/', [AdminController::class, 'mundlichB2PlanningIndex'])->name('index');
        Route::get('/import', [AdminController::class, 'mundlichB2PlanningImportShow'])->name('import.show');
        Route::post('/import', [AdminController::class, 'mundlichB2PlanningImportHandle'])->name('import.handle');
        Route::post('/import/preview', [AdminController::class, 'mundlichB2PlanningImportPreview'])->name('import.preview');
        Route::delete('/{topic}', [AdminController::class, 'mundlichB2PlanningDestroy'])->name('destroy');
        Route::patch('/{topic}/toggle', [AdminController::class, 'mundlichB2PlanningToggle'])->name('toggle');
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
    Route::post('/access/new',    [AccessRequestController::class, 'store'])->middleware('throttle:30,1')->name('access.store');
    Route::get('/access/pending', [AccessRequestController::class, 'pending'])->name('access.pending');

    // Auto-welcome polling — JS hits poll every ~20s, markWelcomed once user dismisses.
    Route::get('/access/poll',     [AccessRequestController::class, 'poll'])->middleware('throttle:120,1')->name('access.poll');
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

    // Hören (rebuilt — modules per (level, teil), two sections each)
    Route::prefix('hoeren')->name('hoeren.')->group(function () {
        // Default landing → teil1 imtihanat (the heaviest section, but capped & paginated)
        Route::get('/', [HoerenController::class, 'index'])->name('index');

        // PDF (Richtig-only) — must come before /{teil} to avoid shadowing
        Route::get('/pdf-all',        [HoerenController::class, 'pdfAll'])->name('pdf.all');
        Route::get('/{teil}/pdf',     [HoerenController::class, 'pdf'])
            ->where('teil', 'teil1|teil2|teil3')
            ->name('pdf');

        // Sections
        Route::get('/{teil}/learn',      [HoerenController::class, 'learn'])
            ->where('teil', 'teil1|teil2|teil3')
            ->name('learn');
        Route::get('/{teil}/imtihanat',  [HoerenController::class, 'imtihanat'])
            ->where('teil', 'teil1|teil2|teil3')
            ->name('imtihanat');

        // Single exam page
        Route::get('/{teil}/exam/{exam:slug}', [HoerenController::class, 'exam'])
            ->where('teil', 'teil1|teil2|teil3')
            ->name('exam');
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

    Route::prefix('goethe-b1/lesen')->name('goethe-b1.lesen.')->group(function () {
        Route::get('/', [GoetheB1LesenController::class, 'index'])->name('index');
        Route::get('/{slug}', [GoetheB1LesenController::class, 'topic'])->name('topic');
        Route::post('/{slug}/submit', [GoetheB1LesenController::class, 'submit'])
            ->middleware('throttle:30,1')
            ->name('submit');
    });

    // Telc B2 Mündlich Teil 3 — Gemeinsam etwas planen
    Route::prefix('mundlich/b2-planning')->name('mundlich.b2-planning.')->group(function () {
        Route::get('/',            [MundlichB2PlanningController::class, 'index'])->name('index');
        Route::get('/strukturen',  [MundlichB2PlanningController::class, 'structures'])->name('structures');
        Route::get('/{slug}',      [MundlichB2PlanningController::class, 'topic'])->name('topic');
    });

    Route::get('/plan',     [PlanController::class, 'index'])->name('plan');
    Route::get('/plan/pdf', [PlanController::class, 'pdf'])->name('plan.pdf');

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
// Throttles: per-IP, generous enough for shared networks (school WiFi → many students same NAT IP)
// but still hard to brute-force a min-8-char password.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:30,1']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware(['guest', 'throttle:30,1']);
