<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\Setting\AdminPublicHolidayController;
use App\Http\Controllers\Admin\Setting\AdminStateWeekendHolidayController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'middleware' => ['auth']],function () {
    Route::middleware('activeuser')->group(function () {
        Route::group(['prefix' => 'user'], function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('admin.user.list');
            Route::post('/user-list', [AdminUserController::class, 'userList']);
            Route::post('/store-update', [AdminUserController::class, 'storeUpdateUser']);
            Route::post('/get-info', [AdminUserController::class, 'getInfoUser']);
            Route::post('/user-active', [AdminUserController::class, 'userActive']);
        });

        Route::group(['prefix' => 'setting'], function () {
            Route::group(['prefix' => 'public-holiday'], function () {
                Route::get('/', [AdminPublicHolidayController::class, 'index'])->name('admin.setting.publicholiday.index');
                Route::post('/list', [AdminPublicHolidayController::class, 'list']);
            });

            Route::group(['prefix' => 'weekend-holiday'], function () {
                Route::get('/', [AdminStateWeekendHolidayController::class, 'index'])->name('admin.setting.weekendholiday.index');
                Route::post('/list', [AdminStateWeekendHolidayController::class, 'list']);
                Route::post('/store-update', [AdminStateWeekendHolidayController::class, 'storeUpdate']);
                Route::post('/get-info', [AdminStateWeekendHolidayController::class, 'getWeekendHoliday']);
                Route::post('/delete', [AdminStateWeekendHolidayController::class, 'deleteWeekendHoliday']);
            });
        });
    });
});
