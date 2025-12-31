<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware('auth.shorturl')->group(function () {
        Route::get('/s/{code}', [ShortUrlController::class, 'redirect'])->name('short-url.redirect');
    });

    Route::resource('short-urls', ShortUrlController::class)->only(['index', 'store', 'edit', 'update']);
    Route::resource('invitations', InvitationController::class)->only(['create', 'store']);
});
