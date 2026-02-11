<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PQRSController extends Controller
{
    public function consultaProductos(Request $request, $query)
    {
        $query = trim((string) $query);

        // cliente y sucursal deberían venir del request (para que funcione para cualquier cliente)
        $nit = $request->get('nit');            // ej 900447351
        $suc = $request->get('sucursal');       // ej 020

        // Si aún no quieres enviarlos, puedes dejarlos fijos temporalmente:
        if (!$nit) $nit = '900447351';
        if (!$suc) $suc = '020';

        $result = DB::connection('sqlsrv')->select("
            SELECT
                f_cliente_fact,
                f_cliente_fact_suc,
                f_id_tipo_docto,
                f_nrodocto,
                f_fecha,
                f_ref_item,
                t120.f120_descripcion as f_descripcion,
                f_cant_inv,
                f_precio_venta,
                f_valor_bruto_local,
                f_valor_imp_local,
                f_valor_neto_local
            FROM BI_T461_1
            LEFT JOIN t120_mc_items AS t120
                ON t120.f120_referencia = f_ref_item
                AND t120.f120_id_cia = 3
            WHERE
                f_cliente_fact = ?
                AND f_cliente_fact_suc = ?
                AND f_parametro_biable = 3
                AND f_id_cia = 3
                AND (
                    f_ref_item LIKE ?
                    OR t120.f120_descripcion LIKE ?
                )
            ORDER BY f_fecha DESC, f_id_tipo_docto
        ", [
            $nit,
            $suc,
            "%{$query}%",
            "%{$query}%",
        ]);

        return response()->json([
            'success' => true,
            'productos' => $result,
        ]);
    }

    public function consultaFactura(Request $request)
    {
        $nit  = trim((string) $request->get('nit'));        // ej: 900447351
        $suc  = trim((string) $request->get('sucursal'));   // ej: 020
        $fact = trim((string) $request->get('factura'));    // ej: 00000500

        // Fallback temporal (si aún no lo mandas desde Ionic)
        if ($nit === '') $nit = '900447351';
        if ($suc === '') $suc = '020';

        if ($fact === '') {
            return response()->json([
                'success' => false,
                'message' => 'Falta el parámetro factura.',
                'facturas' => [],
            ], 422);
        }

        $result = DB::connection('sqlsrv')->select("
        SELECT
            f_cliente_fact,
            f_cliente_fact_suc,
            f_id_tipo_docto,
            f_nrodocto,
            f_fecha,
            f_ref_item,
            t120.f120_descripcion as f_descripcion,
            f_cant_inv,
            f_precio_venta,
            f_valor_bruto_local,
            f_valor_imp_local,
            f_valor_neto_local
        FROM BI_T461_1
        LEFT JOIN t120_mc_items AS t120
            ON t120.f120_referencia = f_ref_item
            AND t120.f120_id_cia = 3
        WHERE
            f_cliente_fact = ?
            AND f_cliente_fact_suc = ?
            AND f_parametro_biable = 3
            AND f_id_cia = 3
            AND f_nrodocto = ?
        ORDER BY f_fecha DESC, f_id_tipo_docto
    ", [$nit, $suc, $fact]);

        return response()->json([
            'success' => true,
            'facturas' => $result,
        ]);
    }
}
