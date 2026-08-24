<?php

use App\Http\Controllers\Auth\AuthenticateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardSettingsController;
use App\Http\Controllers\GlucoseLogController;
use App\Http\Controllers\GlucoseRangeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientSummaryController;
use App\Http\Controllers\ProfileController;
use App\Models\GlucoseRange;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserGlucoseLog;
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

    Route::get('/patients/{patient}/summary', [DashboardController::class, 'patientSummary'])
        ->name('dashboard.patient-summary')
        ->can('show-dashboard', User::class);

    Route::get('/dashboard/triage-patients', [DashboardController::class, 'triagePatients'])
        ->name('dashboard.triage-patients')
        ->can('show-dashboard', User::class);

    Route::prefix('glucose-ranges')->name('glucose-ranges.')
        ->controller(GlucoseRangeController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index')->can('show-glucose-ranges', GlucoseRange::class);
            Route::post('/', 'store')->name('store')->can('create-glucose-ranges', GlucoseRange::class);
            Route::put('/{id}', 'update')->name('update')->can('update-glucose-ranges', GlucoseRange::class);
            Route::delete('/{id}', 'destroy')->name('destroy')->can('delete-glucose-ranges', GlucoseRange::class);
        });

    Route::prefix('dashboard-settings')->name('dashboard-settings.')
        ->controller(DashboardSettingsController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index')->can('show-dashboard-settings', Setting::class);
            Route::put('/', 'update')->name('update')->can('update-dashboard-settings', Setting::class);
        });

    Route::prefix('patients')->name('patients.')
        ->controller(PatientController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index')->can('show-patients', User::class);
            Route::get('/{document}', 'checkPatient')->name('checkPatient')->can('create-patients', User::class);
            Route::post('/', 'store')->name('store')->can('create-patients', User::class);
            Route::put('/{id}', 'update')->name('update')->can('update-patients', User::class);
            Route::delete('/{patient}', 'destroy')->name('destroy')->can('delete-patients', User::class);
        });

    Route::prefix('glucose-logs')->name('glucose-logs.')
        ->controller(GlucoseLogController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index')->can('show-glucose-logs', UserGlucoseLog::class);
            Route::post('/', 'store')->name('store')->can('create-glucose-logs', UserGlucoseLog::class);
        });

    Route::get('/patients/{patient}/glucose-logs', [GlucoseLogController::class, 'forPatient'])
        ->name('glucose-logs.for-patient')
        ->can('show-glucose-logs', UserGlucoseLog::class);

    Route::prefix('profile')->name('profile.')
        ->controller(ProfileController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/', 'update')->name('update');
            Route::put('/password', 'updatePassword')->name('update-password');
        });

    Route::prefix('notifications')->name('notifications.')
        ->controller(NotificationController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::patch('/read-all', 'markAllAsRead')->name('read-all');
            Route::patch('/{id}/read', 'markAsRead')->name('read');
        });

    Route::post('logout', [AuthenticateController::class, 'logout'])->name('logout');
});
