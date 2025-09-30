<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComercialController extends Controller
{

    public function clientesImpactados(Request $request, $asesor, $periodo)
    {
        // periodo esperado: YYYYMM (ej: 202509)
        $periodo = preg_replace('/\D/', '', $periodo);
        if (strlen($periodo) !== 6) {
            return response()->json(['message' => 'Periodo inválido (use YYYYMM)'], 422);
        }
        
        $year  = (int) substr($periodo, 0, 4);
        $month = (int) substr($periodo, 4, 2);
        $asesor = (int) $asesor;

        // Total clientes del asesor
        $totalRow = DB::connection('sqlsrv')->selectOne(
            "SELECT COUNT(DISTINCT t200.f200_nit) AS total_clientes
             FROM t200_mm_terceros t200
             LEFT JOIN t201_mm_clientes t201
               ON t200.f200_rowid = t201.f201_rowid_tercero
             WHERE t200.f200_id_cia = 3
               AND t201.f201_id_cia = 3
               AND t200.f200_ind_cliente = 1
               AND t200.f200_ind_estado = 1
               AND t201.f201_id_vendedor = ?",
            [$asesor]
        );

        // Clientes con venta en el periodo del asesor
        $ventaRow = DB::connection('sqlsrv')->selectOne(
            "SELECT COUNT(DISTINCT t461.f461_rowid_tercero_fact) AS clientes_con_venta
               FROM t461_cm_docto_factura_venta t461
               INNER JOIN t201_mm_clientes t201
                 ON t201.f201_rowid_tercero = t461.f461_rowid_tercero_fact
              WHERE t461.f461_id_cia = 3
                AND YEAR(t461.f461_id_fecha) = ?
                AND MONTH(t461.f461_id_fecha) = ?
                AND t201.f201_id_vendedor = ?",
            [$year, $month, $asesor]
        );

        $total = (int)($totalRow->total_clientes ?? 0);
        $conVenta = (int)($ventaRow->clientes_con_venta ?? 0);
        $cumplimiento = $total > 0 ? round(($conVenta / $total) * 100, 2) : 0.0;

        return response()->json([
            'total_clientes'       => $total,
            'clientes_con_venta'   => $conVenta,
            'cumplimiento_porcent' => $cumplimiento, // 0-100
            'periodo'              => $periodo,
            'asesor'               => $asesor,
        ]);
    }
}
