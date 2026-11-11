<?php

use App\Http\Controllers\Api\AbaPaymentController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerChangePasswordController;
use App\Http\Controllers\Api\CustomerPasswordResetController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogisticController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ResendOtpController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\VerifyOtpController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class)->middleware('throttle:5,1');
Route::post('/login', LoginController::class)->middleware('throttle:5,1');
Route::post('/forgot-password', [CustomerPasswordResetController::class, 'sendLink'])->middleware('throttle:5,1');
Route::post('/reset-password', [CustomerPasswordResetController::class, 'reset'])->middleware('throttle:5,1');
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');
Route::post('/change-password', CustomerChangePasswordController::class)->middleware(['auth:sanctum', 'throttle:5,1']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class);

Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])
    ->scopeBindings();

Route::apiResource('categories', CategoryController::class);
Route::apiResource('logistics', LogisticController::class);
Route::apiResource('settings', SettingsController::class);
Route::post('cart/resolve', CartController::class);
Route::get('addresses', [AddressController::class, 'index'])->middleware('auth:sanctum');
Route::delete('addresses/{address}', [AddressController::class, 'destroy'])->middleware('auth:sanctum');

Route::match(['get', 'post'], 'orders/{order}/payment', [AbaPaymentController::class, 'generatePayment'])
    ->middleware(['auth:sanctum', 'throttle:20,1']);

Route::apiResource('orders', OrderController::class)
    ->middleware('auth:sanctum');

Route::get('orders/{order}/verify', [AbaPaymentController::class, 'verifyPayment'])
    ->middleware(['auth:sanctum', 'throttle:30,1']);

Route::get('banners', [BannerController::class, 'index']);
Route::post('/verify-otp', VerifyOtpController::class)
    ->middleware('throttle:5,1');
Route::post('/resend-otp', ResendOtpController::class)
    ->middleware('throttle:5,1');

Route::get('wishlist', [WishlistController::class, 'index'])->middleware(['auth:sanctum', 'throttle:60,1']);
Route::post('wishlist/operations', [WishlistController::class, 'sync'])->middleware(['auth:sanctum', 'throttle:60,1']);
Route::post('wishlist/resolve', [WishlistController::class, 'resolve'])->middleware('throttle:60,1');
