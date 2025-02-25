<?php

use DashLog\Infrastructure\Http\Controllers\DashLogController;
use DashLog\Infrastructure\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::prefix('dashlog')->name('dashlog.')->group(function () {
        Route::get('/', [DashLogController::class, 'index'])->name('index');
        Route::get('/stats', [DashLogController::class, 'stats'])->name('stats');
        Route::get('/logs', [DashLogController::class, 'logs'])->name('logs');
        Route::get('/logs/{id}', [DashLogController::class, 'show'])->name('logs.show');
        Route::post('/logs/{id}/analyze', [DashLogController::class, 'analyze'])->name('logs.analyze');

        Route::get('/settings', [SettingsController::class, 'show'])->name('settings.show');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
}); 