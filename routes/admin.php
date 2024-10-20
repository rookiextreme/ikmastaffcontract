<?php

use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'middleware' => ['auth']],function () {
    Route::group(['prefix' => 'user'],function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('admin.user.list');
        Route::post('/user-list', [AdminUserController::class, 'userList']);
        Route::post('/store-update', [AdminUserController::class, 'storeUpdateUser']);
        Route::post('/get-info', [AdminUserController::class, 'getInfoUser']);
    });
});
