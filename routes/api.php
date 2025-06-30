<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SyncController;
use \App\Http\Controllers\PurchaseOrderController;

Route::prefix('inventory')->group(function () {
    Route::get('/items', [InventoryController::class, 'items']);
    Route::put('/items/{itemId}', [InventoryController::class, 'updateZohoItem']);

    Route::get('/purchase-accounts', [SyncController::class, 'getPurchaseAccounts']);

    Route::get('/contacts', [InventoryController::class, 'contacts']);
    Route::post('/create-contact', [InventoryController::class, 'createContact']);

    Route::get('/taxes', [InventoryController::class, 'taxes']);

    Route::get('/paymentTerms', [InventoryController::class, 'paymentTerms']);

    Route::post('/create-sales-order', [InventoryController::class, 'createSalesOrder']);
    Route::post('/create-purchase-order', [PurchaseOrderController::class, 'createPurchaseOrder']);

    Route::get('/vendors', [PurchaseOrderController::class, 'getVendors']);

    Route::post('/sync', [SyncController::class, 'syncProducts']);
});
