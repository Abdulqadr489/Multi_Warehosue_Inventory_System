<?php

use App\Http\Controllers\Countries\CountryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::apiResource('countries', CountryController::class);

