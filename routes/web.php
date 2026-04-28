<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Main\InventoryItemController;
use App\Http\Controllers\Main\LowStockController;

// Guest Routes (Only visible if NOT logged in)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthController::class, 'store']);
});

// Authenticated Routes (Only visible if logged in)
Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('profile/info', [ProfileController::class, 'updateInfo'])
        ->name('profile.update-info');
    Route::patch('profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.update-password');

    Route::post('logout', [AuthController::class, 'destroy'])
        ->name('logout');

    Route::get('/', fn() => redirect()->route('inventory.index'));

    Route::get('inventory/low-stock', [LowStockController::class, 'index'])
        ->name('inventory.low-stock');

    Route::resource('inventory', InventoryItemController::class);
});
