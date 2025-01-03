<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProductController;

use Inertia\Inertia;

Route::get('/', [EventController::class, 'index'])->middleware(['verify.shopify'])->name('home');

Route::resource('events', EventController::class);
Route::resource('products', ProductController::class);
Route::get('/sync-products', [ProductController::class, 'syncProducts']);

Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});


