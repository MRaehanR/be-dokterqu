<?php

use App\Http\Controllers\Articles\ArticleCategoryController;
use App\Http\Controllers\Articles\ArticlePostController;
use App\Http\Controllers\Articles\ArticleCommentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\Homecare\HomecareController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\Shop\CartItemController;
use App\Http\Controllers\Shop\ProductCategoryController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\TerritoryIndonesiaController;
use App\Http\Controllers\User\CustomerAddressController;
use App\Http\Controllers\User\HistoryPurchaseController;
use App\Http\Controllers\User\UserController;
use App\Models\CustomerAddress;
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

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::prefix('password')->group(function () {
        Route::post('/send-reset-code', [ForgotPasswordController::class, 'sendResetCode']);
        Route::post('/check-reset-code', [ForgotPasswordController::class, 'checkResetCode']);
        Route::post('/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');
    });

    Route::middleware('auth:sanctum')->prefix('email')->group(function () {
        Route::post('/send-verification', [EmailVerificationController::class, 'sendVerificationEmail']);
        Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    });
});

Route::prefix('article')->group(function () {
    Route::prefix('post')->group(function () {
        Route::get('/', [ArticlePostController::class, 'getAllArticles']);
        Route::get('/{slug}', [ArticlePostController::class, 'getArticle']);
        Route::post('/{articleId}/like', [ArticlePostController::class, 'setArticleLike'])->middleware('auth:sanctum');
    });

    Route::prefix('comment')->group(function () {
        Route::get('/{articlePostId}', [ArticleCommentController::class, 'getComments']);
        Route::post('/{articlePostId}/reply', [ArticleCommentController::class, 'setComment'])->middleware(['throttle:5,0.2', 'auth:sanctum']);
    });

    Route::prefix('category')->group(function () {
        Route::get('/', [ArticleCategoryController::class, 'getAllCategory']);
    });
});

Route::prefix('user')->group(function () {
    Route::get('/profile', [UserController::class, 'getUserProfile'])->middleware('auth:sanctum');
    Route::post('/profile', [UserController::class, 'updateUserProfile'])->middleware('auth:sanctum');
    Route::prefix('customer')->group(function () {
        Route::post('/address', [CustomerAddressController::class, 'setAddress'])->middleware('auth:sanctum');
        Route::get('/addresses', [CustomerAddressController::class, 'getAddresses'])->middleware('auth:sanctum');
        Route::get('/default/address', [CustomerAddressController::class, 'getDefaultAddress'])->middleware('auth:sanctum');
        Route::get('/address/{id}', [CustomerAddressController::class, 'getAddressById'])->middleware('auth:sanctum');
        Route::post('/address/{id}/update', [CustomerAddressController::class, 'updateAddress'])->middleware('auth:sanctum');
        Route::delete('/address/{id}/delete', [CustomerAddressController::class, 'deleteAddress'])->middleware('auth:sanctum');

        Route::prefix('history')->group(function () {
            Route::get('/shop', [HistoryPurchaseController::class, 'getHistoryShop'])->middleware('auth:sanctum');
            Route::get('/shop/{orderId}', [HistoryPurchaseController::class, 'getDetailHistoryShop'])->middleware('auth:sanctum');
            Route::post('/shop/{orderId}/cancel', [HistoryPurchaseController::class, 'cancelHistoryShop'])->middleware('auth:sanctum');

            Route::get('/homecare', [HistoryPurchaseController::class, 'getHistoryHomecare'])->middleware('auth:sanctum');
            Route::post('/homecare/{orderIT}/cancel', [HistoryPurchaseController::class, 'cancelHistoryHomecare'])->middleware('auth:sanctum');
        });
    });
    Route::prefix('doctor')->group(function () {
        Route::get('/doctor-type', [DoctorController::class, 'getDoctorTypes']);
        Route::get('/{slug}', [DoctorController::class, 'getDoctorBySlug']);
    });
    Route::get('/doctors/operational-times', [DoctorController::class, 'getOperationalTime']);
    Route::get('/doctors', [DoctorController::class, 'getDoctors']);
});

Route::prefix('form')->group(function () {
    Route::get('/register', [FormController::class, 'register']);
    Route::get('/register/doctor', [FormController::class, 'registerDoctor']);
    Route::get('/register/apotek', [FormController::class, 'registerApotek']);
});

Route::prefix('shop')->group(function () {
    Route::get('/products', [ProductController::class, 'getAllProducts']);
    Route::get('/product/{slug}', [ProductController::class, 'getProductBySlug']);
    Route::post('/checkout/product', [ProductController::class, 'setCheckoutProduct'])->middleware('auth:sanctum');
    Route::post('/get-available-apotek', [ProductController::class, 'getApotekHasProducts'])->middleware('auth:sanctum');

    Route::get('/category', [ProductCategoryController::class, 'getAllCategory']);

    Route::get('/carts', [CartItemController::class, 'getCartItem'])->middleware('auth:sanctum');
    Route::post('/cart/add', [CartItemController::class, 'addCartItem'])->middleware('auth:sanctum');
    Route::post('/cart/remove', [CartItemController::class, 'removeCartItem'])->middleware('auth:sanctum');
    Route::post('/cart/update', [CartItemController::class, 'updateCartItem'])->middleware('auth:sanctum');
    Route::delete('/cart/delete', [CartItemController::class, 'deleteCartItem'])->middleware('auth:sanctum');
});

Route::prefix('midtrans')->group(function () {
    Route::post('/notification-handler', [MidtransController::class, 'notificationHandler']);
});

Route::prefix('location')->group(function () {
    Route::get('/provinces', [TerritoryIndonesiaController::class, 'getProvinces']);
    Route::get('/cities', [TerritoryIndonesiaController::class, 'getCities']);
});

Route::prefix('homecare')->group(function () {
    Route::post('/checkout', [HomecareController::class, 'setCheckout'])->middleware('auth:sanctum');
});

Route::prefix('file')->group(function () {
    Route::get('/{path}', [FileController::class, 'getPrivateFile'])->middleware();
});
