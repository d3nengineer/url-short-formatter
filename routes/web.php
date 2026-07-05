<?php

use App\Http\Controllers\ShortLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Route::post('/short-links', [ShortLinkController::class, 'store'])
    ->middleware('auth')
    ->name('short-links.store');

require __DIR__.'/auth.php';
