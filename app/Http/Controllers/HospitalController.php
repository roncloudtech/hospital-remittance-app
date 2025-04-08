<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HospitalController extends Controller
{
    public function addhospital(Request $request) {
        $validator = Validator::make($request->all(), [
            'hospital_id' => 'required|string|max:10',
            'hospital_name' => 'required|string',
            'hospital_formation' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required|string|unique:hospital,phone_number',
            'hospital_remitter' => 'required',
        ]);
    }
}
