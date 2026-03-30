<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;
//use App\Http\Controllers\Admin\AdminLeaveController;
use App\Http\Controllers\Admin\StaffStatsController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Reporting\ReportingController;
use App\Http\Controllers\Admin\Branch\AdminBranchController;
use App\Http\Controllers\Admin\Setting\GradeSettingController;
use App\Http\Controllers\Admin\Setting\PositionSettingController;
use App\Http\Controllers\Admin\Setting\AdminPublicHolidayController;
use App\Http\Controllers\Admin\Setting\AdminStateWeekendHolidayController;
use App\Http\Controllers\Admin\Setting\UnitSettingController;
use App\Http\Controllers\Admin\GroupLeaveController;

// ✅ Prestasi
use App\Http\Controllers\Admin\Performance\PerformancePeriodController;
use App\Http\Controllers\Admin\Performance\PerformanceAssignmentController;
use App\Http\Controllers\Admin\Performance\PerformanceEvaluationAdminController;
use App\Http\Controllers\Admin\Performance\PerformanceReportController;
use App\Http\Controllers\Admin\Performance\PerformanceLogController;
use App\Http\Controllers\Admin\Performance\AdminSktController;
use App\Http\Controllers\Admin\Performance\PerformanceDashboardController;

Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {
    Route::middleware('activeuser')->group(function () {

        // ================ Dashboard ================
        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('/', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
        });

        // ================ Statistik Kakitangan ================
        Route::get('/staff-stats', [StaffStatsController::class, 'index'])->name('admin.staff.stats');
        Route::get('/staff-stats/filter', [StaffStatsController::class, 'filterStaff'])->name('admin.staff.stats.filter');

        // ================ Pengguna ================
        Route::group(['prefix' => 'user'], function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('admin.user.list');
            Route::post('/user-list', [AdminUserController::class, 'userList']);
            Route::post('/store-update', [AdminUserController::class, 'storeUpdateUser']);
            Route::post('/get-info', [AdminUserController::class, 'getInfoUser']);
            Route::post('/user-active', [AdminUserController::class, 'userActive']);
        });

        // ================ Penempatan (Cawangan) ================
        Route::group(['prefix' => 'branch'], function () {
            Route::get('/', [AdminBranchController::class, 'index'])->name('admin.branch.index');
            Route::post('/branch-list', [AdminBranchController::class, 'branchList']);
            Route::post('/store-update', [AdminBranchController::class, 'storeUpdate']);
            Route::post('/delete', [AdminBranchController::class, 'deleteBranch']);

            Route::get('/{branch_id}/{page}', [AdminBranchController::class, 'branchDetails'])->name('admin.branch.details');
            Route::post('/position-list', [AdminBranchController::class, 'positionList']);
            Route::post('/position-store-update', [AdminBranchController::class, 'positionStoreUpdate']);
            Route::post('/position-get-info', [AdminBranchController::class, 'positionGetInfo']);
            Route::post('/position-delete', [AdminBranchController::class, 'positionDelete']);
        });

        // ================ Laporan ================
        Route::group(['prefix' => 'reporting'], function () {
            Route::match(['get', 'post'], '/', [ReportingController::class, 'index'])->name('admin.reporting.index');
            Route::get('/pdf', [ReportingController::class, 'pdfDownload'])->name('admin.reporting.pdf');
        });

        // ================ Cuti Kelompok (Admin) ================
        Route::group([
            'prefix' => 'group-leave',
            'middleware' => ['role:super-admin|admin'],
        ], function () {
            Route::get('/', [GroupLeaveController::class, 'index'])->name('admin.group_leave.index');
            Route::get('/create', [GroupLeaveController::class, 'create'])->name('admin.group_leave.create');
            Route::get('/search-staff', [GroupLeaveController::class, 'searchStaff'])->name('admin.group_leave.search_staff');
            Route::post('/store', [GroupLeaveController::class, 'store'])->name('admin.group_leave.store');
        });

        // ================= Dashboard Prestasi =================
        Route::get('/performance/dashboard', [PerformanceDashboardController::class, 'index'])
            ->name('admin.performance.dashboard');

        // ================= Tempoh Penilaian =================
        Route::prefix('performance/periods')->group(function () {
            Route::get('/', [PerformancePeriodController::class, 'index'])
                ->name('admin.performance.periods.index');

            Route::post('/', [PerformancePeriodController::class, 'store'])
                ->name('admin.performance.periods.store');

            Route::put('/{id}', [PerformancePeriodController::class, 'update'])
                ->name('admin.performance.periods.update');

            Route::post('/{id}/activate', [PerformancePeriodController::class, 'activate'])
                ->name('admin.performance.periods.activate');
        });

        // ================= Lantikan PPP / PPK =================
        Route::prefix('performance/assignments')->group(function () {
            Route::get('/', [PerformanceAssignmentController::class, 'index'])
                ->name('admin.performance.assignments.index');

            Route::post('/', [PerformanceAssignmentController::class, 'store'])
                ->name('admin.performance.assignments.store');

            Route::put('/{id}', [PerformanceAssignmentController::class, 'update'])
                ->name('admin.performance.assignments.update');

            Route::delete('/{id}', [PerformanceAssignmentController::class, 'destroy'])
                ->name('admin.performance.assignments.destroy');
        });

        // ================= Senarai Penilaian Prestasi (Admin) =================
        Route::prefix('performance/evaluations')->group(function () {

            Route::get('/', [PerformanceEvaluationAdminController::class, 'index'])
                ->name('admin.performance.evaluations.index');

            Route::get('/{id}', [PerformanceEvaluationAdminController::class, 'show'])
                ->name('admin.performance.evaluations.show');

            Route::post('/{id}/ppsm', [PerformanceEvaluationAdminController::class, 'updatePpsm'])
                ->middleware(['role:super-admin|admin'])
                ->name('admin.performance.evaluations.ppsm');

            Route::post('/{id}/update-pyd', [PerformanceEvaluationAdminController::class, 'updatePydText'])
                ->name('admin.performance.evaluations.updatePydText');

            Route::post('/{id}/reset-ppp-ppk', [PerformanceEvaluationAdminController::class, 'resetPppPpk'])
                ->middleware(['role:super-admin|admin'])
                ->name('admin.performance.evaluations.reset-ppp-ppk');

            Route::post('/{id}/reset-ppk', [PerformanceEvaluationAdminController::class, 'resetPpk'])
                ->middleware(['role:super-admin|admin'])
                ->name('admin.performance.evaluations.reset-ppk');

            Route::post('/{id}/finalize', [PerformanceEvaluationAdminController::class, 'finalize'])
                ->middleware(['role:super-admin|admin'])
                ->name('admin.performance.evaluations.finalize');

            Route::post('/bulk-generate', [PerformanceEvaluationAdminController::class, 'bulkGenerate'])
                ->name('admin.performance.evaluations.bulk-generate');
        });

        // ======================= ADMIN ROUTES (SKT) =======================
        Route::get('/performance/skt', [AdminSktController::class, 'index'])
            ->name('admin.performance.skt.index');

        Route::get('/performance/skt/{evaluation}', [AdminSktController::class, 'show'])
            ->name('admin.performance.skt.show');

        // ================= Log Prestasi (Admin) =================
        Route::prefix('performance/logs')
            ->middleware(['role:super-admin|admin'])
            ->group(function () {
                Route::get('/', [PerformanceLogController::class, 'index'])
                    ->name('admin.performance.logs.index');

                Route::get('/{evaluationId}', [PerformanceLogController::class, 'show'])
                    ->name('admin.performance.logs.show');
            });

        // ================ Tetapan ================
        Route::group(['prefix' => 'setting'], function () {

            // ---- Cuti Umum ----
            Route::group(['prefix' => 'public-holiday'], function () {
                Route::get('/', [AdminPublicHolidayController::class, 'index'])->name('admin.setting.publicholiday.index');
                Route::post('/list', [AdminPublicHolidayController::class, 'list']);
            });

            // ---- Cuti Biasa Mengikut Negeri ----
            Route::group(['prefix' => 'weekend-holiday'], function () {
                Route::get('/', [AdminStateWeekendHolidayController::class, 'index'])->name('admin.setting.weekendholiday.index');
                Route::post('/list', [AdminStateWeekendHolidayController::class, 'list']);
                Route::post('/store-update', [AdminStateWeekendHolidayController::class, 'storeUpdate']);
                Route::post('/get-info', [AdminStateWeekendHolidayController::class, 'getWeekendHoliday']);
                Route::post('/delete', [AdminStateWeekendHolidayController::class, 'deleteWeekendHoliday']);
            });

            // ---- Jawatan ----
            Route::group(['prefix' => 'position'], function () {
                Route::get('/', [PositionSettingController::class, 'index'])->name('admin.setting.position.index');
                Route::post('/list', [PositionSettingController::class, 'list']);
                Route::post('/store-update', [PositionSettingController::class, 'storeUpdate']);
                Route::post('/get-info', [PositionSettingController::class, 'getPosition']);
                Route::post('/delete', [PositionSettingController::class, 'deletePosition']);
            });

            // ---- Gred ----
            Route::group(['prefix' => 'grade'], function () {
                Route::get('/', [GradeSettingController::class, 'index'])->name('admin.setting.grade.index');
                Route::post('/list', [GradeSettingController::class, 'list']);
                Route::post('/store-update', [GradeSettingController::class, 'storeUpdate']);
                Route::post('/get-info', [GradeSettingController::class, 'getGrade']);
                Route::post('/delete', [GradeSettingController::class, 'deleteGrade']);
            });

            // ---- Unit (HQ Sahaja) ----
            Route::group(['prefix' => 'unit'], function () {
                Route::get('/', [UnitSettingController::class, 'index'])->name('admin.setting.unit.index');
                Route::post('/list', [UnitSettingController::class, 'list']);
                Route::post('/store-update', [UnitSettingController::class, 'storeUpdate']);
                Route::post('/get-info', [UnitSettingController::class, 'getUnit']);
                Route::post('/delete', [UnitSettingController::class, 'deleteUnit']);
            });

            // ---- Permohonan Cuti (Admin) ----
            //Route::group(['prefix' => 'leave'], function () {
            //    Route::get('/apply/{staff_id}', [AdminLeaveController::class, 'create'])->name('admin.leave.apply');
            //    Route::post('/apply/store', [AdminLeaveController::class, 'store'])->name('admin.leave.store');
            //    Route::delete('/delete/{leave_id}', [AdminLeaveController::class, 'destroy'])->name('admin.leave.delete');
            //});
        });

    });
});