<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Countries\CountryController;
use App\Http\Controllers\Inventories\InventoryTransactionController;
use App\Http\Controllers\Inventories\InventoryTransferController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Suppliers\SupplierController;
use App\Http\Controllers\Warehouses\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('countries', CountryController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('suppliers', SupplierController::class);

Route::middleware('auth:api')->group(function () {

    Route::apiResource('inventory_transactions', InventoryTransactionController::class)->only(['index', 'show','store']);
    Route::apiResource('inventory_transfer', InventoryTransferController::class)->only(['index', 'show','store']);
});

