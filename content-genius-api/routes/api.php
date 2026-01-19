<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentHistoryController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RewriteController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| ContentGenius API - SaaS Content Generation Platform
|
*/

// API Version prefix
Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    });

    // Public endpoint for supported languages
    Route::get('/languages', [TranslationController::class, 'languages'])->name('languages');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Authentication Required)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Authentication
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        });

        // Profile & Credits
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
            Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
            Route::get('/credits', [ProfileController::class, 'credits'])->name('profile.credits');
        });

        /*
        |--------------------------------------------------------------------------
        | Content Generation Routes (ChatGPT Features)
        |--------------------------------------------------------------------------
        */

        // Articles (ChatGPT)
        Route::prefix('articles')->group(function () {
            Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
            Route::post('/', [ArticleController::class, 'store'])->name('articles.store');
            Route::get('/{uuid}', [ArticleController::class, 'show'])->name('articles.show');
        });

        // Rewrite (ChatGPT)
        Route::prefix('rewrites')->group(function () {
            Route::get('/', [RewriteController::class, 'index'])->name('rewrites.index');
            Route::post('/', [RewriteController::class, 'store'])->name('rewrites.store');
            Route::get('/{uuid}', [RewriteController::class, 'show'])->name('rewrites.show');
        });

        // Summaries (ChatGPT)
        Route::prefix('summaries')->group(function () {
            Route::get('/', [SummaryController::class, 'index'])->name('summaries.index');
            Route::post('/', [SummaryController::class, 'store'])->name('summaries.store');
            Route::get('/{uuid}', [SummaryController::class, 'show'])->name('summaries.show');
        });

        // Emails (ChatGPT)
        Route::prefix('emails')->group(function () {
            Route::get('/', [EmailController::class, 'index'])->name('emails.index');
            Route::post('/', [EmailController::class, 'store'])->name('emails.store');
            Route::get('/{uuid}', [EmailController::class, 'show'])->name('emails.show');
        });

        // Translations (ChatGPT)
        Route::prefix('translations')->group(function () {
            Route::get('/', [TranslationController::class, 'index'])->name('translations.index');
            Route::post('/', [TranslationController::class, 'store'])->name('translations.store');
            Route::get('/{uuid}', [TranslationController::class, 'show'])->name('translations.show');
        });

        /*
        |--------------------------------------------------------------------------
        | Content History & Statistics
        |--------------------------------------------------------------------------
        */
        Route::prefix('history')->group(function () {
            Route::get('/', [ContentHistoryController::class, 'index'])->name('history.index');
            Route::get('/stats', [ContentHistoryController::class, 'stats'])->name('history.stats');
            Route::get('/{uuid}', [ContentHistoryController::class, 'show'])->name('history.show');
        });
    });
});

/*
|--------------------------------------------------------------------------
| API Health Check
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'ContentGenius API',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('health');
