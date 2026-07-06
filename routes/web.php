<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicRedirectController;
use App\Http\Controllers\ShortLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

Route::post('/short-links', [ShortLinkController::class, 'store'])
    ->middleware('auth')
    ->name('short-links.store');

Route::delete('/short-links/{shortLink}', [ShortLinkController::class, 'destroy'])
    ->middleware('auth')
    ->name('short-links.destroy');

require __DIR__.'/auth.php';

Route::get('/{slug}', PublicRedirectController::class)
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('short-links.redirect');
