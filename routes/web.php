<?php

use App\Http\Controllers\FacilityController;
use App\Http\Controllers\AuditLogController;
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
use App\Http\Controllers\PasController;
use App\Http\Controllers\UserController;
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

    Route::middleware(['auth', 'active'])->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/doh', [DashboardController::class, 'dohIndex'])->name('dashboard.doh');
        Route::get('/dashboard/gso', [DashboardController::class, 'gsoIndex'])->name('dashboard.gso');

        Route::resource('items', ItemController::class)->only(['index', 'show']);
        Route::resource('facilities', FacilityController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('items/{item}/{productCode}', [ItemController::class, 'productCodeShow'])->name('items.productcode.show');

        Route::get('items/export', [ItemController::class, 'export'])->name('items.export');
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('doh-dashboard/{supplier}', [SupplierController::class, 'dohDashboard'])->name('doh.dashboard');
        Route::get('gso-dashboard/{supplier}', [SupplierController::class, 'gsoDashboard'])->name('gso.dashboard');

        Route::get('receivings', [ReceivingController::class, 'index'])->name('receivings.index');
        Route::get('receivings/export', [ReceivingController::class, 'export'])->name('receivings.export');
        Route::get('receivings/create', [ReceivingController::class, 'create'])->name('receivings.create');
        Route::get('receivings/{receiving}/edit', [ReceivingController::class, 'edit'])->name('receivings.edit');
        Route::put('receivings/{receiving}', [ReceivingController::class, 'update'])->name('receivings.update');
        Route::get('receivings/{receiving}', [ReceivingController::class, 'view'])->name('receivings.view');
        Route::post('receivings', [ReceivingController::class, 'store'])->name('receivings.store');

        Route::get('releases', [ReleaseController::class, 'index'])->name('releases.index');
        Route::get('releases/create', [ReleaseController::class, 'create'])->name('releases.create');
        Route::get('releases/next-ptr-number/{type}', [ReleaseController::class, 'nextPtrNumber'])->name('releases.next-ptr');
        Route::post('releases', [ReleaseController::class, 'store'])->name('releases.store');
        Route::get('releases/{release}', [ReleaseController::class, 'view'])->name('releases.view');
        Route::get('releases/{release}/print', [ReleaseController::class, 'print'])->name('releases.print');
        Route::put('releases/{release}', [ReleaseController::class, 'update'])->name('releases.update');
        Route::post('releases/{release}/status/{status}', [ReleaseController::class, 'updateStatus'])
            ->where('status', 'released-through-pass|released|canceled|returned|unreleased')
            ->name('releases.status');

        Route::get('reports/liquidation', [ReportController::class, 'liquidation'])->name('reports.liquidation');
        Route::get('reports/liquidation/export', [ReportController::class, 'export'])->name('reports.liquidation.export');

        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

        // Property Allocation Slip (PAS) Routes
        Route::get('pas', [PasController::class, 'index'])->name('pas.index');
        Route::get('pas/create', [PasController::class, 'create'])->name('pas.create');
        Route::post('pas', [PasController::class, 'store'])->name('pas.store');
        Route::get('pas/{pas}/edit', [PasController::class, 'edit'])->name('pas.edit');
        Route::put('pas/{pas}', [PasController::class, 'update'])->name('pas.update');
        Route::get('pas/{pas}', [PasController::class, 'view'])->name('pas.view');
        Route::get('pas/{pas}/print', [PasController::class, 'print'])->name('pas.print');
        Route::post('pas/{pas}/status/{status}', [PasController::class, 'updateStatus'])
            ->where('status', 'Pending|Released|Canceled')
            ->name('pas.status');

        // Program Management Routes — single unified page
        Route::get('program-management', [ProgramManagementController::class, 'index'])->name('program-management.index');
        Route::post('program-management/programs', [ProgramManagementController::class, 'programsStore'])->name('program-management.programs.store');
        Route::put('program-management/programs/{program}', [ProgramManagementController::class, 'programsUpdate'])->name('program-management.programs.update');
        Route::delete('program-management/programs/{program}', [ProgramManagementController::class, 'programsDestroy'])->name('program-management.programs.destroy');
        Route::post('program-management/coordinators', [ProgramManagementController::class, 'coordinatorsStore'])->name('program-management.coordinators.store');
        Route::put('program-management/coordinators/{coordinator}', [ProgramManagementController::class, 'coordinatorsUpdate'])->name('program-management.coordinators.update');
        Route::delete('program-management/coordinators/{coordinator}', [ProgramManagementController::class, 'coordinatorsDestroy'])->name('program-management.coordinators.destroy');

        // User Management (administrators only)
        Route::middleware(['admin'])->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
            Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        });
    });
});
