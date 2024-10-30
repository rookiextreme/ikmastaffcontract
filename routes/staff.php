<?php

use App\Http\Controllers\Staff\StaffController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'staff', 'middleware' => ['auth']],function () {
    Route::middleware('activeuser')->group(function () {
        Route::group(['prefix' => 'profile'], function () {
            Route::get('/{user_id}/{page}', [StaffController::class, 'index'])->name('staff.profile');
            Route::post('/store-update-main', [StaffController::class, 'storeUpdateMain']);
            Route::post('/academic-list', [StaffController::class, 'academicList']);
            Route::post('/store-update-academic', [StaffController::class, 'storeUpdateAcademic']);
            Route::post('/get-info-academic', [StaffController::class, 'getAcademicInfo']);
            Route::post('/delete-academic', [StaffController::class, 'deleteAcademic']);
            Route::post('/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');
        });
    });
});
