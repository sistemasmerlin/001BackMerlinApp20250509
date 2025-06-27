<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\InteresesCartera;


class CarteraController extends Controller
{
    public function cargarFacturasCliente($nit){
        
        $facturas = DB::connection('sqlsrv')
        ->select("SELECT f200_nit as nit
            ,[f200_razon_social] as razon_social
            ,[f353_id_tipo_docto_cruce] as prefijo
            ,CASE 
                WHEN [f353_id_tipo_docto_cruce] = 'FVM' THEN 'FEDQ'
            ELSE [f353_id_tipo_docto_cruce] END AS prefijo_fe
            ,CASE 
                WHEN [f353_id_tipo_docto_cruce] = 'FVM' THEN 'factura'
            ELSE 'nota' END AS tipo_documento
            ,[f353_consec_docto_cruce] as consecutivo
            ,f353_total_db AS total_d
            ,f353_valor_base as valor_base
            ,(f353_valor_base+[f353_valor_impuesto]) as total_factura
            ,[f353_valor_impuesto] as impuestos
            ,f353_total_cr AS abonos
            ,(f353_total_db - f353_total_cr) as saldo
            ,[f353_fecha_docto_cruce] as fecha_factura
            ,GETDATE() as fecha_hoy
            ,DATEDIFF(DAY, [f353_fecha_docto_cruce], GETDATE()) as dias_transcurrido
            ,t201.[f201_id_vendedor] vendedor
            ,[f353_id_cond_pago] as condicion_pago
        FROM t353_co_saldo_abierto t353
            INNER JOIN t201_mm_clientes as t201
                ON t201.f201_rowid_tercero = t353.f353_rowid_tercero
                AND t201.f201_id_sucursal = t353.f353_id_sucursal
                AND t201.f201_id_cia =t353.f353_id_cia
            INNER JOIN t200_mm_terceros as t200
                ON t200.f200_rowid = t201.f201_rowid_tercero
            INNER JOIN t253_co_auxiliares as t253
                ON t253.f253_rowid = t353.f353_rowid_auxiliar
            AND t253.f253_ind_sa=1
        WHERE t353.f353_id_cia=3
            AND f353_fecha_cancelacion IS NULL
            AND [f353_id_tipo_docto_cruce] in ('FVM','CNC','NCD','NIC','R01','R02','R03','R04',
                'R05','R06','R07','R08','R09',
                'R10','R11','R12','R13','R14','R15','R16','R17','R18','R19','R20','R21','R22',
                'R23','R24','R25','R26','R27','R28','R29','R30','R31','R32','R33','R34','R35',
                'R36','R37','R38','R39','R40','R41','R42','R43','R44','R45','R46','R47','R48',
                'R49','R50')
            --AND t253.f253_id = 13050501
            AND t200.f200_nit  = '$nit'
            --AND (f353_total_db - f353_total_cr) > 0
            ORDER BY [f353_id_tipo_docto_cruce], dias_transcurrido DESC");

        $consecutivos = collect($facturas)->pluck('consecutivo')->toArray();

        $intereses = InteresesCartera::whereIn('consecutivo', $consecutivos)->get();


        foreach ($facturas as &$factura) {
            $interes = $intereses->firstWhere('consecutivo', $factura->consecutivo);

            $factura->interes_acumulado = $interes ? $interes->valor_acumulado_interes : 0;
        }

        return response()->json([
            'facturas' => $facturas,
            'estado' => true,
            'mensaje' => 'Se retornan las facturas pendientes de pago',
        ], 200);
    }
}
