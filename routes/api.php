<?php

use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PatientCatalogController;
use App\Http\Controllers\Api\PosController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:30,1')->group(function () {
    Route::get('catalog', [PatientCatalogController::class, 'index']);
});

Route::middleware(['web', 'throttle:10,1'])->prefix('pos')->group(function () {
    Route::post('login', [PosController::class, 'login']);
});

Route::middleware(['web', 'auth:admin', 'admin_role:admin,cashier'])
    ->prefix('pos')
    ->group(function () {
        Route::get('me', [PosController::class, 'me']);
        Route::post('logout', [PosController::class, 'logout']);
        Route::get('catalog', [PosController::class, 'catalog']);
        Route::get('patients', [PosController::class, 'patients']);
        Route::get('promotions', [PosController::class, 'promotions']);
        Route::post('affiliate-codes/validate', [PosController::class, 'validateAffiliateCode']);
        Route::post('checkout', [PosController::class, 'checkout']);
    });

Route::middleware(['web', 'throttle:10,1'])->prefix('inventory')->group(function () {
    Route::post('login', [InventoryController::class, 'login']);
});

Route::middleware(['web', 'auth:admin', 'admin_role:inventory_officer'])
    ->prefix('inventory')
    ->group(function () {
        Route::get('me', [InventoryController::class, 'me']);
        Route::post('logout', [InventoryController::class, 'logout']);
        Route::get('summary', [InventoryController::class, 'summary']);
        Route::get('products', [InventoryController::class, 'products']);
        Route::get('products/{id}', [InventoryController::class, 'showProduct'])->whereNumber('id');
        Route::get('low-stock', [InventoryController::class, 'lowStock']);
        Route::get('movements', [InventoryController::class, 'movements']);
        Route::post('stock-movements', [InventoryController::class, 'storeMovement']);
    });
