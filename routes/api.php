<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\RemittanceController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated via Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Registration Management
    Route::post('/register', [UserController::class, 'register']);

    // User Management
    Route::get('/getusers', [UserController::class, 'getUsers']);

    // Remittance
    Route::post('/remittances', [RemittanceController::class, 'store']);
    Route::get('/getremittances', [RemittanceController::class, 'getRemittances']);
    Route::get('/allremittances', [RemittanceController::class, 'allRemittances']);
    Route::patch('/updateremittance/{id}/{action}', [RemittanceController::class, 'updateRemittance']);

    // Hospitals
    Route::get('/hospitals', [HospitalController::class, 'indexForRemitter']);
    Route::get('/gethospitals', [HospitalController::class, 'getHospitals']);
    Route::post('/addhospital', [HospitalController::class, 'addHospital']);
    Route::get('/my-hospitals', [HospitalController::class, 'fetchRemitterHospitals']);
    Route::put('/hospital/update/{id}', [HospitalController::class, 'updateHospital']);

    // Tickets
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/admin/tickets', [TicketController::class, 'allTickets']);
    Route::get('/user/tickets', [TicketController::class, 'userTickets']);


});


