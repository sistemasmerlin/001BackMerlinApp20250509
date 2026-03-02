<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PresupuestoRecaudo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InteresesCartera;
use App\Models\User;
use Carbon\Carbon;

class CarteraController extends Controller
{
    public function cargarFacturasCliente($nit)
    {
        // ✅ FIX: SQL Injection -> bindings (NO concatenar $nit en el string)
        $sql = "
            SELECT
                f200_nit as nit,
                [f200_razon_social] as razon_social,
                [f353_id_tipo_docto_cruce] as prefijo,
                CASE
                    WHEN [f353_id_tipo_docto_cruce] = 'FVM' THEN 'FEDQ'
                    ELSE [f353_id_tipo_docto_cruce]
                END AS prefijo_fe,
                CASE
                    WHEN [f353_id_tipo_docto_cruce] = 'FVM' THEN 'factura'
                    ELSE 'nota'
                END AS tipo_documento,
                [f353_consec_docto_cruce] as consecutivo,
                f353_total_db AS total_d,
                f353_valor_base as valor_base,
                (f353_valor_base + [f353_valor_impuesto]) as total_factura,
                [f353_valor_impuesto] as impuestos,
                f353_total_cr AS abonos,
                (f353_total_db - f353_total_cr) as saldo,
                [f353_fecha_docto_cruce] as fecha_factura,
                GETDATE() as fecha_hoy,
                DATEDIFF(DAY, [f353_fecha_docto_cruce], GETDATE()) as dias_transcurrido,
                t201.[f201_id_vendedor] vendedor,
                [f353_id_cond_pago] as condicion_pago
            FROM t353_co_saldo_abierto t353
                INNER JOIN t201_mm_clientes as t201
                    ON t201.f201_rowid_tercero = t353.f353_rowid_tercero
                    AND t201.f201_id_sucursal = t353.f353_id_sucursal
                    AND t201.f201_id_cia = t353.f353_id_cia
                INNER JOIN t200_mm_terceros as t200
                    ON t200.f200_rowid = t201.f201_rowid_tercero
                INNER JOIN t253_co_auxiliares as t253
                    ON t253.f253_rowid = t353.f353_rowid_auxiliar
                    AND t253.f253_ind_sa = 1
            WHERE t353.f353_id_cia = 3
                AND f353_fecha_cancelacion IS NULL
                AND [f353_id_tipo_docto_cruce] IN (
                    'FVM','CNC','NCD','NIC',
                    'R01','R02','R03','R04','R05','R06','R07','R08','R09',
                    'R10','R11','R12','R13','R14','R15','R16','R17','R18','R19','R20','R21','R22',
                    'R23','R24','R25','R26','R27','R28','R29','R30','R31','R32','R33','R34','R35',
                    'R36','R37','R38','R39','R40','R41','R42','R43','R44','R45','R46','R47','R48',
                    'R49','R50'
                )
                AND t200.f200_nit = ?
            ORDER BY [f353_id_tipo_docto_cruce], dias_transcurrido DESC
        ";

        $facturas = DB::connection('sqlsrv')->select($sql, [$nit]);

        $consecutivos = collect($facturas)->pluck('consecutivo')->toArray();
        $intereses = InteresesCartera::whereIn('consecutivo', $consecutivos)->get();

        foreach ($facturas as &$factura) {
            $interes = $intereses->firstWhere('consecutivo', $factura->consecutivo);
            $factura->interes_acumulado = $interes ? $interes->valor_acumulado_interes : 0;
        }

        return response()->json([
            'facturas' => $facturas,
            'estado'   => true,
            'mensaje'  => 'Se retornan las facturas pendientes de pago',
        ], 200);
    }

