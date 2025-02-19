<?php

use DashLog\Infrastructure\Http\Controllers\DashLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('dashlog')->name('dashlog.')->group(function () {
        Route::get('/', [DashLogController::class, 'index'])->name('index');
        Route::get('/stats', [DashLogController::class, 'stats'])->name('stats');
        Route::get('/logs', [DashLogController::class, 'logs'])->name('logs');
        Route::get('/logs/{id}', [DashLogController::class, 'show'])->name('logs.show');
    });
}); 