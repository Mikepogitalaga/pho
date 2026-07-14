<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])
        ->middleware('guest')
        ->name('login');

    Route::post('login', [LoginController::class, 'login'])
        ->middleware('guest')
        ->name('login.attempt');

    Route::get('register', [RegisterController::class, 'showRegisterForm'])
        ->middleware('guest')
        ->name('register');

    Route::post('register', [RegisterController::class, 'register'])
        ->middleware('guest')
        ->name('register.store');

    Route::post('logout', [LoginController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware(['auth'])->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('items', ItemController::class)->only(['index', 'show']);

    Route::get('items/export', [ItemController::class, 'export'])->name('items.export');
    Route::resource('suppliers', SupplierController::class)->except(['show']);

    Route::get('receivings', [ReceivingController::class, 'index'])->name('receivings.index');
    Route::get('receivings/create', [ReceivingController::class, 'create'])->name('receivings.create');
    Route::post('receivings', [ReceivingController::class, 'store'])->name('receivings.store');

    Route::get('releases', [ReleaseController::class, 'index'])->name('releases.index');
    Route::get('releases/create', [ReleaseController::class, 'create'])->name('releases.create');
    Route::post('releases', [ReleaseController::class, 'store'])->name('releases.store');
    Route::get('releases/{release}', [ReleaseController::class, 'view'])->name('releases.view');
    Route::put('releases/{release}', [ReleaseController::class, 'update'])->name('releases.update');
    Route::post('releases/{release}/status/{status}', [ReleaseController::class, 'updateStatus'])
        ->where('status', 'released-through-pass|released|canceled|returned|unreleased')
        ->name('releases.status');

    Route::get('reports/liquidation', [\App\Http\Controllers\ReportController::class, 'liquidation'])->name('reports.liquidation');
    });
});
