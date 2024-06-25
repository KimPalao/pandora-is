<?php

use App\Http\Controllers\Api\ProductsApiController;
use App\Http\Controllers\Api\ResolveProductsApiController;
use App\Http\Controllers\Api\ResourcesApiController;
use App\Http\Controllers\Api\SalesApiController;
use App\Http\Controllers\Api\OrdersApiController;
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

Route::controller(OrdersApiController::class)->prefix('orders')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'create');
});
Route::controller(SalesApiController::class)->prefix('sales')->group(function () {
    Route::get('/report', 'report');
});
Route::controller(ResolveProductsApiController::class)->prefix('resolve-products')->group(function () {
    Route::get('/', 'index')->name('resolve-products');
});
Route::controller(ProductsApiController::class)->prefix('products')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'create');
    Route::post('/{product}/update-stock/{stock}', 'updateStock');
    Route::get('/most-sold', 'mostSold');
    Route::get('/low-on-stock', 'lowOnStock');
});
Route::controller(ResourcesApiController::class)->prefix('resources')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'create');
    Route::post('/{resource}/update-stock/{stock}', 'updateStock');
    Route::get('/low-on-stock', 'lowOnStock');
    Route::get('/{resource}/image', 'image');
});