    public function recuadoPresupuesto(Request $request)
    {
        $cedula          = trim((string) $request->asesor);
        $categoria_asesor = strtolower(trim((string) $request->categoria_asesor));
        $periodo         = str_replace("-", "", (string) $request->periodo);

        // 1) Presupuesto(s) del asesor
        $dataPresupuestos = $this->obtenerPresupuestos($periodo, $cedula);

        // 2) Totales base
        $dataPresupuestos->each(function ($dataPresupuesto) use ($periodo) {
            $dataPresupuesto->total_recaudado         = (float) $this->calcularTotalRecaudado($periodo, $dataPresupuesto->cedula);
            $dataPresupuesto->total_recaudado_diez    = (float) $this->calcularTotalRecaudadoDiez($periodo, $dataPresupuesto->cedula);
            $dataPresupuesto->total_recaudado_contado = (float) $this->calcularTotalRecaudadoContado($periodo, $dataPresupuesto->cedula);
            $dataPresupuesto->porcentaje_comision     = 0;
            $dataPresupuesto->comisiones              = 0;
        });

        // 3) Recaudo por días (base comisión)
        $recuadoPorDiasCartera = $this->recuadoPorDiasCartera($periodo, $cedula);

        // base comisión = total recaudo por dias SIN flete
        $baseComision = (float) data_get($recuadoPorDiasCartera, 'totales.total_recaudo_dias_sin_flete', 0);
        $baseComision = round($baseComision);

        // 4) Efectividad -> factor
        $efectividad = (float) $this->porcentajeClientesImpactadosCredito($periodo, $cedula);

        $factor = 0.0;
        if ($efectividad >= 60) {
            $factor = 1.10;
        } elseif ($efectividad >= 55) {
            $factor = 1.05;
        } elseif ($efectividad > 50) {
            $factor = 1.00;
        } elseif ($efectividad > 45) {
            $factor = 0.90;
        } elseif ($efectividad > 39) {
            $factor = 0.80;
        } else {
            $factor = 0.0;
        }

        foreach ($dataPresupuestos as $dataPresupuesto) {

            $totalPresupuesto = (float) ($dataPresupuesto->total_presupuesto ?? 0);

            if ($totalPresupuesto > 0) {
                // presupuesto sin IVA
                $dataPresupuesto->total_presupuesto = $totalPresupuesto / 1.19;

                $dataPresupuesto->cumplimiento = ceil(
                    (
                        ((float) $dataPresupuesto->total_recaudado / (float) $dataPresupuesto->total_presupuesto) * 100
                    ) * 10
                ) / 10;
            } else {
                $dataPresupuesto->cumplimiento = 0;
            }

            // ✅ FIX: simplificado para master/senior (misma tabla)
            if (in_array($categoria_asesor, ['master', 'senior'], true)) {
                $dataPresupuesto->porcentaje_comision = $this->porcentajeComisionPorCumplimiento((float) $dataPresupuesto->cumplimiento);

                $comisionCalculada = round($baseComision * (float) $dataPresupuesto->porcentaje_comision);
                $dataPresupuesto->comisiones = (int) round($comisionCalculada * $factor);
            } else {
                $dataPresupuesto->porcentaje_comision = 0;
                $dataPresupuesto->comisiones = 0;
            }

            // Debug opcional
            $dataPresupuesto->baseComision = $baseComision;
            $dataPresupuesto->efectividad_ventas = $efectividad;
            $dataPresupuesto->factor_efectividad = $factor;
        }

        return response()->json([
            'categoria_asesor'      => $categoria_asesor,
            'recaudoCartera'        => $dataPresupuestos,
            'baseComision'          => $baseComision,
            'recuadoPorDiasCartera' => $recuadoPorDiasCartera,
            'efectividad_ventas'    => $efectividad,
            'factor_efectividad'    => $factor,
            'estado'                => true,
            'mensaje'               => 'Se retornan las facturas pendientes de pago',
        ], 200);
    }

