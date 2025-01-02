<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProductController;

use Inertia\Inertia;

Route::get('/', function (Request $request) {
    return inertia('Home');
})->middleware(['verify.shopify'])->name('home');
// Route::inertia('/', 'Home');
Route::resource('events', EventController::class);
Route::resource('products', ProductController::class);
Route::get('/sync-products', [ProductController::class, 'syncProducts']);


