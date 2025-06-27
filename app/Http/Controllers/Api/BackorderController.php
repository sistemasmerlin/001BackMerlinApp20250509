<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backorder;
use App\Models\DetalleBackorder;
use App\Xml\PedidoBackOrder as PedidoBackOrder;

class BackorderController extends Controller
{

    public function index($codigo_asesor)
    {
        $backorders = Backorder::with('pedido.direccionEnvio', 'detalles')
            ->whereHas('pedido', function ($query) use ($codigo_asesor) {
                $query->where('codigo_asesor', $codigo_asesor);
            })
            ->whereHas('detalles', function ($q) {
                $q->where('cantidad', '>', 0);
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'backorders' => $backorders,
        ]);
    }

    public function crearPedido(Request $request){

        foreach ($request->detalles as $detalle) {
            // Convertir a array si viene como objeto JSON
            $detalle = (object) $detalle;
            $nueva_cantidad = 0;

            if (isset($detalle->id) && $detalle->cantidad_enviar > 0) {

                $nueva_cantidad = $detalle->cantidad - $detalle->cantidad_enviar;

                if($nueva_cantidad <= 0){
                    $nueva_cantidad = 0;
                }

                $objDetalle = DetalleBackorder::findOrFail($detalle->id);
                $objDetalle->cantidad_enviar = $detalle->cantidad_enviar;
                $objDetalle->cantidad = $nueva_cantidad;
                $objDetalle->save();
            }
        }

        $backorder = Backorder::with('pedido.direccionEnvio', 'detalles')
            ->where('id','=', $request->id)
            ->first();

            $backorderXml = new PedidoBackOrder();
            $resultadoXml = $backorderXml->generarXml($backorder);

            if ($resultadoXml['status'] !== 'success') {
                return response()->json([
                    'error' => 'Error al generar XML',
                    'mensaje' => $resultadoXml['mensaje'] ?? 'Error no especificado',
                    'resultadoXml' => $resultadoXml
                ], 500);
            }

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Backorder actualizado correctamente',
            'backorder' => $backorder
        ], 200);
    }

}