    // ✅ helper
    private function porcentajeComisionPorCumplimiento(float $cumplimiento): float
    {
        if ($cumplimiento >= 90) return 0.00600;
        if ($cumplimiento >= 85) return 0.00570;
        if ($cumplimiento >= 80) return 0.00540;
        return 0.0;
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
            ->where('presupuesto_recaudo.periodo', $periodo)
            ->where('users.cedula', '=', $cedula)
            ->where('presupuesto_recaudo.estado', '1')
            // ✅ FIX: agregar categoria_asesor al GROUP BY para evitar ONLY_FULL_GROUP_BY
            ->groupBy(
                'users.cedula',
                'presupuesto_recaudo.periodo',
                'users.name',
                'users.email',
                'users.codigo_asesor',
                'users.categoria_asesor'
            )
            ->get();
    }

    private function calcularTotalRecaudado($periodo, $cedula)
    {
        $infoPresupuestos = PresupuestoRecaudo::select('prefijo', 'consecutivo', 'cond_pago')
            ->where('estado', 1)
            ->where('periodo', $periodo)
            ->where('asesor', $cedula)
            ->get();

        if ($infoPresupuestos->isEmpty()) {
            return 0;
        }

        $prefijos = $infoPresupuestos->pluck('prefijo')->toArray();
        $consecutivos = $infoPresupuestos->pluck('consecutivo')->toArray();

        $totalCreditos = DB::connection('sqlsrv')->table('UnoEE.dbo.BI_T351_1')
            ->where('compañia', 3)
            ->where('id_periodo', $periodo)
            ->whereIn('id_tipo_docto', [
                'FCF','FCR','FRC','NCD',
                'R01','R02','R03','R04','R05','R06','R07','R08','R09',
                'R10','R11','R12','R13','R14','R15','R16','R17','R18','R19','R20','R21','R22',
                'R23','R24','R25','R26','R27','R28','R29','R30','R31','R32','R33','R34','R35',
                'R36','R37','R38','R39','R40','R41','R42','R43','R44','R45','R46','R47','R48',
                'R49','R50'
            ])
            ->whereIn('id_tipo_docto_cruce', $prefijos)
            ->whereIn('nro_docto_cruce', $consecutivos)
            ->where('parametro_biable', 3)
            ->where('creditos', '>', 0)
            ->sum('creditos');

        return ($totalCreditos / 1.19) ?? 0;
    }

