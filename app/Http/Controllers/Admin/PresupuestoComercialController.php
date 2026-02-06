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
            fn () => print($csv),
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
        return DB::connection('sqlsrv')->select("
            SELECT
                bi_t461.f_periodo AS periodo,
                bi_t461.f_vendedor AS vendedor,
                t106.f106_descripcion AS marca,
                SUM(bi_t461.f_valor_sub_local) AS venta
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

    /**
     * Si igual quieres endpoint JSON (opcional)
     */
    public function cumplimientoJson(Request $request)
    {
        $periodo = preg_replace('/\D/', '', (string) $request->input('periodo', now()->format('Ym')));
        return response()->json([
            'ventas' => $this->cumplimientoData($periodo),
        ]);
    }
}
