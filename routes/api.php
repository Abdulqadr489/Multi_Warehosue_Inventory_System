<?php

use App\Http\Controllers\Countries\CountryController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Suppliers\SupplierController;
use App\Http\Controllers\Warehouses\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::apiResource('countries', CountryController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('suppliers', SupplierController::class);

