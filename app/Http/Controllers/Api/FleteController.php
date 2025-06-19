<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FleteCiudad;

class FleteController extends Controller
{
    public function index(){

        $fletes = FleteCiudad::where('estado','=','1')->get();

        return response()->json([
            'fletes' => $fletes,
        ]);  
    }
}
