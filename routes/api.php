<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\RemittanceController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated via Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // User Management
    Route::get('/getusers', [UserController::class, 'getUsers']);

    // Remittance
    Route::post('/remittances', [RemittanceController::class, 'store']);
    Route::get('/getremittances', [RemittanceController::class, 'getRemittances']);
    // Route::get('/getremittances', [RemittanceController::class, 'fetchRemitterRemittances']);
    

    // Hospitals
    Route::get('/hospitals', [HospitalController::class, 'indexForRemitter']);
    Route::get('/gethospitals', [HospitalController::class, 'getHospitals']);
    Route::post('/addhospital', [HospitalController::class, 'addHospital']);
    Route::get('/my-hospitals', [HospitalController::class, 'fetchRemitterHospitals']);
});
