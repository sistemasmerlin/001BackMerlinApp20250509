<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Noticias;

class NoticiasController extends Controller
{
    public function index(){

        $noticias = Noticias::get();

        return response()->json([
            'noticias' => $noticias,
        ]);  
    }
}
