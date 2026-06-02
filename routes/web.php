<?php

use App\Http\Controllers\Auth\AuthenticateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlucoseRangeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientSummaryController;
use App\Models\GlucoseRange;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticateController::class, 'index'])->name('login');
    Route::post('/login', [AuthenticateController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('index')
        ->can('show-dashboard', User::class);

    Route::get('/summary', [PatientSummaryController::class, 'index'])
        ->name('index')
        ->can('show-summary', User::class);

    Route::prefix('glucose-ranges')->name('glucose-ranges.')
        ->controller(GlucoseRangeController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index')->can('show-glucose-ranges', GlucoseRange::class);
                Route::post('/', 'store')->name('store')->can('create-glucose-ranges', GlucoseRange::class);
                Route::put('/{id}', 'update')->name('update')->can('update-glucose-ranges', GlucoseRange::class);
                Route::delete('/{id}', 'destroy')->name('destroy')->can('delete-glucose-ranges', GlucoseRange::class);
            });

    Route::prefix('patients')->name('patients.')
        ->controller(PatientController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index')->can('show-patients', User::class);
                Route::get('/{document}', 'checkPatient')->name('checkPatient')->can('create-patients', User::class);
                Route::post('/', 'store')->name('store')->can('create-patients', User::class);
                Route::put('/{id}', 'update')->name('update')->can('update-patients', User::class);
            });

    Route::post('logout', [AuthenticateController::class, 'logout'])->name('logout');
});
