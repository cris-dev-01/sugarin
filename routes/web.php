<?php

use App\Http\Controllers\Auth\AuthenticateController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticateController::class, 'index'])->name('login');
    Route::post('/login', [AuthenticateController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    // dd(auth()->user());
    Route::get('/', function () {
        return Inertia::render('Index');
    });
});
