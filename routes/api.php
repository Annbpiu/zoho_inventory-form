<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SyncController;

Route::prefix('inventory')->group(function () {
    Route::get('/items', [InventoryController::class, 'items']);
    Route::get('/contacts', [InventoryController::class, 'contacts']);
    Route::get('/taxes', [InventoryController::class, 'taxes']);
    Route::get('/paymentTerms', [InventoryController::class, 'paymentTerms']);
    Route::post('/create-sales-order', [InventoryController::class, 'createSalesOrder']);
    Route::post('/create-contact', [InventoryController::class, 'createContact']);
    Route::post('/sync', [SyncController::class, 'syncProducts']);
});
