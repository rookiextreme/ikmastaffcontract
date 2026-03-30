<?php

use App\Http\Controllers\ProfileController;
use App\Models\PublicHoliday;
use App\Models\State;
use Holiday\MalaysiaHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ✅ TAMBAH (PPP Prestasi)
use App\Http\Controllers\PPP\Performance\PPPPerformanceController;
use App\Http\Controllers\PPK\Performance\PPKPerformanceController;

// ✅ TAMBAH (PPP SKT)
use App\Http\Controllers\PPP\Performance\PPPSktController;

Route::get('user-inactive', function(){
    return view('user-inactive');
})->name('user-inactive');

Route::match(['GET', 'POST'], '/meta-test', function (Request $request) {
    Log::info('Incoming Request:', $request->all());
    return response()->json(['status' => 'success'], 200);
});

Route::match(['GET'], '/sql-to-excel', [ProfileController::class, 'sqlToExcel']);

Route::match(['GET', 'POST'], '/meta-test-verify', function (Request $request) {
    Log::info('Incoming Request:', $request->all());
    if($request->hub_challenge){
        echo $request->hub_challenge;
    }
    return response()->json(['status' => 'success'], 200);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group(['prefix' => 'ppp', 'middleware' => ['auth']], function () {

    Route::middleware('activeuser')->group(function () {

        // ===== LNPT / PRESTASI =====
        Route::get('/performance', [PPPPerformanceController::class, 'index'])
            ->name('ppp.performance.index');

        Route::get('/performance/{evaluationId}', [PPPPerformanceController::class, 'show'])
            ->whereNumber('evaluationId') // ✅ TAMBAH (elak /performance/skt kena route ini)
            ->name('ppp.performance.show');

        Route::post('/performance/{evaluationId}/save', [PPPPerformanceController::class, 'save'])
            ->whereNumber('evaluationId') // ✅ TAMBAH
            ->name('ppp.performance.save');

        Route::post('/performance/{evaluationId}/submit', [PPPPerformanceController::class, 'submit'])
            ->whereNumber('evaluationId') // ✅ TAMBAH
            ->name('ppp.performance.submit');


        // ✅ PPP ROUTES (SKT)
        Route::get('/performance/skt', [PPPSktController::class, 'index'])
            ->name('ppp.performance.skt.index');

        // penting: param mesti {evaluation} untuk implicit binding
        Route::get('/performance/skt/{evaluation}', [PPPSktController::class, 'show'])
            ->name('ppp.performance.skt.show');

        Route::post('/performance/skt/{evaluation}/save', [PPPSktController::class, 'save'])
            ->name('ppp.performance.skt.save');

        Route::post('/performance/skt/{evaluation}/submit', [PPPSktController::class, 'submit'])
            ->name('ppp.performance.skt.submit');

    });

});


// =======================
// PPK ROUTES (Prestasi)
// =======================
Route::group(['prefix' => 'ppk', 'middleware' => ['auth']], function () {

    Route::middleware('activeuser')->group(function () {

        Route::get('/performance', [PPKPerformanceController::class, 'index'])
            ->name('ppk.performance.index');

        Route::get('/performance/{evaluationId}', [PPKPerformanceController::class, 'show'])
            ->name('ppk.performance.show');

        // ✅ tambah save (PPK isi markah/komen)
        Route::post('/performance/{evaluationId}/save', [PPKPerformanceController::class, 'save'])
            ->name('ppk.performance.save');

        // ✅ approve / finalize (PPK sahkan)
        Route::post('/performance/{evaluationId}/approve', [PPKPerformanceController::class, 'approve'])
            ->name('ppk.performance.approve');

    });

});


require __DIR__.'/auth.php';
require __DIR__.'/staff.php';
require __DIR__.'/admin.php';
require __DIR__.'/approval-admin.php';