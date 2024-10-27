<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});

Route::match(['GET', 'POST'], '/meta-test', function (Request $request) {
    Log::info('Incoming Request:', $request->all());
//    echo $request->hub_challenge;
    return response()->json(['status' => 'success'], 200);
});

Route::match(['GET', 'POST'], '/meta-test-verify', function (Request $request) {
    Log::info('Incoming Request:', $request->all());
    echo $request->hub_challenge;
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/staff.php';
require __DIR__.'/admin.php';
