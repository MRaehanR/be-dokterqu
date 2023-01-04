<?php

use App\Http\Controllers\Admin\ApotekVerificationCrudController;
use App\Http\Controllers\Admin\DoctorVerificationCrudController;
use App\Http\Controllers\Admin\UserCrudController;
use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');

    Route::crud('doctor-verification', 'DoctorVerificationCrudController');
    Route::prefix('doctor')->group(function(){
        Route::get('get-all-doctors', [UserCrudController::class, 'getAllDoctors']);
        Route::post('update-status', [DoctorVerificationCrudController::class, 'updateStatus']);
    });

    Route::crud('apotek-verification', 'ApotekVerificationCrudController');
    Route::prefix('apotek')->group(function(){
        Route::get('get-all-apotek', [UserCrudController::class, 'getAllApotek']);
        Route::post('update-status', [ApotekVerificationCrudController::class, 'updateStatus']);
    });

    Route::crud('article-category', 'ArticleCategoryCrudController');
    Route::crud('article-post', 'ArticlePostCrudController');
    Route::crud('article-comment', 'ArticleCommentCrudController');
}); // this should be the absolute last line of this file