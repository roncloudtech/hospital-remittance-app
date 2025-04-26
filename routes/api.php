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
Route::post('/logout', [UserController::class, 'logout']);
//     try {
//         // Revoke current token
//         $request->user()->currentAccessToken()->delete();
        
//         // Or revoke all tokens
//         // $request->user()->tokens()->delete();

//         return response()->json([
//             'success' => true,
//             'message' => 'Successfully logged out'
//         ]);
        
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Logout failed'
//         ], 500);
//     }
// })->middleware('auth:sanctum');


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
    Route::get('/allremittances', [RemittanceController::class, 'allRemittances']);
    Route::patch('/updateremittance/{id}/{action}', [RemittanceController::class, 'updateRemittance']);

    // Hospitals
    Route::get('/hospitals', [HospitalController::class, 'indexForRemitter']);
    Route::get('/gethospitals', [HospitalController::class, 'getHospitals']);
    Route::post('/addhospital', [HospitalController::class, 'addHospital']);
    Route::get('/my-hospitals', [HospitalController::class, 'fetchRemitterHospitals']);
    Route::put('/hospital/update/{id}', [HospitalController::class, 'updateHospital']);


});
