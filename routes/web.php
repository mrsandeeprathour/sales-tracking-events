<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\EventController;
use Inertia\Inertia;

Route::get('/', function (Request $request) {
    return inertia('Home');
})->middleware(['verify.shopify'])->name('home');
// Route::inertia('/', 'Home');
Route::resource('events', EventController::class);