    private function calcularTotalRecaudadoDiez($periodo, $cedula)
    {
        $infoPresupuestos = PresupuestoRecaudo::select('prefijo', 'consecutivo', 'cond_pago')
            ->where('estado', 1)
            ->where('periodo', $periodo)
            ->where('asesor', $cedula)
            ->get();

        if ($infoPresupuestos->isEmpty()) {
            return 0;
        }

        $totalCreditosDiez = DB::connection('sqlsrv')->table('UnoEE.dbo.BI_T351_1')
            ->where('compañia', 3)
            ->where('id_periodo', $periodo)
            ->whereIn('id_tipo_docto', [
                'FCF','FCR','FRC','NCD',
                'R01','R02','R03','R04','R05','R06','R07','R08','R09',
                'R10','R11','R12','R13','R14','R15','R16','R17','R18','R19','R20','R21','R22',
                'R23','R24','R25','R26','R27','R28','R29','R30','R31','R32','R33','R34','R35',
                'R36','R37','R38','R39','R40','R41','R42','R43','R44','R45','R46','R47','R48',
                'R49','R50','R51','R52','R53'
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

        if ($infoPresupuestos->isEmpty()) {
            return 0;
        }

        $totalCreditosContado = DB::connection('sqlsrv')->table('UnoEE.dbo.BI_T351_1')
            ->where('compañia', 3)
            ->where('id_periodo', $periodo)
            ->whereIn('id_tipo_docto', [
                'FCF','FCR','FRC','NCD',
                'R01','R02','R03','R04','R05','R06','R07','R08','R09',
                'R10','R11','R12','R13','R14','R15','R16','R17','R18','R19','R20','R21','R22',
                'R23','R24','R25','R26','R27','R28','R29','R30','R31','R32','R33','R34','R35',
                'R36','R37','R38','R39','R40','R41','R42','R43','R44','R45','R46','R47','R48',
                'R49','R50'
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
        $cedula  = $asesor;

        $resp = $this->recuadoPorDias($cedula, $periodo);

        $rows = collect(data_get($resp, 'data_asesores', []))->values();

        $totales = [
            'total_recaudo_dias'           => (float) data_get($resp, 'totales.total_recaudo_dias', 0),
            'total_recaudo_dias_sin_flete' => (float) data_get($resp, 'totales.total_recaudo_dias_sin_flete', 0),
            'total_comision_dias'          => (float) data_get($resp, 'totales.total_comision_dias', 0),
        ];

        return [
            'rows'    => $rows,
            'totales' => $totales,
        ];
    }

    public function recuadoPorDias($cedula, $periodo)
    {
        $data_asesores = User::select(
            DB::raw('RTRIM(users.name) as name'),
            'users.email',
            'users.cedula',
            'users.codigo_asesor',
            'users.categoria_asesor',

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

            DB::raw('0 as recaudo_46_65'),
            DB::raw('0 as recaudo_46_a_65_sin_flete'),
            DB::raw('0 as porcentaje_46_a_65'),
            DB::raw('0 as comision_46_a_65'),

            DB::raw('0 as recaudo_66_80'),
            DB::raw('0 as recaudo_66_a_80_sin_flete'),
            DB::raw('0 as porcentaje_66_a_80'),
            DB::raw('0 as comision_66_a_80'),

            DB::raw('0 as recaudo_mayor_81'),
            DB::raw('0 as recaudo_mayor_81_sin_flete'),
            DB::raw('0 as porcentaje_mayor_a_81'),

            DB::raw('0 as porcentaje_flete'),
            DB::raw('0 as comision_a_pagar')
        )
            ->where('cedula', '=', $cedula)
            ->get();

        $terceros_vendedores = $data_asesores->pluck('cedula')->map(fn($x) => trim((string)$x))->toArray();

        if (empty($terceros_vendedores)) {
            return [
                'data_asesores' => $data_asesores,
                'totales' => [
                    'total_recaudo_dias' => 0,
                    'total_recaudo_dias_sin_flete' => 0,
                    'total_comision_dias' => 0,
                ],
            ];
        }

        // ✅ FIX: SQL corregido (sin coma final / alias correcto creditos_46_65)
        $placeholders = implode(',', array_fill(0, count($terceros_vendedores), '?'));

        $sqlRecaudos = "
            WITH base AS (
                SELECT
                    RTRIM(t351.tercero_vend) AS tercero_vend,
                    DATEDIFF(
                        DAY,
                        TRY_CONVERT(date, t351.fecha_docto_cruce, 112),
                        TRY_CONVERT(date, t351.fecha_recaudo, 112)
                    ) AS dias,

                    ISNULL(t351.creditos, 0) AS creditos,
                    ISNULL(t461f.f461_vlr_imp, 0) AS vlr_imp,

                    CASE
                        WHEN ISNULL(t461f.f461_vlr_imp, 0) = 0
                            THEN ISNULL(t351.creditos, 0)
                        ELSE
                            ISNULL(t351.creditos, 0) / 1.19
                    END AS valor_ajustado

                FROM [UnoEE].[dbo].[BI_T351_1] t351

                LEFT JOIN [UnoEE].[dbo].[BI_T461] bi461
                    ON  bi461.[f_id_tipo_docto] = t351.[id_tipo_docto_cruce]
                    AND bi461.[f_nrodocto]      = t351.[nro_docto_cruce]
                    AND bi461.[f_id_cia]        = 3
                    AND bi461.[f_parametro_biable] = 3

                LEFT JOIN [UnoEE].[dbo].[t350_co_docto_contable] t350
                    ON  t350.f350_id_cia        = 3
                    AND t350.f350_id_tipo_docto = t351.id_tipo_docto_cruce
                    AND t350.f350_consec_docto  = t351.nro_docto_cruce

                LEFT JOIN [UnoEE].[dbo].[t461_cm_docto_factura_venta] t461f
                    ON  t461f.f461_id_cia      = 3
                    AND t461f.f461_rowid_docto = t350.f350_rowid

                WHERE
                    t351.[compañia] = 3
                    AND t351.[parametro_biable] = 3
                    AND t351.[id_periodo] = ?
                    AND t351.[id_tipo_docto_cruce] IN ('FVM')
                    AND t351.[id_tipo_docto] IN (
                        'FCF','FCR','FRC','NCD','R01','R02','R03','R04','R05','R06','R07','R08','R09','R10','R11','R12','R13','R14','R15',
                        'R16','R17','R18','R19','R20','R21','R22','R23','R24','R25','R26','R27','R28','R29','R30','R31','R32','R33','R34','R35',
                        'R36','R37','R38','R39','R40','R41','R42','R43','R44','R45','R46','R47','R48','R49','R50'
                    )
                    AND bi461.[f_id_cia] = 3
                    AND bi461.[f_parametro_biable] = 3
                    AND RTRIM(t351.[tercero_vend]) IN ($placeholders)
            )
            SELECT
                tercero_vend,
                SUM(CASE WHEN dias BETWEEN -100 AND 15 THEN valor_ajustado ELSE 0 END) AS creditos_1_15,
                SUM(CASE WHEN dias BETWEEN 16 AND 30  THEN valor_ajustado ELSE 0 END) AS creditos_16_30,
                SUM(CASE WHEN dias BETWEEN 31 AND 45  THEN valor_ajustado ELSE 0 END) AS creditos_31_45,
                SUM(CASE WHEN dias BETWEEN 46 AND 65  THEN valor_ajustado ELSE 0 END) AS creditos_46_65,
                SUM(CASE WHEN dias BETWEEN 66 AND 80  THEN valor_ajustado ELSE 0 END) AS creditos_66_80,
                SUM(CASE WHEN dias >= 81            THEN valor_ajustado ELSE 0 END) AS creditos_mayor_81
            FROM base
            GROUP BY tercero_vend;
        ";

        $bindings = array_merge([$periodo], $terceros_vendedores);
        $recaudos = DB::connection('sqlsrv')->select($sqlRecaudos, $bindings);
        $recaudos = collect($recaudos)->keyBy('tercero_vend');

        foreach ($data_asesores as $data_asesor) {
            $key = trim((string)$data_asesor->cedula);
            if (isset($recaudos[$key])) {
                $recaudoGen = $recaudos[$key];
                $data_asesor->recaudo_1_15      = (int) round($recaudoGen->creditos_1_15 ?? 0);
                $data_asesor->recaudo_16_30     = (int) round($recaudoGen->creditos_16_30 ?? 0);
                $data_asesor->recaudo_31_45     = (int) round($recaudoGen->creditos_31_45 ?? 0);
                // ✅ FIX: ahora sí coincide con el alias del SQL
                $data_asesor->recaudo_46_65     = (int) round($recaudoGen->creditos_46_65 ?? 0);
                $data_asesor->recaudo_66_80     = (int) round($recaudoGen->creditos_66_80 ?? 0);
                $data_asesor->recaudo_mayor_81  = (int) round($recaudoGen->creditos_mayor_81 ?? 0);
            }
        }

        // ventana para % flete (mes-2 a mes-1)
        $fecha = Carbon::createFromFormat('Ym', $periodo);
        $mesAnterior  = $fecha->copy()->subMonth()->format('Ym');
        $mesTresMenos = $fecha->copy()->subMonths(2)->format('Ym');

        foreach ($data_asesores as $data_asesor) {

            $cat = strtolower(trim((string)($data_asesor->categoria_asesor ?? '')));

            $data_asesor->porcentaje_1_a_15   = 0;
            $data_asesor->porcentaje_16_a_30  = 0;
            $data_asesor->porcentaje_31_a_45  = 0;
            $data_asesor->porcentaje_46_a_65  = 0;
            $data_asesor->porcentaje_66_a_80  = 0;
            $data_asesor->porcentaje_mayor_81 = 0;

            if ($cat === 'master') {
                $data_asesor->porcentaje_1_a_15   = 0.0041;
                $data_asesor->porcentaje_16_a_30  = 0.0038;
                $data_asesor->porcentaje_31_a_45  = 0.0035;
                $data_asesor->porcentaje_46_a_65  = 0.0032;
                $data_asesor->porcentaje_66_a_80  = 0.0029;
                $data_asesor->porcentaje_mayor_81 = 0;
            } elseif ($cat === 'senior') {
                $data_asesor->porcentaje_1_a_15   = 0.0028;
                $data_asesor->porcentaje_16_a_30  = 0.0025;
                $data_asesor->porcentaje_31_a_45  = 0.0022;
                $data_asesor->porcentaje_46_a_65  = 0.0019;
                $data_asesor->porcentaje_66_a_80  = 0.0016;
                $data_asesor->porcentaje_mayor_81 = 0;
            }

            $recaudoFletes = DB::connection('sqlsrv')->select(
                "
                SELECT
                    RTRIM([f_vendedor]) f_vendedor,
                    ROUND(
                        (
                            CONVERT(decimal(18,2), SUM(CASE WHEN [f_ref_item] IN ('ZLE99999', 'ZLE99998') THEN [f_valor_sub_local] ELSE 0 END))
                            /
                            NULLIF(CONVERT(decimal(18,2), SUM(CASE WHEN [f_ref_item] NOT IN ('ZLE99999', 'ZLE99998', '101999999', '0013686', '0013694', '0013695', '0013822') THEN [f_valor_sub_local] ELSE 0 END)), 0)
                        ) * 100
                    , 2) AS porcentaje
                FROM [UnoEE].[dbo].[BI_T461_1]
                WHERE
                    [f_id_cia] = 3
                    AND f_parametro_biable = 3
                    AND RTRIM([f_vendedor]) = ?
                    AND f_periodo BETWEEN ? AND ?
                GROUP BY [f_vendedor]
                ",
                [trim((string)$data_asesor->cedula), $mesTresMenos, $mesAnterior]
            );

            $data_asesor->porcentaje_flete = 0;
            if (!empty($recaudoFletes)) {
                $data_asesor->porcentaje_flete = (float)($recaudoFletes[0]->porcentaje ?? 0);
            }

            $pf = (float)($data_asesor->porcentaje_flete ?? 0);

            $data_asesor->recaudo_1_a_15_sin_flete   = (int) round($data_asesor->recaudo_1_15     - (($data_asesor->recaudo_1_15     / 100) * $pf));
            $data_asesor->recaudo_16_a_30_sin_flete  = (int) round($data_asesor->recaudo_16_30    - (($data_asesor->recaudo_16_30    / 100) * $pf));
            $data_asesor->recaudo_31_a_45_sin_flete  = (int) round($data_asesor->recaudo_31_45    - (($data_asesor->recaudo_31_45    / 100) * $pf));
            $data_asesor->recaudo_46_a_65_sin_flete  = (int) round($data_asesor->recaudo_46_65    - (($data_asesor->recaudo_46_65    / 100) * $pf));
            $data_asesor->recaudo_66_a_80_sin_flete  = (int) round($data_asesor->recaudo_66_80    - (($data_asesor->recaudo_66_80    / 100) * $pf));
            $data_asesor->recaudo_mayor_81_sin_flete = (int) round($data_asesor->recaudo_mayor_81 - (($data_asesor->recaudo_mayor_81 / 100) * $pf));

            $data_asesor->comision_1_a_15 = $data_asesor->porcentaje_1_a_15 != 0
                ? (int) round($data_asesor->recaudo_1_a_15_sin_flete * $data_asesor->porcentaje_1_a_15) : 0;

            $data_asesor->comision_16_a_30 = $data_asesor->porcentaje_16_a_30 != 0
                ? (int) round($data_asesor->recaudo_16_a_30_sin_flete * $data_asesor->porcentaje_16_a_30) : 0;

            $data_asesor->comision_31_a_45 = $data_asesor->porcentaje_31_a_45 != 0
                ? (int) round($data_asesor->recaudo_31_a_45_sin_flete * $data_asesor->porcentaje_31_a_45) : 0;

            $data_asesor->comision_46_a_65 = $data_asesor->porcentaje_46_a_65 != 0
                ? (int) round($data_asesor->recaudo_46_a_65_sin_flete * $data_asesor->porcentaje_46_a_65) : 0;

            $data_asesor->comision_66_a_80 = $data_asesor->porcentaje_66_a_80 != 0
                ? (int) round($data_asesor->recaudo_66_a_80_sin_flete * $data_asesor->porcentaje_66_a_80) : 0;

            $data_asesor->comision_mayor_81 = $data_asesor->porcentaje_mayor_81 != 0
                ? (int) round($data_asesor->recaudo_mayor_81_sin_flete * $data_asesor->porcentaje_mayor_81) : 0;

            $data_asesor->comision_a_pagar =
                $data_asesor->comision_1_a_15 +
                $data_asesor->comision_16_a_30 +
                $data_asesor->comision_31_a_45 +
                $data_asesor->comision_46_a_65 +
                $data_asesor->comision_66_a_80 +
                $data_asesor->comision_mayor_81;
        }

        $totales = [
            'total_recaudo_dias' => (int) (
                ($data_asesores->sum('recaudo_1_15') ?? 0) +
                ($data_asesores->sum('recaudo_16_30') ?? 0) +
                ($data_asesores->sum('recaudo_31_45') ?? 0) +
                ($data_asesores->sum('recaudo_46_65') ?? 0) +
                ($data_asesores->sum('recaudo_66_80') ?? 0) +
                ($data_asesores->sum('recaudo_mayor_81') ?? 0)
            ),
            'total_recaudo_dias_sin_flete' => (int) (
                ($data_asesores->sum('recaudo_1_a_15_sin_flete') ?? 0) +
                ($data_asesores->sum('recaudo_16_a_30_sin_flete') ?? 0) +
                ($data_asesores->sum('recaudo_31_a_45_sin_flete') ?? 0) +
                ($data_asesores->sum('recaudo_46_a_65_sin_flete') ?? 0) +
                ($data_asesores->sum('recaudo_66_a_80_sin_flete') ?? 0) +
                ($data_asesores->sum('recaudo_mayor_81_sin_flete') ?? 0)
            ),
            'total_comision_dias' => (int) ($data_asesores->sum('comision_a_pagar') ?? 0),
        ];

        return [
            'data_asesores' => $data_asesores,
            'totales' => $totales,
        ];
    }

    public function porcentajeClientesImpactadosCredito(string $periodo, string $asesorCedula): float
    {
        $periodo = preg_replace('/\D/', '', $periodo);
        if (strlen($periodo) !== 6) {
            return 0.0;
        }

        $year  = (int) substr($periodo, 0, 4);
        $month = (int) substr($periodo, 4, 2);

        $asesorCedula = trim($asesorCedula);

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

        $ventaRow = DB::connection('sqlsrv')->selectOne(
            "SELECT COUNT(DISTINCT t461.f461_rowid_tercero_fact) AS clientes_con_venta
             FROM t461_cm_docto_factura_venta t461
             INNER JOIN t201_mm_clientes t201
               ON t201.f201_rowid_tercero = t461.f461_rowid_tercero_fact
             WHERE t461.f461_id_cia = 3
                AND YEAR(t461.f461_id_fecha) = ?
                AND MONTH(t461.f461_id_fecha) = ?
                AND t461.[f461_id_concepto] = '501'
                AND t461.f461_id_clase_docto = '523'
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