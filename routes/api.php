<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\StudentApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. 
| These routes are loaded by the RouteServiceProvider within a group 
| which is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public route: login (accessible without token)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes: logout (requires valid Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->get('/validate-token', [AuthController::class, 'validateToken']);

// Institution
Route::middleware('auth:sanctum')->group(function () {
    // Cascading dropdowns
    Route::get('/countries', [InstitutionController::class, 'getCountries']);
    Route::get('/states/{country_id}', [InstitutionController::class, 'getStates']);
    Route::get('/districts/{state_id}', [InstitutionController::class, 'getDistricts']);
    Route::get('/blocks/{district_id}', [InstitutionController::class, 'getBlocks']);

    Route::get('/institutions', [InstitutionController::class, 'index']);
    Route::post('/institutions', [InstitutionController::class, 'store']);
    Route::put('/institutions/{id}', [InstitutionController::class, 'update']);
    Route::delete('/institutions/{id}', [InstitutionController::class, 'destroy']);
});

// Student
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/students', [StudentApiController::class, 'index']);
    Route::post('/students', [StudentApiController::class, 'store']);
    Route::put('/students/{id}', [StudentApiController::class, 'update']);
    Route::delete('/students/{id}', [StudentApiController::class, 'destroy']);
});
