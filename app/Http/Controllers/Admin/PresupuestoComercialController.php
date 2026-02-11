<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresupuestoComercialController extends Controller
{
    public function plantilla()
    {
        $csv = implode("\n", [
            'periodo,codigo_asesor,tipo_presupuesto,presupuesto,marca,categoria,clasificacion_asesor',
            '202509,A001,valor,15000000,RINOVA,llantas,A',
            '202509,A001,unidades,120,RINOVA,llantas,A',
            '202509,A002,valor,8000000,,repuestos,B',
        ]);

        return response()->streamDownload(
            fn() => print($csv),
            'plantilla_presupuestos.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * ESTE es el que usa Livewire
     * Retorna filas: periodo, vendedor, marca, venta
     */
    public function cumplimientoData(string $periodo): array
    {
        return DB::connection('sqlsrv')
        ->select("SELECT
                bi_t461.f_periodo AS periodo,
                bi_t461.f_vendedor AS vendedor,
                t106.f106_descripcion AS marca,
                SUM(bi_t461.f_valor_sub_local) AS venta,
                SUM(bi_t461.f_cant_base) AS unidades
            FROM BI_T461_1 AS bi_t461
            LEFT JOIN t120_mc_items AS t120
                ON t120.f120_rowid = bi_t461.f_rowid_item
            LEFT JOIN t125_mc_items_criterios AS t125
                ON t125.f125_rowid_item = t120.f120_rowid
            LEFT JOIN t105_mc_criterios_item_planes AS t105
                ON t105.f105_id = t125.f125_id_plan
            LEFT JOIN t106_mc_criterios_item_mayores AS t106
                ON t106.f106_id = t125.f125_id_criterio_mayor
            WHERE
                bi_t461.f_id_cia = 3
                AND t105.f105_descripcion = 'MARCA'
                AND t106.f106_descripcion IN (
                    'RINOVA TIRES','PIRELLI','RINOVA LIGHTING','RINOVA LIGHTING LED','PIRELLI RADIAL',
                    'NARVA','KOYO','PFI','RNV','BATERIAS RINOVA','RINOVA - GOOD TUBE','HAKUBA - ARMOR - WDT',
                    'WDT','CST TIRES','WDT BIKE','WDT TUBE','WDT E-SCOOTER','FORERUNNER','CST ATV','WORCRAFT','CST E-SCOOTER'
                )
                AND bi_t461.f_parametro_biable = 3
                AND t105.f105_id_cia = bi_t461.f_id_cia
                AND t106.f106_id_cia = bi_t461.f_id_cia
                AND bi_t461.f_periodo = ?
            GROUP BY bi_t461.f_periodo, bi_t461.f_vendedor, t106.f106_descripcion
        ", [$periodo]);
    }

    public function comprometidosData(): array
    {
        return DB::connection('sqlsrv')->select("
            SELECT
                t106.f106_descripcion AS marca,
                SUM(ISNULL(t431.f431_cant1_comprometida, 0)) AS unidades_comprometidas,
                SUM(ISNULL(t431.f431_vlr_bruto, 0) - ISNULL(t431.f431_vlr_dscto_linea, 0)) AS valor_bruto_menos_dscto_linea
            FROM t431_cm_pv_movto t431
            LEFT JOIN t121_mc_items_extensiones t121
                ON t431.f431_rowid_item_ext = t121.f121_rowid
            LEFT JOIN t120_mc_items t120
                ON t120.f120_rowid = t121.f121_rowid_item
            LEFT JOIN t125_mc_items_criterios t125
                ON t125.f125_rowid_item = t120.f120_rowid
            AND t125.f125_id_cia = t120.f120_id_cia
            AND t125.f125_id_plan = '003'
            INNER JOIN t106_mc_criterios_item_mayores t106
                ON t106.f106_id = t125.f125_id_criterio_mayor
            AND t106.f106_id_plan = t125.f125_id_plan
            AND t106.f106_id_plan = '003'
            AND t106.f106_id_cia = t125.f125_id_cia
            LEFT JOIN t430_cm_pv_docto t430
                ON t431.f431_rowid_pv_docto = t430.f430_rowid
            LEFT JOIN t054_mm_estados t054
                ON t054.f054_id = t430.f430_ind_estado
            AND t054.f054_id_grupo_clase_docto = 502
            WHERE t430.f430_id_cia = 3
            AND t431.f431_id_cia = 3
            AND t054.f054_descripcion = 'Comprometido'
            GROUP BY t106.f106_descripcion
            ORDER BY valor_bruto_menos_dscto_linea DESC;");
    }

    public function cumplimientoJson(Request $request)
    {
        $periodo = preg_replace('/\D/', '', (string) $request->input('periodo', now()->format('Ym')));
        return response()->json([
            'ventas' => $this->cumplimientoData($periodo),
            'comprometidos' => $this->comprometidosData(),
        ]);
    }
}
