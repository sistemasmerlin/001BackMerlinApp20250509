<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductoEmpaque;
use Illuminate\Http\JsonResponse;

class ProductosEmpaquesController extends Controller
{
    public function index(): JsonResponse
    {
        $productos = ProductoEmpaque::query()
            ->orderBy('referencia')
            ->get([
                'referencia',
                'descripcion',
                'unidades_por_empaque',
                'updated_at',
            ]);

        return response()->json([
            'success' => true,
            'total' => $productos->count(),
            'datos' => $productos,
        ]);
    }
}