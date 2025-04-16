<?php

use App\Http\Controllers\HospitalController;
use App\Http\Controllers\RemittanceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::middleware('auth:sanctum')->group(function () {
// });
// Protected routes here
Route::get('/getusers', [UserController::class, 'getUsers']);
Route::post('/remittances', [RemittanceController::class, 'store']);
Route::get('/hospitals', [HospitalController::class, 'indexForRemitter']);
Route::post('/addhospital', [HospitalController::class, 'addHospital']);
Route::get('/gethospitals', [HospitalController::class, 'getHospitals']);
Route::post('/register',[UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);


// Route::middleware(['auth:sanctum', 'remitter'])->group(function () {
//     Route::post('/remittances', [RemittanceController::class, 'store']);
//     Route::get('/hospitals', [HospitalController::class, 'indexForRemitter']);
// });