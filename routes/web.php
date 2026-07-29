<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\ProgramManagementController;
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
        Route::get('/dashboard/doh', [DashboardController::class, 'dohIndex'])->name('dashboard.doh');
        Route::get('/dashboard/gso', [DashboardController::class, 'gsoIndex'])->name('dashboard.gso');

        Route::resource('items', ItemController::class)->only(['index', 'show']);

        Route::get('items/{item}/{productCode}', [ItemController::class, 'productCodeShow'])->name('items.productcode.show');

        Route::get('items/export', [ItemController::class, 'export'])->name('items.export');
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('doh-dashboard/{supplier}', [SupplierController::class, 'dohDashboard'])->name('doh.dashboard');
        Route::get('gso-dashboard/{supplier}', [SupplierController::class, 'gsoDashboard'])->name('gso.dashboard');

        Route::get('receivings', [ReceivingController::class, 'index'])->name('receivings.index');
        Route::get('receivings/create', [ReceivingController::class, 'create'])->name('receivings.create');
        Route::get('receivings/{receiving}', [ReceivingController::class, 'view'])->name('receivings.view');
        Route::post('receivings', [ReceivingController::class, 'store'])->name('receivings.store');

        Route::get('releases', [ReleaseController::class, 'index'])->name('releases.index');
        Route::get('releases/create', [ReleaseController::class, 'create'])->name('releases.create');
        Route::get('releases/next-ptr-number/{type}', [ReleaseController::class, 'nextPtrNumber'])->name('releases.next-ptr');
        Route::post('releases', [ReleaseController::class, 'store'])->name('releases.store');
        Route::get('releases/{release}', [ReleaseController::class, 'view'])->name('releases.view');
        Route::put('releases/{release}', [ReleaseController::class, 'update'])->name('releases.update');
        Route::post('releases/{release}/status/{status}', [ReleaseController::class, 'updateStatus'])
            ->where('status', 'released-through-pass|released|canceled|returned|unreleased')
            ->name('releases.status');

        Route::get('reports/liquidation', [ReportController::class, 'liquidation'])->name('reports.liquidation');

        // Program Management Routes
        Route::get('program-management', [ProgramManagementController::class, 'index'])->name('program-management.index');
        Route::get('program-management/programs', [ProgramManagementController::class, 'programsIndex'])->name('program-management.programs.index');
        Route::get('program-management/programs/create', [ProgramManagementController::class, 'programsCreate'])->name('program-management.programs.create');
        Route::post('program-management/programs', [ProgramManagementController::class, 'programsStore'])->name('program-management.programs.store');
        Route::get('program-management/programs/{program}', [ProgramManagementController::class, 'programsShow'])->name('program-management.programs.show');
        Route::get('program-management/programs/{program}/edit', [ProgramManagementController::class, 'programsEdit'])->name('program-management.programs.edit');
        Route::put('program-management/programs/{program}', [ProgramManagementController::class, 'programsUpdate'])->name('program-management.programs.update');
        Route::delete('program-management/programs/{program}', [ProgramManagementController::class, 'programsDestroy'])->name('program-management.programs.destroy');
        
        Route::get('program-management/coordinators', [ProgramManagementController::class, 'coordinatorsIndex'])->name('program-management.coordinators.index');
        Route::get('program-management/coordinators/create', [ProgramManagementController::class, 'coordinatorsCreate'])->name('program-management.coordinators.create');
        Route::post('program-management/coordinators', [ProgramManagementController::class, 'coordinatorsStore'])->name('program-management.coordinators.store');
        Route::get('program-management/coordinators/{coordinator}', [ProgramManagementController::class, 'coordinatorsShow'])->name('program-management.coordinators.show');
        Route::get('program-management/coordinators/{coordinator}/edit', [ProgramManagementController::class, 'coordinatorsEdit'])->name('program-management.coordinators.edit');
        Route::put('program-management/coordinators/{coordinator}', [ProgramManagementController::class, 'coordinatorsUpdate'])->name('program-management.coordinators.update');
        Route::delete('program-management/coordinators/{coordinator}', [ProgramManagementController::class, 'coordinatorsDestroy'])->name('program-management.coordinators.destroy');
    });
});
