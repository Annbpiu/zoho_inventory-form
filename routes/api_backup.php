<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SyncController;

Route::prefix('inventory')->group(function() {
    Route::get('/products', [InventoryController::class, 'products']);
    Route::post('/sync', [SyncController::class, 'syncProducts']);
});

Route::get('/ping', function () {
    return 'pong';
});
