<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\AuthController;

// Guest Routes (Only visible if NOT logged in)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store']);
});

// Authenticated Routes (Only visible if logged in)
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/', fn() => redirect()->route('inventory.index'));
    Route::resource('inventory', InventoryItemController::class);
});
