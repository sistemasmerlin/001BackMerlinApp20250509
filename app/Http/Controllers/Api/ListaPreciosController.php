<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ListaPreciosController extends Controller
{
    public function descargar(Request $request)
    {
        $datos = $request->validate([
            'lista' => ['required', Rule::in(['001', '202'])],
        ]);

        /*
         * Lista comercial compartida con los usuarios autenticados
         * de la app. No se filtra por el usuario administrador
         * que generó el archivo.
         */
        $exportacion = DB::table('listas_precios_exportaciones')
            ->where('lista', $datos['lista'])
            ->where('imagenes', true)
            ->where('estado', 'listo')
            ->whereNotNull('ruta')
            ->orderByDesc('id')
            ->first();

        if (
            !$exportacion ||
            !Storage::disk('local')->exists($exportacion->ruta)
        ) {
            return response()->json([
                'message' =>
                    'No hay una lista con imágenes disponible. '
                    . 'Primero debe generarse desde el módulo web.',
            ], 404);
        }

        return Storage::disk('local')->download(
            $exportacion->ruta,
            'lista_precios_' . $exportacion->lista
                . '_imagenes_' . $exportacion->id . '.xlsx',
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}