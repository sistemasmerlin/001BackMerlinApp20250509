<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PresupuestoRecaudo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\InteresesCartera;
use App\Models\User;
use Carbon\Carbon;

class CarteraController extends Controller
{
    public function cargarFacturasCliente($nit)
    {

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

    public function recuadoPresupuesto(Request $request)
    {

        $cedula = $request->asesor;

        $categoria_asesor = $request->categoria_asesor;

        $periodo = str_replace("-", "", $request->periodo);

        $dataPresupuestos = $this->obtenerPresupuestos($periodo, $cedula);

        $dataPresupuestos->each(function ($dataPresupuesto) use ($periodo) {
            $totalRecaudado = $this->calcularTotalRecaudado($periodo, $dataPresupuesto->cedula);
            $totalRecaudadoDiez = $this->calcularTotalRecaudadoDiez($periodo, $dataPresupuesto->cedula);
            $totalRecaudadoContado = $this->calcularTotalRecaudadoContado($periodo, $dataPresupuesto->cedula);
            $dataPresupuesto->total_recaudado = $totalRecaudado;
            $dataPresupuesto->porcentaje_comision = 0;
            $dataPresupuesto->total_recaudado_diez = $totalRecaudadoDiez;
            $dataPresupuesto->total_recaudado_contado = $totalRecaudadoContado;
        });

        //return $dataPresupuestos;

        foreach ($dataPresupuestos as $dataPresupuesto) {

            $recaudos = \DB::connection('sqlsrv')->select("SELECT 
                    RTRIM(bi_t351_1.[tercero_vend]) AS tercero_vendedor,
                    SUM(
                        CASE 
                            WHEN bi_t461.[f_valor_imp_local] > 0 
                            THEN bi_t351_1.[creditos] 
                            ELSE bi_t351_1.[creditos] 
                        END
                    ) AS total_creditos
                FROM [UnoEE].[dbo].[BI_T351_1] AS bi_t351_1
                LEFT JOIN [UnoEE].[dbo].[BI_T461] AS bi_t461
                    ON bi_t461.[f_id_tipo_docto] = bi_t351_1.[id_tipo_docto_cruce]
                    AND bi_t461.[f_nrodocto] = bi_t351_1.[nro_docto_cruce]
                WHERE bi_t351_1.[compañia] = 3
                    AND bi_t351_1.[tercero_vend] = '$dataPresupuesto->cedula'
                    AND bi_t351_1.[parametro_biable] = 3
                    AND bi_t351_1.[id_periodo] = '$periodo'
                    AND bi_t351_1.[id_tipo_docto_cruce] IN ('FVM')
                    AND bi_t351_1.[id_tipo_docto] IN (
                        'FCF','FCR','FRC','NCD','NDE','R01','R02','R03','R04','R05','R06','R07','R08','R09','R10',
                        'R11','R12','R13','R14','R15','R16','R17','R18','R19','R20','R21','R22','R23','R24',
                        'R25','R26','R27','R28','R29','R30','R31','R32','R33','R34','R35','R36','R37','R38',
                        'R39','R40','R41','R42','R43','R44','R45','R46','R47','R48','R49','R50'
                    )
                    AND bi_t461.[f_id_cia] = 3
                    AND bi_t461.[f_parametro_biable] = 3
                GROUP BY 
                    RTRIM(bi_t351_1.[tercero_vend]);");


            $fecha = Carbon::createFromFormat('Ym', $periodo);
            $mesAnterior = $fecha->subMonth()->format('Ym');
            $mesTresMenos = $fecha->subMonths(2)->format('Ym');

            $porcentaje_flete = 0;

            $dataPresupuesto->porcentaje_flete = $porcentaje_flete;


            $recaudo_total_general = 0;
            $total_recaudado_general_sin_flete = 0;

            foreach ($recaudos as $recaudo) {
                $recaudo_total_general = $recaudo->total_creditos;

                $total_recaudado_general_sin_flete = round($recaudo->total_creditos - (($recaudo->total_creditos / 100) * $porcentaje_flete));
            }

            $dataPresupuesto->total_recaudado_general = $recaudo_total_general;

            $dataPresupuesto->total_recaudado_general_sin_flete = $total_recaudado_general_sin_flete;

            if ($dataPresupuesto->total_presupuesto > 0) {
                $dataPresupuesto->total_presupuesto = ($dataPresupuesto->total_presupuesto/1.19);
                $dataPresupuesto->cumplimiento = round(($dataPresupuesto->total_recaudado / $dataPresupuesto->total_presupuesto) * 100, 1);
            } else {
                $dataPresupuesto->cumplimiento = 0;
            }

            $porcentajeClientesImpactadosCredito = $this->porcentajeClientesImpactadosCredito($periodo, $cedula);

            $baseComision = $dataPresupuesto->total_recaudado_general_sin_flete ?? 0;

            if ($categoria_asesor == 'master') {
                if($porcentajeClientesImpactadosCredito >= 60){
                    $baseComision = $baseComision * 1.1;
                }elseif($porcentajeClientesImpactadosCredito >= 55){
                    $baseComision = $baseComision * 1.05;
                }elseif($porcentajeClientesImpactadosCredito >= 50){
                    $baseComision = $baseComision;
                }elseif($porcentajeClientesImpactadosCredito >= 45){
                    $baseComision = $baseComision * 0.90;
                }elseif($porcentajeClientesImpactadosCredito >= 45){
                    $baseComision = $baseComision * 0.8;
                }else{
                   $baseComision = 0; 
                }
            }else{
                if($porcentajeClientesImpactadosCredito >= 60){
                    $baseComision = $baseComision * 1.05;
                }elseif($porcentajeClientesImpactadosCredito >= 55){
                    $baseComision = $baseComision * 1.02;
                }elseif($porcentajeClientesImpactadosCredito >= 50){
                    $baseComision = $baseComision;
                }elseif($porcentajeClientesImpactadosCredito >= 45){
                    $baseComision = $baseComision * 0.90;
                }elseif($porcentajeClientesImpactadosCredito >= 45){
                    $baseComision = $baseComision * 0.8;
                }else{
                   $baseComision = 0; 
                }
            }

            if ($categoria_asesor == 'master') {
                if ($dataPresupuesto->cumplimiento >= 90) {
                    $dataPresupuesto->porcentaje_comision = 0.00600;
                    $dataPresupuesto->comisiones = round($baseComision * $dataPresupuesto->porcentaje_comision);
                } elseif ($dataPresupuesto->cumplimiento >= 85) {
                    $dataPresupuesto->porcentaje_comision = 0.0057;
                    $dataPresupuesto->comisiones = round($baseComision * $dataPresupuesto->porcentaje_comision);
                } elseif ($dataPresupuesto->cumplimiento >= 80) {
                    $dataPresupuesto->porcentaje_comision = 0.0054;
                    $dataPresupuesto->comisiones = round($baseComision * $dataPresupuesto->porcentaje_comision);
                } else {
                    $dataPresupuesto->porcentaje_comision = 0;
                    $dataPresupuesto->comisiones = 0;
                }
            } elseif ($categoria_asesor == 'senior') {
                if ($dataPresupuesto->cumplimiento >= 90) {
                    $dataPresupuesto->porcentaje_comision = 0.00600;
                    $dataPresupuesto->comisiones = round($baseComision * $dataPresupuesto->porcentaje_comision);
                } elseif ($dataPresupuesto->cumplimiento >= 85) {
                    $dataPresupuesto->porcentaje_comision = 0.0057;
                    $dataPresupuesto->comisiones = round($baseComision * $dataPresupuesto->porcentaje_comision);
                } elseif ($dataPresupuesto->cumplimiento >= 80) {
                    $dataPresupuesto->porcentaje_comision = 0.00540;
                    $dataPresupuesto->comisiones = round($baseComision * $dataPresupuesto->porcentaje_comision);
                } else {
                    $dataPresupuesto->porcentaje_comision = 0;
                    $dataPresupuesto->comisiones = 0;
                }
            }
        }

        $recuadoPorDiasCartera = $this->recuadoPorDiasCartera($periodo, $cedula);


        return response()->json([
            'categoria_asesor' => $categoria_asesor,
            'recaudoCartera' => $dataPresupuestos,
            'recuadoPorDiasCartera' => $recuadoPorDiasCartera,
           // 'porcentajeClientesImpactadosCredito' => $porcentajeClientesImpactadosCredito,
            'estado' => true,
            'mensaje' => 'Se retornan las facturas pendientes de pago',
        ], 200);
    }

    private function obtenerPresupuestos($periodo, $cedula)
    {
        return User::select(
            DB::raw('RTRIM(users.name) as name'),
            'users.email',
            'users.cedula',
            'users.codigo_asesor',
            'users.categoria_asesor',
            'presupuesto_recaudo.periodo',
            DB::raw('SUM(presupuesto_recaudo.saldo) as total_presupuesto'),
            DB::raw('0 as total_recaudado'),
            DB::raw('0 as total_recaudado_diez'),
            DB::raw('0 as total_recaudado_contado'),
            DB::raw('0 as total_recaudado_general'),
            DB::raw('0 as total_recaudado_general_sin_flete'),
            DB::raw('0 as porcentaje_flete'),
            DB::raw('0 as cumplimiento'),
            DB::raw('0 as comisiones')
        )
            ->leftJoin('presupuesto_recaudo', 'presupuesto_recaudo.asesor', '=', 'users.cedula')
            ->groupBy('users.cedula', 'presupuesto_recaudo.periodo', 'users.name', 'users.email', 'users.codigo_asesor')
            ->where('presupuesto_recaudo.periodo', $periodo)
            ->where('users.cedula', '=', $cedula)
            ->where('presupuesto_recaudo.estado', '1')
            ->get();
    }

    private function calcularTotalRecaudado($periodo, $cedula)
    {
        $infoPresupuestos = PresupuestoRecaudo::select('prefijo', 'consecutivo', 'cond_pago')
            ->where('estado', 1)
            ->where('periodo', $periodo)
            ->where('asesor', $cedula)
            ->get();

        // Si no hay presupuestos, retorna 0
        if ($infoPresupuestos->isEmpty()) {
            return 0;
        }

        // Extraer los valores de 'prefijo' y 'consecutivo'
        $prefijos = $infoPresupuestos->pluck('prefijo')->toArray();
        $consecutivos = $infoPresupuestos->pluck('consecutivo')->toArray();

        // Realiza la consulta SQL con el uso de parámetros en lugar de concatenación
        $totalCreditos = \DB::connection('sqlsrv')->table('UnoEE.dbo.BI_T351_1')
            ->where('compañia', 3)
            ->where('id_periodo', $periodo)
            ->whereIn('id_tipo_docto', [
                'FCF',
                'FCR',
                'FRC',
                'NCD',
                'R01',
                'R02',
                'R03',
                'R04',
                'R05',
                'R06',
                'R07',
                'R08',
                'R09',
                'R10',
                'R11',
                'R12',
                'R13',
                'R14',
                'R15',
                'R16',
                'R17',
                'R18',
                'R19',
                'R20',
                'R21',
                'R22',
                'R23',
                'R24',
                'R25',
                'R26',
                'R27',
                'R28',
                'R29',
                'R30',
                'R31',
                'R32',
                'R33',
                'R34',
                'R35',
                'R36',
                'R37',
                'R38',
                'R39',
                'R40',
                'R41',
                'R42',
                'R43',
                'R44',
                'R45',
                'R46',
                'R47',
                'R48',
                'R49',
                'R50'
            ])
            ->whereIn('id_tipo_docto_cruce', $prefijos)
            ->whereIn('nro_docto_cruce', $consecutivos)
            ->where('parametro_biable', 3)
            ->where('creditos', '>', 0)
            ->sum('creditos');

        return ($totalCreditos/1.19) ?? 0;
    }

    private function calcularTotalRecaudadoDiez($periodo, $cedula)
    {
        $infoPresupuestos = PresupuestoRecaudo::select('prefijo', 'consecutivo', 'cond_pago')
            ->where('estado', 1)
            ->where('periodo', $periodo)
            ->where('asesor', $cedula)
            ->get();

        // Si no hay presupuestos, retorna 0
        if ($infoPresupuestos->isEmpty()) {
            return 0;
        }

        // Realiza la consulta SQL con el uso de parámetros en lugar de concatenación
        $totalCreditosDiez = \DB::connection('sqlsrv')->table('UnoEE.dbo.BI_T351_1')
            ->where('compañia', 3)
            ->where('id_periodo', $periodo)
            ->whereIn('id_tipo_docto', [
                'FCF',
                'FCR',
                'FRC',
                'NCD',
                'R01',
                'R02',
                'R03',
                'R04',
                'R05',
                'R06',
                'R07',
                'R08',
                'R09',
                'R10',
                'R11',
                'R12',
                'R13',
                'R14',
                'R15',
                'R16',
                'R17',
                'R18',
                'R19',
                'R20',
                'R21',
                'R22',
                'R23',
                'R24',
                'R25',
                'R26',
                'R27',
                'R28',
                'R29',
                'R30',
                'R31',
                'R32',
                'R33',
                'R34',
                'R35',
                'R36',
                'R37',
                'R38',
                'R39',
                'R40',
                'R41',
                'R42',
                'R43',
                'R44',
                'R45',
                'R46',
                'R47',
                'R48',
                'R49',
                'R50',
                'R51',
                'R52',
                'R53'
            ])
            ->where('condicion_pago', '10D')
            ->where('parametro_biable', 3)
            ->where('docto_vend', $cedula)
            ->where('creditos', '>', 0)
            ->sum('creditos');

        return $totalCreditosDiez ?? 0;
    }

    private function calcularTotalRecaudadoContado($periodo, $cedula)
    {
        $infoPresupuestos = PresupuestoRecaudo::select('prefijo', 'consecutivo')
            ->where('estado', 1)
            ->where('periodo', $periodo)
            ->where('asesor', $cedula)
            ->get();

        // Si no hay presupuestos, retorna 0
        if ($infoPresupuestos->isEmpty()) {
            return 0;
        }

        // Realiza la consulta SQL con el uso de parámetros en lugar de concatenación
        $totalCreditosContado = \DB::connection('sqlsrv')->table('UnoEE.dbo.BI_T351_1')
            ->where('compañia', 3)
            ->where('id_periodo', $periodo)
            ->whereIn('id_tipo_docto', [
                'FCF',
                'FCR',
                'FRC',
                'NCD',
                'R01',
                'R02',
                'R03',
                'R04',
                'R05',
                'R06',
                'R07',
                'R08',
                'R09',
                'R10',
                'R11',
                'R12',
                'R13',
                'R14',
                'R15',
                'R16',
                'R17',
                'R18',
                'R19',
                'R20',
                'R21',
                'R22',
                'R23',
                'R24',
                'R25',
                'R26',
                'R27',
                'R28',
                'R29',
                'R30',
                'R31',
                'R32',
                'R33',
                'R34',
                'R35',
                'R36',
                'R37',
                'R38',
                'R39',
                'R40',
                'R41',
                'R42',
                'R43',
                'R44',
                'R45',
                'R46',
                'R47',
                'R48',
                'R49',
                'R50'
            ])
            ->where('condicion_pago', 'CON')
            ->where('parametro_biable', 3)
            ->where('docto_vend', $cedula)
            ->where('creditos', '>', 0)
            ->sum('creditos');

        return $totalCreditosContado ?? 0;
    }


    public function recuadoPorDiasCartera($periodo, $asesor)
    {

        $periodo = str_replace("-", "", $periodo);
        $cedula = $asesor;

        return $data_asesores = $this->recuadoPorDias($cedula, $periodo);
    }
    public function recuadoPorDias($cedula, $periodo)
    {
        $data_asesores = User::select(
            DB::raw('RTRIM(users.name) as name'),
            'users.email',
            'users.cedula',
            'users.codigo_asesor',
            'users.categoria_asesor', // <-- IMPORTANTE para que no sea null

            DB::raw('0 as recaudo_1_15'),
            DB::raw('0 as recaudo_1_a_15_sin_flete'),
            DB::raw('0 as porcentaje_1_a_15'),
            DB::raw('0 as comision_1_a_15'),

            DB::raw('0 as recaudo_16_30'),
            DB::raw('0 as recaudo_16_a_30_sin_flete'),
            DB::raw('0 as porcentaje_16_a_30'),
            DB::raw('0 as comision_16_a_30'),

            DB::raw('0 as recaudo_31_45'),
            DB::raw('0 as recaudo_31_a_45_sin_flete'),
            DB::raw('0 as porcentaje_31_a_45'),
            DB::raw('0 as comision_31_a_45'),

            DB::raw('0 as recaudo_46_60'),
            DB::raw('0 as recaudo_46_a_60_sin_flete'),
            DB::raw('0 as porcentaje_46_a_60'),
            DB::raw('0 as comision_46_a_60'),

            DB::raw('0 as recaudo_61_75'),
            DB::raw('0 as recaudo_61_a_75_sin_flete'),
            DB::raw('0 as porcentaje_61_a_75'),
            DB::raw('0 as comision_61_a_75'),

            DB::raw('0 as recaudo_76_85'),
            DB::raw('0 as recaudo_76_a_85_sin_flete'),
            DB::raw('0 as porcentaje_76_a_85'),
            DB::raw('0 as comision_76_a_85'),

            DB::raw('0 as recaudo_mayor_86'),
            DB::raw('0 as recaudo_mayor_86_sin_flete'),
            DB::raw('0 as porcentaje_mayor_a_86'),

            DB::raw('0 as porcentaje_flete'),
            DB::raw('0 as comision_a_pagar')
        )
            ->where('cedula', '=', $cedula)
            ->get();

        $terceros_vendedores = $data_asesores->pluck('cedula')->toArray();

        // 1) Recaudos por rangos (SQL Server)
        $recaudos = \DB::connection('sqlsrv')->select("
        SELECT 
            RTRIM(bi_t351_1.[tercero_vend]) tercero_vend,
            SUM(CASE 
                WHEN DATEDIFF(day, CONVERT(datetime, bi_t351_1.[fecha_docto_cruce], 112), CONVERT(datetime, bi_t351_1.[fecha_recaudo], 112)) BETWEEN -100 AND 16 
                THEN bi_t351_1.[creditos] ELSE 0 END
            ) AS creditos_1_15,
            SUM(CASE 
                WHEN DATEDIFF(day, CONVERT(datetime, bi_t351_1.[fecha_docto_cruce], 112), CONVERT(datetime, bi_t351_1.[fecha_recaudo], 112)) BETWEEN 16 AND 30 
                THEN bi_t351_1.[creditos] ELSE 0 END
            ) AS creditos_16_30,
            SUM(CASE 
                WHEN DATEDIFF(day, CONVERT(datetime, bi_t351_1.[fecha_docto_cruce], 112), CONVERT(datetime, bi_t351_1.[fecha_recaudo], 112)) BETWEEN 31 AND 45 
                THEN bi_t351_1.[creditos] ELSE 0 END
            ) AS creditos_31_45,
            SUM(CASE 
                WHEN DATEDIFF(day, CONVERT(datetime, bi_t351_1.[fecha_docto_cruce], 112), CONVERT(datetime, bi_t351_1.[fecha_recaudo], 112)) BETWEEN 46 AND 65 
                THEN bi_t351_1.[creditos] ELSE 0 END
            ) AS creditos_46_60,
            SUM(CASE 
                WHEN DATEDIFF(day, CONVERT(datetime, bi_t351_1.[fecha_docto_cruce], 112), CONVERT(datetime, bi_t351_1.[fecha_recaudo], 112)) BETWEEN 66 AND 80 
                THEN bi_t351_1.[creditos] ELSE 0 END
            ) AS creditos_61_75,
            SUM(CASE 
                WHEN DATEDIFF(day, CONVERT(datetime, bi_t351_1.[fecha_docto_cruce], 112), CONVERT(datetime, bi_t351_1.[fecha_recaudo], 112)) >= 81
                THEN bi_t351_1.[creditos] ELSE 0 END
            ) AS creditos_76_85,
            SUM(CASE 
                WHEN DATEDIFF(day, CONVERT(datetime, bi_t351_1.[fecha_docto_cruce], 112), CONVERT(datetime, bi_t351_1.[fecha_recaudo], 112)) >= 100000000
                THEN bi_t351_1.[creditos] ELSE 0 END
            ) AS creditos_mayor_86
        FROM 
            [UnoEE].[dbo].[BI_T351_1] AS bi_t351_1
        LEFT JOIN 
            [UnoEE].[dbo].[BI_T461] AS bi_t461
        ON 
            bi_t461.[f_id_tipo_docto] = bi_t351_1.[id_tipo_docto_cruce]
        AND 
            bi_t461.[f_nrodocto] = bi_t351_1.[nro_docto_cruce]
        WHERE 
            bi_t351_1.[compañia] = 3
            AND bi_t351_1.[parametro_biable] = 3
            AND bi_t351_1.[id_periodo] = '$periodo'
            AND bi_t351_1.[id_tipo_docto_cruce] IN ('FVM')
            AND bi_t351_1.[id_tipo_docto] IN (
                'FCF','FCR','FRC','NCD','R01','R02','R03','R04','R05','R06','R07','R08','R09','R10','R11','R12','R13','R14','R15',
                'R16','R17','R18','R19','R20','R21','R22','R23','R24','R25','R26','R27','R28','R29','R30','R31','R32','R33','R34','R35',
                'R36','R37','R38','R39','R40','R41','R42','R43','R44','R45','R46','R47','R48','R49','R50'
            )
            AND bi_t461.[f_id_cia] = 3
            AND bi_t461.[f_parametro_biable] = 3
            AND RTRIM(bi_t351_1.[tercero_vend]) IN ('" . implode("','", $terceros_vendedores) . "')
        GROUP BY 
            RTRIM(bi_t351_1.[tercero_vend])
    ");

        $recaudos = collect($recaudos)->keyBy('tercero_vend');

        foreach ($data_asesores as $data_asesor) {
            if (isset($recaudos[$data_asesor->cedula])) {
                $recaudoGen = $recaudos[$data_asesor->cedula];
                $data_asesor->recaudo_1_15   = round($recaudoGen->creditos_1_15);
                $data_asesor->recaudo_16_30  = round($recaudoGen->creditos_16_30);
                $data_asesor->recaudo_31_45  = round($recaudoGen->creditos_31_45);
                $data_asesor->recaudo_46_60  = round($recaudoGen->creditos_46_60);
                $data_asesor->recaudo_61_75  = round($recaudoGen->creditos_61_75);
                $data_asesor->recaudo_76_85  = round($recaudoGen->creditos_76_85);
                $data_asesor->recaudo_mayor_86 = round($recaudoGen->creditos_mayor_86);
            }
        }

        // Para % flete: mes anterior y dos meses atrás (3 meses de ventana: mes-2 a mes-1)
        $fecha = Carbon::createFromFormat('Ym', $periodo);
        $mesAnterior  = $fecha->copy()->subMonth()->format('Ym');
        $mesTresMenos = $fecha->copy()->subMonths(2)->format('Ym');

        foreach ($data_asesores as $data_asesor) {

            // 2) Porcentajes por categoría (ASIGNADOS AL OBJETO)
            $cat = strtolower(trim((string)($data_asesor->categoria_asesor ?? '')));

            // por defecto (si no es master/senior)
            $data_asesor->porcentaje_1_a_15  = 0;
            $data_asesor->porcentaje_16_a_30 = 0;
            $data_asesor->porcentaje_31_a_45 = 0;
            $data_asesor->porcentaje_46_a_60 = 0;
            $data_asesor->porcentaje_61_a_75 = 0;
            $data_asesor->porcentaje_76_a_85 = 0;

            if ($cat === 'master') {
                $data_asesor->porcentaje_1_a_15  = 0.0041;
                $data_asesor->porcentaje_16_a_30 = 0.0038;
                $data_asesor->porcentaje_31_a_45 = 0.0035;
                $data_asesor->porcentaje_46_a_60 = 0.0032;
                $data_asesor->porcentaje_61_a_75 = 0.0029;
                $data_asesor->porcentaje_76_a_85 = 0;
            } elseif ($cat === 'senior') {
                $data_asesor->porcentaje_1_a_15  = 0.0028;
                $data_asesor->porcentaje_16_a_30 = 0.0025;
                $data_asesor->porcentaje_31_a_45 = 0.0022;
                $data_asesor->porcentaje_46_a_60 = 0.0019;
                $data_asesor->porcentaje_61_a_75 = 0.0016;
                $data_asesor->porcentaje_76_a_85 = 0;
            }

            // 3) Porcentaje de flete (SQL Server)
            $recaudoFletes = \DB::connection('sqlsrv')->select("
            SELECT
                RTRIM([f_vendedor]) f_vendedor,
                ROUND(
                    (
                        CONVERT(decimal(10), SUM(CASE WHEN [f_ref_item] IN ('ZLE99999', 'ZLE99998') THEN [f_valor_sub_local] ELSE 0 END))
                        /
                        NULLIF(CONVERT(decimal(10), SUM(CASE WHEN [f_ref_item] NOT IN ('ZLE99999', 'ZLE99998', '101999999', '0013686', '0013694', '0013695', '0013822') THEN [f_valor_sub_local] ELSE 0 END)), 0)
                    ) * 100
                , 2) AS porcentaje
            FROM [UnoEE].[dbo].[BI_T461_1]
            WHERE
                [f_id_cia] = 3
                AND f_parametro_biable = 3
                AND RTRIM([f_vendedor]) = '{$data_asesor->cedula}'
                AND f_periodo BETWEEN '$mesTresMenos' AND '$mesAnterior'
            GROUP BY [f_vendedor]
        ");

            $data_asesor->porcentaje_flete = 0;
            if (!empty($recaudoFletes)) {
                $data_asesor->porcentaje_flete = (float)($recaudoFletes[0]->porcentaje ?? 0);
            }

            // 4) Recaudo sin flete
            $pf = (float)($data_asesor->porcentaje_flete ?? 0);

            $data_asesor->recaudo_1_a_15_sin_flete = round(round($data_asesor->recaudo_1_15 - (($data_asesor->recaudo_1_15 / 100) * $pf))/1.19);
            $data_asesor->recaudo_16_a_30_sin_flete = round(round($data_asesor->recaudo_16_30 - (($data_asesor->recaudo_16_30 / 100) * $pf))/1.19);
            $data_asesor->recaudo_31_a_45_sin_flete = round(round($data_asesor->recaudo_31_45 - (($data_asesor->recaudo_31_45 / 100) * $pf))/1.19);
            $data_asesor->recaudo_46_a_60_sin_flete = round(round($data_asesor->recaudo_46_60 - (($data_asesor->recaudo_46_60 / 100) * $pf))/1.19);
            $data_asesor->recaudo_61_a_75_sin_flete = round(round($data_asesor->recaudo_61_75 - (($data_asesor->recaudo_61_75 / 100) * $pf))/1.19);
            $data_asesor->recaudo_76_a_85_sin_flete = round(round($data_asesor->recaudo_76_85 - (($data_asesor->recaudo_76_85 / 100) * $pf))/1.19);
            $data_asesor->recaudo_mayor_86_sin_flete = round(round($data_asesor->recaudo_mayor_86 - (($data_asesor->recaudo_mayor_86 / 100) * $pf))/1.19);

            // 5) Comisiones (CORRECTO: multiplicar directo por 0.00xx)
            $data_asesor->comision_1_a_15 = $data_asesor->porcentaje_1_a_15 != 0
                ? round($data_asesor->recaudo_1_a_15_sin_flete * $data_asesor->porcentaje_1_a_15) : 0;

            $data_asesor->comision_16_a_30 = $data_asesor->porcentaje_16_a_30 != 0
                ? round($data_asesor->recaudo_16_a_30_sin_flete * $data_asesor->porcentaje_16_a_30) : 0;

            $data_asesor->comision_31_a_45 = $data_asesor->porcentaje_31_a_45 != 0
                ? round($data_asesor->recaudo_31_a_45_sin_flete * $data_asesor->porcentaje_31_a_45) : 0;

            $data_asesor->comision_46_a_60 = $data_asesor->porcentaje_46_a_60 != 0
                ? round($data_asesor->recaudo_46_a_60_sin_flete * $data_asesor->porcentaje_46_a_60) : 0;

            $data_asesor->comision_61_a_75 = $data_asesor->porcentaje_61_a_75 != 0
                ? round($data_asesor->recaudo_61_a_75_sin_flete * $data_asesor->porcentaje_61_a_75) : 0;

            $data_asesor->comision_76_a_85 = $data_asesor->porcentaje_76_a_85 != 0
                ? round($data_asesor->recaudo_76_a_85_sin_flete * $data_asesor->porcentaje_76_a_85) : 0;

            $data_asesor->comision_a_pagar =
                $data_asesor->comision_1_a_15 +
                $data_asesor->comision_16_a_30 +
                $data_asesor->comision_31_a_45 +
                $data_asesor->comision_46_a_60 +
                $data_asesor->comision_61_a_75 +
                $data_asesor->comision_76_a_85;
        }

        return $data_asesores;
    }

    public function porcentajeClientesImpactadosCredito(string $periodo, string $asesorCedula): float
{
    // Periodo YYYYMM
    $periodo = preg_replace('/\D/', '', $periodo);
    if (strlen($periodo) !== 6) {
        return 0.0;
    }

    $year  = (int) substr($periodo, 0, 4);
    $month = (int) substr($periodo, 4, 2);

    // IMPORTANTÍSIMO: el asesor aquí es CÉDULA (string), no int
    $asesorCedula = trim($asesorCedula);

    // Total clientes del asesor con condición 30D o 10D (según maestro t201)
    $totalRow = DB::connection('sqlsrv')->selectOne(
        "SELECT COUNT(DISTINCT t200.f200_nit) AS total_clientes
         FROM t200_mm_terceros t200
         INNER JOIN t201_mm_clientes t201
           ON t200.f200_rowid = t201.f201_rowid_tercero
         WHERE t200.f200_id_cia = 3
           AND t201.f201_id_cia = 3
           AND t200.f200_ind_cliente = 1
           AND t200.f200_ind_estado = 1
           AND t201.f201_id_vendedor = ?
           AND t201.f201_id_cond_pago IN ('30D','10D')",
        [$asesorCedula]
    );

    $totalClientes = (int) ($totalRow->total_clientes ?? 0);

    // Clientes con venta en el mes (solo clientes 30D o 10D)
    $ventaRow = DB::connection('sqlsrv')->selectOne(
        "SELECT COUNT(DISTINCT t461.f461_rowid_tercero_fact) AS clientes_con_venta
         FROM t461_cm_docto_factura_venta t461
         INNER JOIN t201_mm_clientes t201
           ON t201.f201_rowid_tercero = t461.f461_rowid_tercero_fact
         WHERE t461.f461_id_cia = 3
           AND YEAR(t461.f461_id_fecha) = ?
           AND MONTH(t461.f461_id_fecha) = ?
           AND t201.f201_id_vendedor = ?
           AND t201.f201_id_cond_pago IN ('30D','10D')",
        [$year, $month, $asesorCedula]
    );

    $clientesConVenta = (int) ($ventaRow->clientes_con_venta ?? 0);

    return $totalClientes > 0
        ? round(($clientesConVenta / $totalClientes) * 100, 2)
        : 0.0;
}

}
