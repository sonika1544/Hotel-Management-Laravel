<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Facility Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Public routes (no role required)
    Route::get('/facilities', [App\Http\Controllers\Api\FacilityController::class, 'index']);
    Route::get('/facilities/category/{category}', [App\Http\Controllers\Api\FacilityController::class, 'getByCategory']);
    Route::get('/facilities/{id}', [App\Http\Controllers\Api\FacilityController::class, 'show']);

    // Protected routes (Admin and Manager only)
    Route::middleware(['api.role:Admin,Manager'])->group(function () {
        Route::post('/facilities', [App\Http\Controllers\Api\FacilityController::class, 'store']);
        Route::put('/facilities/{id}', [App\Http\Controllers\Api\FacilityController::class, 'update']);
        Route::delete('/facilities/{id}', [App\Http\Controllers\Api\FacilityController::class, 'destroy']);
    });
});
