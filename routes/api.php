<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LogisticController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class);

Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])
    ->scopeBindings();

Route::apiResource('categories', CategoryController::class);
Route::apiResource('logistics', LogisticController::class);
