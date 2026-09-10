<?php

use App\Http\Controllers\Api\AbaPaymentController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogisticController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class)->middleware('throttle:5,1');
Route::post('/login', LoginController::class)->middleware('throttle:5,1');
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class);

Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])
    ->scopeBindings();

Route::apiResource('categories', CategoryController::class);
Route::apiResource('logistics', LogisticController::class);
Route::apiResource('settings', SettingsController::class);

Route::match(['get', 'post'], 'orders/{order}/payment', [AbaPaymentController::class, 'generatePayment'])
    ->middleware(['auth:sanctum', 'throttle:20,1']);

Route::apiResource('orders', OrderController::class)
    ->middleware('auth:sanctum');

Route::get('orders/{order}/verify', [AbaPaymentController::class, 'verifyPayment'])
    ->middleware(['auth:sanctum', 'throttle:30,1']);
