<?php

use App\Http\Controllers\Api\CourierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. No authentication is required for
| the courier master-data endpoints in this exercise.
|
*/

Route::apiResource('couriers', CourierController::class);
