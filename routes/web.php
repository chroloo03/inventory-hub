<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\StaffController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Main\InventoryItemController;
use App\Http\Controllers\Main\LowStockController;
use App\Http\Controllers\Main\TransactionController;
use App\Http\Controllers\Main\DashboardController;
use App\Http\Controllers\Main\AlertController;

// ── Guest Routes ───────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login',  [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store']);
});

// ── Authenticated Routes ───────────────────────────────────
Route::middleware('auth')->group(function () {

    // Profile
    Route::get('profile',            [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile/info',     [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::patch('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Root redirect
    Route::get('/', fn() => redirect()->route('inventory.index'));

    // Inventory — specific routes BEFORE resource to avoid conflicts
    Route::get('inventory/low-stock', [LowStockController::class, 'index'])->name('inventory.low-stock');

    Route::get('inventory/{inventory}/transactions',
        [TransactionController::class, 'index'])->name('inventory.transactions.index');
    Route::get('inventory/{inventory}/transactions/create',
        [TransactionController::class, 'create'])->name('inventory.transactions.create');
    Route::post('inventory/{inventory}/transactions',
        [TransactionController::class, 'store'])->name('inventory.transactions.store');

    Route::resource('inventory', InventoryItemController::class);

    // Logout
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

    // ── Admin-only routes ──────────────────────────────────
    Route::middleware('admin')->group(function () {
        Route::resource('staff', StaffController::class)->except(['show']);

        // Dashboard
        Route::get('dashboard',                 [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('dashboard/export',          [DashboardController::class, 'export'])->name('dashboard.export');
        Route::get('dashboard/export/snapshot', [DashboardController::class, 'exportSnapshot'])->name('dashboard.export.snapshot');

        // Alerts
        Route::post('alerts/low-stock', [AlertController::class, 'sendLowStockAlert'])->name('alerts.low-stock');
    });

});
