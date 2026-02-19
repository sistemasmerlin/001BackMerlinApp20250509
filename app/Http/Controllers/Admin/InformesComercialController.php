<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformesComercialController extends Controller
{
    public function ventasPeriodo(Request $request, $id)
    {

        $periodo =  $request->input('periodo');

        $result = DB::connection('sqlsrv')
            ->select("SELECT bi_t461.f_periodo AS periodo, 
            bi_t461.f_vendedor AS vendedor, 
            t106.f106_descripcion AS marca,
            SUM([f_valor_sub_local]) AS venta

            FROM BI_T461_1 AS bi_t461

            LEFT JOIN t120_mc_items AS t120
            ON t120.f120_rowid = bi_t461.f_rowid_item

            LEFT JOIN t125_mc_items_criterios AS t125
            ON t125.f125_rowid_item = t120.f120_rowid

            LEFT JOIN t105_mc_criterios_item_planes AS t105
            ON t105.f105_id = t125.f125_id_plan

            LEFT JOIN t106_mc_criterios_item_mayores AS t106
            ON t106.f106_id = t125.f125_id_criterio_mayor

            WHERE bi_t461.f_id_cia = 3
            AND t105.f105_descripcion = 'MARCA'
            AND t106.f106_descripcion IN ('RINOVA TIRES', 'PIRELLI', 'RINOVA LIGHTING','RINOVA LIGHTING LED', 'PIRELLI RADIAL','NARVA', 'KOYO', 'PFI', 'RNV', 'BATERIAS RINOVA', 'RINOVA - GOOD TUBE', 'HAKUBA - ARMOR - WDT', 'WDT','CST TIRES', 'WDT BIKE' ,'WDT TUBE', 'WDT E-SCOOTER','FORERUNNER','CST ATV','WORCRAFT','CST E-SCOOTER')
            AND bi_t461.f_parametro_biable = 3
            AND t105.f105_id_cia = bi_t461.f_id_cia
            AND t106.f106_id_cia = bi_t461.f_id_cia
            AND bi_t461.f_periodo = '202602'
            GROUP BY f_periodo, f_vendedor, t106.f106_descripcion");

        return response()->json([
            'ventas' => $result,
        ]);
    }


    public function cumplimientoEfectividad(Request $request)
    {
        $periodo = (string) $request->get('periodo', '');
        if (!preg_match('/^\d{6}$/', $periodo)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Periodo inválido. Usa formato YYYYMM (ej: 202601).'
            ], 422);
        }

        $anio = (int) substr($periodo, 0, 4);
        $mes  = (int) substr($periodo, 4, 2);

        $clientesPorAsesor = DB::connection('sqlsrv')->select("
        SELECT t201.f201_id_vendedor, COUNT(DISTINCT t200.f200_nit) AS total_clientes
        FROM t200_mm_terceros t200
        LEFT JOIN t201_mm_clientes t201
            ON t200.f200_rowid = t201.f201_rowid_tercero
        WHERE t200.f200_id_cia = 3
            AND t201.f201_id_cia = 3
            AND t200.f200_ind_cliente = 1
            AND t200.f200_ind_estado = 1
            AND t201.f201_id_cond_pago IN ('30D','10D','15D','30E')
        GROUP BY t201.f201_id_vendedor
    ");

        $ventasPorAsesor = DB::connection('sqlsrv')->select("
        SELECT t201.f201_id_vendedor, COUNT(DISTINCT t461.f461_rowid_tercero_fact) AS clientes_con_venta
        FROM t461_cm_docto_factura_venta t461
        INNER JOIN t201_mm_clientes t201
            ON t201.f201_rowid_tercero = t461.f461_rowid_tercero_fact
        WHERE t461.f461_id_cia = 3
            AND YEAR(t461.f461_id_fecha) = ?
            AND MONTH(t461.f461_id_fecha) = ?
            AND t461.f461_id_concepto = '501'
            AND t461.f461_id_clase_docto = '523'
            AND t201.f201_id_cond_pago IN ('30D','10D','15D','30E')
        GROUP BY t201.f201_id_vendedor
    ", [$anio, $mes]);
    }
}
