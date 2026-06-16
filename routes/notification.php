<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NotificationController;

Route::middleware(['auth', 'activeuser'])->group(function () {

    Route::prefix('notification')->group(function () {

        Route::get('/', [NotificationController::class, 'index'])
            ->name('notification.index');

        Route::get('/read/{id}', [NotificationController::class, 'read'])
            ->name('notification.read');

    });

});