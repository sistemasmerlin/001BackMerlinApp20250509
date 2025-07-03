<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Noticias;
use Illuminate\Support\Carbon;

class NoticiasController extends Controller
{
    public function index(){

        $hoy = Carbon::now()->toDateString(); // Solo fecha en formato YYYY-MM-DD

        $noticias = Noticias::whereDate('fecha_activacion', '<=', $hoy)->get();

        return response()->json([
            'noticias' => $noticias,
        ]); 
    }
}
