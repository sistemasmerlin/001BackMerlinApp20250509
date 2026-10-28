<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use Illuminate\Support\Facades\DB;

class PromocionesController extends Controller
{
    public function index()
    {
        $promociones = Promocion::query()
            ->where('estado', '1')
            ->with('detalles', 'relaciones')
            ->get();

        $subcategorias = DB::table('listas_precios_subcategorias')
            ->where('activa', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $ids = $subcategorias->pluck('id');

        $marcasPorSubcategoria = DB::table(
            'listas_precios_subcategoria_marcas'
        )
            ->whereIn('subcategoria_id', $ids)
            ->orderBy('marca_excel')
            ->get()
            ->groupBy('subcategoria_id');

        $rangosPorSubcategoria = DB::table(
            'listas_precios_subcategoria_rangos'
        )
            ->whereIn('subcategoria_id', $ids)
            ->orderBy('desde')
            ->get()
            ->groupBy('subcategoria_id');

        $configuracion = $subcategorias
            ->map(function ($subcategoria) use (
                $marcasPorSubcategoria,
                $rangosPorSubcategoria
            ) {
                return [
                    'id' => (int) $subcategoria->id,

                    'nombre' => (string) $subcategoria->nombre,

                    'tipo_calculo' => (string) $subcategoria->tipo_calculo,

                    'orden' => (int) $subcategoria->orden,

                    'marcas' => $marcasPorSubcategoria
                        ->get($subcategoria->id, collect())
                        ->map(
                            fn ($marca) => trim(
                                (string) $marca->marca_excel
                            )
                        )
                        ->values()
                        ->all(),

                    'rangos' => $rangosPorSubcategoria
                        ->get($subcategoria->id, collect())
                        ->map(fn ($rango) => [
                            'id' => (int) $rango->id,

                            'desde' => (float) $rango->desde,

                            'hasta' => $rango->hasta === null
                                ? null
                                : (float) $rango->hasta,

                            // 5 significa 5 %, no 0.05.
                            'porcentaje' => (float) $rango->porcentaje,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values();

        return response()->json([
            'promociones' => $promociones,

            'subcategorias_descuentos' => $configuracion,

            'configuracion_descuentos_version' => 1,
        ]);
    }
}