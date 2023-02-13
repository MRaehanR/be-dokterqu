<?php

use App\Http\Controllers\Articles\ArticleCategoryController;
use App\Http\Controllers\Articles\ArticlePostController;
use App\Http\Controllers\Articles\ArticleCommentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\Shop\CartItemController;
use App\Http\Controllers\Shop\ProductCategoryController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\User\CustomerAddressController;
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
    Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

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

Route::prefix('user')->group(function() {
    Route::prefix('customer')->group(function() {
        Route::post('/address', [CustomerAddressController::class, 'setAddress'])->middleware('auth:sanctum');
        Route::get('/address', [CustomerAddressController::class, 'getAddresses'])->middleware('auth:sanctum');
        Route::post('/address/{id}/update', [CustomerAddressController::class, 'updateAddress'])->middleware('auth:sanctum');
        Route::get('/address/{id}/delete', [CustomerAddressController::class, 'deleteAddress'])->middleware();
    });
});

Route::prefix('form')->group(function() {
    Route::get('/register/customer', [FormController::class, 'registerCustomer']);
});

Route::prefix('shop')->group(function() {
    Route::get('/products', [ProductController::class, 'getAllProducts']);
    Route::get('/product/{slug}', [ProductController::class, 'getProductBySlug']);
    Route::post('/checkout/product', [ProductController::class, 'setCheckoutProduct'])->middleware('auth:sanctum');
    Route::get('/get-available-apotek', [ProductController::class, 'getApotekHasProducts'])->middleware('auth:sanctum');
    Route::post('/get-midtrans-snap-token', [ProductController::class, 'getMidtransSnapToken'])->middleware('auth:sanctum');

    Route::get('/category', [ProductCategoryController::class, 'getAllCategory']);

    Route::get('/carts', [CartItemController::class, 'getCartItem'])->middleware('auth:sanctum');
    Route::post('/cart/add', [CartItemController::class, 'addCartItem'])->middleware('auth:sanctum');
    Route::post('/cart/remove', [CartItemController::class, 'removeCartItem'])->middleware('auth:sanctum');
    Route::post('/cart/update', [CartItemController::class, 'updateCartItem'])->middleware('auth:sanctum');
});

Route::prefix('midtrans')->group(function() {
    Route::post('/notification-handler', [MidtransController::class, 'notificationHandler']);
});
