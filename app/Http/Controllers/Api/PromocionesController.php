<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promocion;

class PromocionesController extends Controller
{
    public function index(){

        $promociones = Promocion::where('estado','=','1')->with('detalles','relaciones')->get();   

        return response()->json([
            'promociones' => $promociones,
        ]); 
    }
}
