<?php

use App\Http\Controllers\Staff\Leave\StaffLeaveController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Staff\Performance\StaffPerformanceController;
use App\Http\Controllers\Staff\Performance\StaffSktController; // ✅ TAMBAH
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
            Route::post('/store-update-appointed', [StaffController::class, 'storeUpdateAppointed']);

            Route::get('/get-branch-by-state', [StaffController::class, 'getBranchByState']);
            Route::get('/get-position-by-branch', [StaffController::class, 'getPositionByBranch']);
            Route::post('/store-update-position', [StaffController::class, 'storeUpdatePosition']);
            Route::post('/store-update-new-leave-balance', [StaffController::class, 'storeUpdateNewLeaveBalance']);

            Route::post('/family-list', [StaffController::class, 'familyList']);
            Route::post('/store-update-family', [StaffController::class, 'storeUpdateFamily']);
            Route::post('/get-info-family', [StaffController::class, 'getFamilyInfo']);
            Route::post('/delete-family', [StaffController::class, 'deleteFamily']);

            Route::post('/harta-list', [StaffController::class, 'hartaList']);
            Route::post('/store-update-harta', [StaffController::class, 'storeUpdateHarta']);
            Route::post('/get-info-harta', [StaffController::class, 'getHartaInfo']);
            Route::post('/delete-harta', [StaffController::class, 'deleteHarta']);

            Route::post('/store-update-work-status', [StaffController::class, 'storeUpdateWorkStatus']);
            Route::post('/get-info-position-history', [StaffController::class, 'getPositionHistoryInfo']);
            Route::post('/store-update-position-history-date', [StaffController::class, 'storeUpdatePositionHistoryDate']);
            Route::post('/set-position-as-active', [StaffController::class, 'setPositionAsActive']);
        });

        Route::group(['prefix' => 'leave'], function () {
            Route::get('/request/{user_id}', [StaffLeaveController::class, 'leaveRequest'])->name('staff.leave.request');
            Route::post('/request-list', [StaffLeaveController::class, 'requestList']);
            Route::post('/request-delete', [StaffLeaveController::class, 'requestDelete']);
            Route::get('/new-request/{user_id}', [StaffLeaveController::class, 'leaveNewRequest'])->name('staff.leave.new-request');
            Route::post('/store-update-new-request', [StaffLeaveController::class, 'storeUpdateNewRequest']);
            Route::post('/request-approval', [StaffLeaveController::class, 'requestApproval']);
            Route::get('/get-approver', [StaffLeaveController::class, 'getApprover']);
            Route::get('/approval/{user_id}', [StaffLeaveController::class, 'leaveApproval'])->name('staff.leave.approval');
            Route::post('/approval-list', [StaffLeaveController::class, 'approvalList']);
            Route::get('/get-approver-options', [StaffLeaveController::class, 'approverOptions']);
            Route::post('/update-approver', [StaffLeaveController::class, 'updateApprover']);
            Route::post('/leave-request-change-category', [StaffLeaveController::class, 'leaveRequestChangeCategory']);
            Route::post('/admin-adjust-auto-approve', [StaffLeaveController::class, 'adminAdjustAndAutoApprove']);
        });

        // Penilaian Prestasi (LNPT - PYD)
        Route::get('/performance', [StaffPerformanceController::class, 'index'])
            ->name('staff.performance.index');

        Route::post('/performance/save', [StaffPerformanceController::class, 'saveDraft'])
            ->name('staff.performance.save');

        Route::post('/performance/submit', [StaffPerformanceController::class, 'submit'])
            ->name('staff.performance.submit');

        // ✅ SKT (PYD) — klik menu terus buka borang (Bahagian I default)
        Route::get('/performance/skt', [StaffSktController::class, 'show'])
            ->name('staff.performance.skt');

        Route::post('/performance/skt/save', [StaffSktController::class, 'saveDraft'])
            ->name('staff.performance.skt.save');

        Route::post('/performance/skt/submit', [StaffSktController::class, 'submit'])
            ->name('staff.performance.skt.submit');
    });
});