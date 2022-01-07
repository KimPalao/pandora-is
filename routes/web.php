<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:sanctum', 'verified'])->get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');
Route::middleware(['auth:sanctum', 'verified'])->get('/inventory', function () {
    return Inertia::render('Inventory/Index');
})->name('inventory');
Route::middleware(['auth:sanctum', 'verified'])->get('/inventory/products', function () {
    return Inertia::render('Inventory/Products');
})->name('products');
Route::middleware(['auth:sanctum', 'verified'])->get('/inventory/resources', function () {
    return Inertia::render('Inventory/Resources');
})->name('resources');
Route::middleware(['auth:sanctum', 'verified'])->get('/inventory/bag/{id}', function (int $id) {
    return Inertia::render('Inventory/Bag', ['bag_id' => $id]);
})->name('bag');
Route::middleware(['auth:sanctum', 'verified'])->get('/inventory/audit', function () {
    return Inertia::render('Inventory/Audit');
})->name('audit');
Route::middleware(['auth:sanctum', 'verified'])->get('/reports', function () {
    return Inertia::render('Reports');
})->name('reports');
Route::middleware(['auth:sanctum', 'verified'])->get('/sales', function () {
    return Inertia::render('Sales');
})->name('sales');
Route::middleware(['auth:sanctum', 'verified'])->get('/orders', function () {
    return Inertia::render('Orders');
})->name('orders');
Route::middleware(['auth:sanctum', 'verified'])->get('/query', function () {
    return Inertia::render('Query');
})->name('query');
