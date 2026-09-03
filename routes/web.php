<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/shop-products', function () {
    return Http::get(config('services.store_api.url').'/api/products')
        ->throw()
        ->json();
});
