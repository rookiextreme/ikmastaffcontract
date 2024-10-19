<?php

use App\Http\Controllers\Staff\StaffController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'staff', 'middleware' => ['auth']],function () {
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/{user_id}/{page}', [StaffController::class, 'index'])->name('staff.profile');
        Route::post('/store-update-main', [StaffController::class, 'storeUpdateMain']);
    });
});
