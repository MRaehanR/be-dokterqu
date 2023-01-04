<?php

use App\Http\Controllers\Articles\ArticleCategoryController;
use App\Http\Controllers\Articles\ArticlePostController;
use App\Http\Controllers\Articles\ArticleCommentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function() {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::prefix('password')->group(function() {
        Route::post('/forgot', [ForgotPasswordController::class, 'forgot']);
        Route::post('/check-reset-code', [ForgotPasswordController::class, 'checkResetCode']);
        Route::post('/reset', [ForgotPasswordController::class, 'reset'])->name('password.reset');
    });

    Route::middleware('auth:sanctum')->prefix('email')->group(function() {
        Route::post('/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail']);
        Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    });
});

Route::prefix('article')->group(function() {
    Route::prefix('post')->group(function() {
        Route::get('/', [ArticlePostController::class, 'getAllArticles']);
        Route::get('/{slug}', [ArticlePostController::class, 'getArticle']);
        Route::post('/{articleId}/like', [ArticlePostController::class, 'setArticleLike'])->middleware('auth:sanctum');
    });

    Route::prefix('comment')->group(function() {
        Route::get('/{articlePostId}', [ArticleCommentController::class, 'getComments']);
        Route::post('/{articlePostId}/reply', [ArticleCommentController::class, 'setComment'])->middleware(['throttle:5,0.2', 'auth:sanctum']);
    });

    Route::prefix('category')->group(function() {
        Route::get('/', [ArticleCategoryController::class, 'getAllCategory']);
    });
});
