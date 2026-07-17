<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NotificationController;

Route::middleware(['auth', 'activeuser'])->group(function () {

    Route::prefix('notification')->group(function () {

        Route::get('/', [NotificationController::class, 'index'])
            ->name('notification.index');

        Route::post('/read-all', [NotificationController::class, 'readAll'])
            ->name('notification.readAll');

        // AJAX: tandakan satu notifikasi sebagai dibaca
        Route::post('/mark-read/{id}', [NotificationController::class, 'markRead'])
            ->name('notification.markRead');

        // Fallback jika JavaScript tidak berfungsi
        Route::get('/read/{id}', [NotificationController::class, 'read'])
            ->name('notification.read');

    });

});