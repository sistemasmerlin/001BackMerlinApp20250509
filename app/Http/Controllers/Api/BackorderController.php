<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backorder;

class BackorderController extends Controller
{

    public function index($codigo_asesor)
    {
        $backorders = Backorder::with('pedido.direccionEnvio', 'detalles')
            ->whereHas('pedido', function ($query) use ($codigo_asesor) {
                $query->where('codigo_asesor', $codigo_asesor);
            })
            ->get();

        return response()->json([
            'backorders' => $backorders,
        ]);
    }

    public function crearPedido(Request $request){


        return $request->detalles;

        foreach($request->detalles as $detalles){

            

        }
    }

    // public function detalleBackOrder($id)
    // {
    //     $backorder = Backorder::with('pedido', 'detalles')
    //         ->where('id', '=', $id)
    //         ->first();

    //     if (!$backorder) {
    //         return response()->json([
    //             'error' => 'Backorder no encontrado',
    //         ], 404);
    //     }

    //     return response()->json([
    //         'detalles' => $backorder->detalles,
    //         'pedido' => $backorder->pedido,
    //     ]);
    // }
}
