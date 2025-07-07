<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\RemittanceController;
use App\Http\Controllers\HospitalRemittanceController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [UserController::class, 'login']);
Route::post('/forgot-password', [UserController::class, 'sendResetLink']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);
// Route::post('/remittance', [HospitalRemittanceController::class, 'store']);
/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated via Sanctum)
|--------------------------------------------------------------------------
*/

// Route::get('/onehospital/{id}', [HospitalController::class, 'oneHospital']);

// Route::post('/register', [UserController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {

    // User Management
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/getusers', [UserController::class, 'getUsers']);
    Route::get('/users/{id}', [UserController::class, 'getUser']);
    Route::put('/users/update/{id}', [UserController::class, 'editUser']);

    // Remittance Management
    Route::post('/remittance', [HospitalRemittanceController::class, 'store']);
    Route::post('/remittances', [RemittanceController::class, 'store']);
    Route::get('/admin-hospitals-summary', [HospitalController::class, 'adminHospitalsSummary']);
    Route::get('/remitter-hospitals-summary', [HospitalController::class, 'remitterHospitalsSummary']);
    Route::get('/getremittances', [RemittanceController::class, 'getRemittances']);
    Route::get('/allremittances', [RemittanceController::class, 'allRemittances']);
    Route::patch('/updateremittance/{id}/{action}', [RemittanceController::class, 'updateRemittance']);

    // Hospitals
    Route::get('/hospitals', [HospitalController::class, 'indexForRemitter']);
    Route::get('/gethospitals', [HospitalController::class, 'getHospitals']);
    Route::get('/onehospital/{id}', [HospitalController::class, 'oneHospital']);
    Route::post('/addhospital', [HospitalController::class, 'addHospital']);
    Route::get('/my-hospitals', [HospitalController::class, 'fetchRemitterHospitals']);
    Route::put('/hospital/update/{id}', [HospitalController::class, 'updateHospital']);

    // Tickets
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/admin/tickets', [TicketController::class, 'allTickets']);
    Route::get('/user/tickets', [TicketController::class, 'userTickets']);
    Route::put('/admin/tickets/{id}/status', [TicketController::class, 'updateStatus']);

    // Hospital Remittance
    Route::post('/remittance', [HospitalRemittanceController::class, 'store']); // Submit remittance
    Route::get('/remittance/hospital/{hospital_id}', [HospitalRemittanceController::class, 'getHospitalRemittances']); // Get remittances for a hospital
    Route::get('/hospitals/{hospital_id}/cumulative-status/{year}/{month}', [HospitalController::class, 'getHospitalCumulativeStatus']); // Get cumulative remittance status for a hospital
});


