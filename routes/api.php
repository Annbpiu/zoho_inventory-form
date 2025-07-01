<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SyncController;
use \App\Http\Controllers\PurchaseOrderController;

Route::prefix('inventory')->group(function () {
    Route::get('/items', [SyncController::class, 'items']);
    Route::put('/items/{itemId}', [InventoryController::class, 'updateZohoItem']);

    Route::get('/purchase-accounts', [SyncController::class, 'getPurchaseAccounts']);

    Route::get('/contacts', [SyncController::class, 'contacts']);
    Route::post('/create-contact', [InventoryController::class, 'createContact']);

    Route::get('/taxes', [SyncController::class, 'taxes']);

    Route::post('/create-sales-order', [InventoryController::class, 'createSalesOrder']);
    Route::post('/create-purchase-order', [PurchaseOrderController::class, 'createPurchaseOrder']);

    Route::get('/vendors', [PurchaseOrderController::class, 'getVendors']);
    Route::get('/accounts', [PurchaseOrderController::class, 'accounts']);


    Route::post('/sync', [SyncController::class, 'syncProducts']);
});
