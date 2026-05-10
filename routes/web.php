<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserDb\SystemLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// System-Login — kein Link, kein Modal; URL nur dem System-User bekannt
Route::get('/backstage', [SystemLoginController::class, 'login'])->name('system.backstage.login');
Route::post('/backstage', [SystemLoginController::class, 'handleLogin'])->name('system.backstage.handle');
Route::post('/backstage/verify', [SystemLoginController::class, 'verifyTwoFactor'])->name('system.login.verify');

require __DIR__.'/auth.php';
