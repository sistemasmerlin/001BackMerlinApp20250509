<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PresupuestoComercial;
use App\Models\PresupuestoRecaudo;
use App\Models\ReporteVisita;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class ComisionesController extends Controller
{

    public function indexVentas(Request $request)
    {
        $periodo = $request->periodo;

        $asesores = User::role('asesor')
            ->whereNotNull('codigo_asesor')
            ->get();

        $resultado = [];

        foreach ($asesores as $usuario) {
            $codigoAsesor = trim($usuario->codigo_asesor);
            $categoriaAsesor = strtolower(trim($usuario->categoria_asesor ?? ''));

            $presupuestos = PresupuestoComercial::query()
                ->periodo($periodo)
                ->asesor($codigoAsesor)
                ->get();

            $presupuestoLlantas = (float) $presupuestos->where('categoria', 'llantas')->sum('presupuesto');
            $presupuestoRepuestos = (float) $presupuestos->where('categoria', 'repuestos')->sum('presupuesto');
            $presupuestoPirelli = (float) $presupuestos->where('categoria', 'pirelli')->sum('presupuesto');
            $presupuestoTotal = (float) $presupuestos->where('categoria', 'total')->sum('presupuesto');

            $sqlVentasCat = <<<SQL
                SELECT 
                    RTRIM([f_cod_vendedor]) as vendedor,
                    RTRIM([f_cod_vendedor]) as cod_vendedor,

                    CONVERT(int, SUM(CASE 
                        WHEN t106.f106_descripcion IN (
                            'CST TIRES','CST ATV','CST E-SCOOTER','RINOVA TIRES',
                            'HAKUBA - ARMOR - WDT','WDT TUBE','WDT BIKE','WDT E-SCOOTER',
                            'FORERUNNER','RINOVA ATV','WDT','WORCRAFT'
                        )
                        THEN [f_cant_base] ELSE 0 END)) AS llantas,

                    CONVERT(int, SUM(CASE 
                        WHEN t106.f106_descripcion IN ('PIRELLI','PIRELLI RADIAL')
                        THEN [f_cant_base] ELSE 0 END)) AS pirelli,
                    
                    CONVERT(int, SUM(CASE 
                        WHEN t106.f106_descripcion IN ('PIRELLI','PIRELLI RADIAL')
                        THEN [f_valor_sub_local] ELSE 0 END)) AS pirelli_dinero,

                    CONVERT(int, SUM(CASE 
                        WHEN t106.f106_descripcion IN (
                            'KOYO','PFI','RNV','BATERIAS RINOVA','NARVA','RINOVA LIGHTING',
                            'RINOVA LIGHTING LED','RINOVA - GOOD TUBE', 'RINOVA PARTS'
                        )
                        THEN [f_valor_sub_local] ELSE 0 END)) AS repuestos,

                    CONVERT(int, SUM(CASE 
                        WHEN t106.f106_descripcion IN (
                            'KOYO','PFI','RNV','BATERIAS RINOVA','NARVA','RINOVA LIGHTING',
                            'RINOVA LIGHTING LED','RINOVA - GOOD TUBE', 'RINOVA PARTS',
                            'PIRELLI','PIRELLI RADIAL','CST TIRES','CST ATV','CST E-SCOOTER',
                            'HAKUBA - ARMOR - WDT','WDT TUBE','WDT BIKE','WDT E-SCOOTER',
                            'FORERUNNER','RINOVA ATV','WDT','RINOVA TIRES','WORCRAFT'
                        )
                        THEN [f_valor_sub_local] ELSE 0 END)) AS total,

                    CONVERT(int, SUM(CASE 
                        WHEN t106.f106_descripcion IN (
                            'KOYO','PFI','RNV','BATERIAS RINOVA','NARVA','RINOVA LIGHTING',
                            'RINOVA LIGHTING LED','RINOVA - GOOD TUBE', 'RINOVA PARTS',
                            'CST TIRES','CST ATV','CST E-SCOOTER',
                            'HAKUBA - ARMOR - WDT','WDT TUBE','WDT BIKE','WDT E-SCOOTER',
                            'FORERUNNER','RINOVA ATV','WDT','RINOVA TIRES','WORCRAFT'
                        )
                        THEN [f_valor_sub_local] ELSE 0 END)) AS total_sin_pirelli
                FROM BI_T461_1 AS t461_1
                LEFT JOIN [t120_mc_items] AS t120 
                    ON t120.[f120_rowid] = t461_1.[f_rowid_item]
                LEFT JOIN [t125_mc_items_criterios] AS t125 
                    ON t125.[f125_rowid_item] = t120.[f120_rowid]
                LEFT JOIN [t105_mc_criterios_item_planes] AS t105 
                    ON t105.[f105_id] = t125.[f125_id_plan]
                LEFT JOIN [t106_mc_criterios_item_mayores] AS t106 
                    ON t106.[f106_id] = t125.[f125_id_criterio_mayor]
                WHERE 
                    t461_1.[f_id_cia] = 3
                    AND t461_1.[f_co] = '003'
                    AND t461_1.[f_parametro_biable] = 3
                    AND t106.[f106_id_plan] = '003'
                    AND t106.[f106_id_cia] = '3'
                    AND t120.[f120_id_cia] = '3'
                    AND t125.[f125_id_plan] = '003'
                    AND t125.[f125_id_cia] = '3'
                    AND t105.[f105_id_cia] = '3'
                    AND t106.[f106_descripcion] NOT IN ('ZFLETE','NO APLICA')
                    AND t461_1.f_periodo = ?
                    AND [f_cod_vendedor] = ?
                GROUP BY [f_vendedor], [f_cod_vendedor]
            SQL;

            $dataVentasCat = DB::connection('sqlsrv')->select($sqlVentasCat, [$periodo, $codigoAsesor]);

            $ventasLlantas = 0;
            $ventasRepuestos = 0;
            $ventasPirelli = 0;
            $ventasPirelliDinero = 0;
            $ventasTotal = 0;
            $ventasTotalSinPirelli = 0;

            if (!empty($dataVentasCat)) {
                $filaVenta = $dataVentasCat[0];

                $ventasLlantas = (float) ($filaVenta->llantas ?? 0);
                $ventasRepuestos = (float) ($filaVenta->repuestos ?? 0);
                $ventasPirelli = (float) ($filaVenta->pirelli ?? 0);
                $ventasPirelliDinero = (float) ($filaVenta->pirelli_dinero ?? 0);
                $ventasTotal = (float) ($filaVenta->total ?? 0);
                $ventasTotalSinPirelli = (float) ($filaVenta->total_sin_pirelli ?? 0);
            }

            $cumplimientoLlantas = $this->calcularCumplimiento($ventasLlantas, $presupuestoLlantas);
            $cumplimientoRepuestos = $this->calcularCumplimiento($ventasRepuestos, $presupuestoRepuestos);
            $cumplimientoPirelli = $this->calcularCumplimiento($ventasPirelli, $presupuestoPirelli);
            $cumplimientoTotal = $this->calcularCumplimiento($ventasTotal, $presupuestoTotal);

            if ($categoriaAsesor === 'master') {
                $rangosRepuestos = [
                    ['min' => 0, 'max' => 79.99, 'factor' => 0.0000],
                    ['min' => 80, 'max' => 84.99, 'factor' => 0.0058],
                    ['min' => 85, 'max' => 89.99, 'factor' => 0.0062],
                    ['min' => 90, 'max' => 94.99, 'factor' => 0.0066],
                    ['min' => 95, 'max' => 99.99, 'factor' => 0.0070],
                    ['min' => 100, 'max' => 109.99, 'factor' => 0.0074],
                    ['min' => 110, 'max' => 119.99, 'factor' => 0.0079],
                    ['min' => 120, 'max' => 1000, 'factor' => 0.0084],
                ];

                $rangosLlantas = [
                    ['min' => 0, 'max' => 79.99, 'factor' => 0.0000],
                    ['min' => 80, 'max' => 84.99, 'factor' => 0.0045],
                    ['min' => 85, 'max' => 89.99, 'factor' => 0.0049],
                    ['min' => 90, 'max' => 94.99, 'factor' => 0.0053],
                    ['min' => 95, 'max' => 99.99, 'factor' => 0.0057],
                    ['min' => 100, 'max' => 109.99, 'factor' => 0.0061],
                    ['min' => 110, 'max' => 119.99, 'factor' => 0.0066],
                    ['min' => 120, 'max' => 1000, 'factor' => 0.0071],
                ];
            } else {
                $rangosRepuestos = [
                    ['min' => 0, 'max' => 79.99, 'factor' => 0.0000],
                    ['min' => 80, 'max' => 84.99, 'factor' => 0.0034],
                    ['min' => 85, 'max' => 89.99, 'factor' => 0.0038],
                    ['min' => 90, 'max' => 94.99, 'factor' => 0.0042],
                    ['min' => 95, 'max' => 99.99, 'factor' => 0.0046],
                    ['min' => 100, 'max' => 109.99, 'factor' => 0.0050],
                    ['min' => 110, 'max' => 119.99, 'factor' => 0.0055],
                    ['min' => 120, 'max' => 1000, 'factor' => 0.0060],
                ];

                $rangosLlantas = [
                    ['min' => 0, 'max' => 79.99, 'factor' => 0.0000],
                    ['min' => 80, 'max' => 84.99, 'factor' => 0.0025],
                    ['min' => 85, 'max' => 89.99, 'factor' => 0.0029],
                    ['min' => 90, 'max' => 94.99, 'factor' => 0.0033],
                    ['min' => 95, 'max' => 99.99, 'factor' => 0.0037],
                    ['min' => 100, 'max' => 109.99, 'factor' => 0.0041],
                    ['min' => 110, 'max' => 119.99, 'factor' => 0.0046],
                    ['min' => 120, 'max' => 1000, 'factor' => 0.0051],
                ];
            }

            $factorLlantas = $this->buscarFactorPorCumplimiento($cumplimientoLlantas, $rangosLlantas);
            $factorRepuestos = $this->buscarFactorPorCumplimiento($cumplimientoRepuestos, $rangosRepuestos);

            $comisionLlantas = round($ventasTotalSinPirelli * $factorLlantas, 2);
            $comisionRepuestos = round($ventasTotalSinPirelli * $factorRepuestos, 2);
            $comisionPirelli = round(($ventasPirelliDinero / 100) * 1.5, 2);
            $comisionTotal = round($comisionLlantas + $comisionRepuestos + $comisionPirelli, 2);

            $resultado[] = [
                'user_id' => $usuario->id,
                'nombre_asesor' => $usuario->name,
                'codigo_asesor' => $codigoAsesor,
                'categoria_asesor' => $categoriaAsesor,
                'periodo' => $periodo,

                'llantas' => [
                    'presupuesto' => $presupuestoLlantas,
                    'ventas' => $ventasLlantas,
                    'cumplimiento' => $cumplimientoLlantas,
                    'factor' => $factorLlantas,
                    'comision' => $comisionLlantas,
                ],

                'repuestos' => [
                    'presupuesto' => $presupuestoRepuestos,
                    'ventas' => $ventasRepuestos,
                    'cumplimiento' => $cumplimientoRepuestos,
                    'factor' => $factorRepuestos,
                    'comision' => $comisionRepuestos,
                ],

                'pirelli' => [
                    'presupuesto' => $presupuestoPirelli,
                    'ventas_unidades' => $ventasPirelli,
                    'ventas_dinero' => $ventasPirelliDinero,
                    'cumplimiento' => $cumplimientoPirelli,
                    'comision' => $comisionPirelli,
                ],

                'total' => [
                    'presupuesto' => $presupuestoTotal,
                    'ventas' => $ventasTotal,
                    'ventas_total_sin_pirelli' => $ventasTotalSinPirelli,
                    'cumplimiento' => $cumplimientoTotal,
                    'comision' => $comisionTotal,
                ],
            ];
        }

        return $resultado;
    }

    private function calcularCumplimiento(float $ventas, float $presupuesto): int
    {
        return $presupuesto > 0
            ? (int) ceil(($ventas / $presupuesto) * 100)
            : 0;
    }

    private function buscarFactorPorCumplimiento(float $cumplimiento, array $rangos): float
    {
        foreach ($rangos as $rango) {
            if ($cumplimiento >= $rango['min'] && $cumplimiento <= $rango['max']) {
                return (float) $rango['factor'];
            }
        }

        return 0;
    }

    public function clientesImpactados(Request $request, $asesor, $periodo)
    {
        $periodo = preg_replace('/\D/', '', $periodo);
        if (strlen($periodo) !== 6) {
            return response()->json(['message' => 'Periodo inválido (use YYYYMM)'], 422);
        }

        $year  = (int) substr($periodo, 0, 4);
        $month = (int) substr($periodo, 4, 2);

        //  $asesor = str_pad(preg_replace('/\D/', '', (string) $asesor), 4, '0', STR_PAD_LEFT);

        // $datosAsesor = DB::connection('sqlsrv')
        //     ->selectOne("SELECT t210.f210_id as codigo_asesor,
        //     t200.f200_nit as codigo_tercero
        //     from [t200_mm_terceros] AS t200
        //     INNER JOIN [t210_mm_vendedores]AS t210
        //     ON t210.[f210_rowid_tercero] = t200.f200_rowid
        //     AND t200.f200_id_cia = 3
        //     AND t210.f210_id = $asesor");

        // if ($datosAsesor) {
        //     $codigo_tercero = $datosAsesor->codigo_tercero;
        //     $codigo_asesor = $datosAsesor->codigo_asesor;
        // }

        $codigo_asesor  = $asesor;
        $codigo_tercero = $asesor;

        //return  $codigo_tercero;
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
            [$codigo_asesor]
        );

        $totalCreditoRow = DB::connection('sqlsrv')->selectOne(
            "SELECT COUNT(DISTINCT t200.f200_nit) AS total_clientes
            FROM t200_mm_terceros t200
            LEFT JOIN t201_mm_clientes t201
                ON t200.f200_rowid = t201.f201_rowid_tercero
            WHERE t200.f200_id_cia = 3
                AND t201.f201_id_cia = 3
                AND t200.f200_ind_cliente = 1
                AND t200.f200_ind_estado = 1
                AND t201.f201_id_vendedor = ?
	            AND f201_id_cond_pago IN ('30D','10D','15D','30E')",
            [$codigo_asesor]
        );

        $totalContadoRow = DB::connection('sqlsrv')->selectOne(
            "SELECT COUNT(DISTINCT t200.f200_nit) AS total_clientes
            FROM t200_mm_terceros t200
            LEFT JOIN t201_mm_clientes t201
                ON t200.f200_rowid = t201.f201_rowid_tercero
            WHERE t200.f200_id_cia = 3
                AND t201.f201_id_cia = 3
                AND t200.f200_ind_cliente = 1
                AND t200.f200_ind_estado = 1
                AND t201.f201_id_vendedor = ?
	            AND f201_id_cond_pago NOT IN ('30D','10D','15D','30E')",
            [$codigo_asesor]
        );


        $ventaConPagoRow = DB::connection('sqlsrv')->select(
            "WITH totales AS (
                        SELECT 
                            f_condicion_pago,
                            SUM(f_valor_subtotal_local) AS total_por_condicion
                        FROM [BI_T461_1]
                        WHERE f_periodo = ?
                        AND f_vendedor = ?
                        AND f_parametro_biable = 3
                        AND f_ref_item NOT IN ('ZLE99998','ZLE99999') 
                        GROUP BY f_condicion_pago
                    )
                    SELECT 
                        ISNULL(f_condicion_pago, 'TOTAL GENERAL') AS condicion_pago,
                        SUM(total_por_condicion) AS total_por_condicion,
                        CASE 
                            WHEN f_condicion_pago IS NOT NULL THEN 
                                ROUND(SUM(total_por_condicion) * 100.0 / 
                                    SUM(SUM(total_por_condicion)) OVER (), 2)
                            ELSE 100.00
                        END AS porcentaje
                    FROM totales
                    GROUP BY ROLLUP(f_condicion_pago);
                    ",
            [$periodo, $codigo_tercero]
        );

        $ventasPeriodoRow = DB::connection('sqlsrv')->select(
            "SELECT 
                        t461_1.f_cliente_fact,
                        t200.f200_razon_social,
                        t461_1.f_id_tipo_docto AS tipo_docto,
                        t461_1.f_nrodocto AS consecutivo_docto,
                        t461_1.f_condicion_pago AS condicion_pago,
                        SUM(t461_1.f_valor_bruto_local)    AS total_bruto,
                        SUM(t461_1.f_valor_dscto_local)    AS total_descuento,
                        SUM(t461_1.f_valor_imp_local)      AS total_impuesto,
                        SUM(t461_1.f_valor_neto_local)     AS total_neto_local,
                        SUM(t461_1.f_valor_subtotal_local) AS total_subtotal,
                        t461_1.f_ciudad_desp,
                        MAX(t206.f206_descripcion) AS categoria
                    FROM [BI_T461_1] AS t461_1
                    LEFT JOIN [t200_mm_terceros] AS t200
                    ON t200.f200_nit = t461_1.f_cliente_fact
                    AND t200.f200_id_cia = 3
                    LEFT JOIN [t201_mm_clientes] AS t201
					ON t201.f201_rowid_tercero = t200.f200_rowid
					AND t201.[f201_id_sucursal] = t461_1.f_cliente_fact_suc
					AND t200.f200_id_cia = 3
					LEFT JOIN t207_mm_criterios_clientes t207 ON t207.f207_rowid_tercero = t201.f201_rowid_tercero
					AND t207.f207_id_sucursal = t201.f201_id_sucursal
					AND t207.f207_id_cia = t201.f201_id_cia
					AND t207.f207_id_plan_criterios = '005'
					LEFT JOIN  t206_mm_criterios_mayores t206 
					ON t206.f206_id_plan = t207.f207_id_plan_criterios
					AND t206.f206_id_cia = t207.f207_id_cia 
					AND t206.f206_id = t207.f207_id_criterio_mayor
                    WHERE t461_1.f_periodo = ?
                    AND t461_1.f_ref_item NOT IN ('ZLE99998','ZLE99999')
                    AND t461_1.f_parametro_biable = 3 
                    AND t461_1.f_cod_vendedor = ?
                    GROUP BY t461_1.f_cliente_fact, 
                    t461_1.f_ciudad_desp, t200.f200_razon_social, 
                    t461_1.f_condicion_pago, t461_1.f_nrodocto , 
                    t461_1.f_id_tipo_docto, t206.f206_descripcion,
					t201.[f201_id_sucursal], t461_1.f_cliente_fact_suc
                    ORDER BY t461_1.f_id_tipo_docto,t461_1.f_condicion_pago, t461_1.f_ciudad_desp ASC;
                    ",
            [$periodo, $codigo_asesor]
        );

        $ventaRow = DB::connection('sqlsrv')->selectOne(
            "SELECT COUNT(DISTINCT t461.f461_rowid_tercero_fact) AS clientes_con_venta
               FROM t461_cm_docto_factura_venta t461
               INNER JOIN t201_mm_clientes t201
                 ON t201.f201_rowid_tercero = t461.f461_rowid_tercero_fact
              WHERE t461.f461_id_cia = 3
                AND YEAR(t461.f461_id_fecha) = ?
                AND MONTH(t461.f461_id_fecha) = ?
                AND t201.f201_id_vendedor = ?",
            [$year, $month, $codigo_asesor]
        );

        $ventaCreditoRow = DB::connection('sqlsrv')->selectOne(
            "SELECT COUNT(DISTINCT t461.f461_rowid_tercero_fact) AS clientes_con_venta
               FROM t461_cm_docto_factura_venta t461
               INNER JOIN t201_mm_clientes t201
                 ON t201.f201_rowid_tercero = t461.f461_rowid_tercero_fact
              WHERE t461.f461_id_cia = 3
                AND YEAR(t461.f461_id_fecha) = ?
                AND MONTH(t461.f461_id_fecha) = ?
                AND t201.f201_id_vendedor = ?
                AND t461.[f461_id_concepto] = '501'
				AND t461.f461_id_clase_docto = '523'
	            AND f201_id_cond_pago IN ('30D','10D','15D','30E')",
            [$year, $month, $codigo_asesor]
        );

        $ventaContadoRow = DB::connection('sqlsrv')->selectOne(
            "SELECT COUNT(DISTINCT t461.f461_rowid_tercero_fact) AS clientes_con_venta
               FROM t461_cm_docto_factura_venta t461
               INNER JOIN t201_mm_clientes t201
                 ON t201.f201_rowid_tercero = t461.f461_rowid_tercero_fact
              WHERE t461.f461_id_cia = 3
                AND YEAR(t461.f461_id_fecha) = ?
                AND MONTH(t461.f461_id_fecha) = ?
                AND t201.f201_id_vendedor = ?
                AND t461.[f461_id_concepto] = '501'
	            AND f201_id_cond_pago NOT IN ('30D','10D','15D','30E')",
            [$year, $month, $codigo_asesor]
        );


        $clientesConVenta = DB::connection('sqlsrv')->select(
            "SELECT DISTINCT t200.f200_nit
               FROM t461_cm_docto_factura_venta t461
               INNER JOIN t201_mm_clientes t201
                 ON t201.f201_rowid_tercero = t461.f461_rowid_tercero_fact
                LEFT JOIN t200_mm_terceros t200 ON t200.f200_rowid = t201.f201_rowid_tercero
              WHERE t461.f461_id_cia = 3
                AND YEAR(t461.f461_id_fecha) = ?
                AND MONTH(t461.f461_id_fecha) = ?
                AND t201.f201_id_vendedor = ?",
            [$year, $month, $codigo_asesor]
        );

        $nitsVenta = collect($clientesConVenta)
            ->pluck('f200_nit')
            ->filter()
            ->map(fn($n) => trim((string)$n))
            ->unique()
            ->values();

        $clientesSinVentaCantidad = ReporteVisita::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('vendedor', $codigo_asesor)
            ->whereHas('motivos', fn($q) => $q->where('motivos_visita_id', '<>', '11'))
            ->when($nitsVenta->isNotEmpty(), fn($q) => $q->whereNotIn('nit', $nitsVenta))
            ->distinct('nit')
            ->count('nit');

        $impactadosNoVenta = ReporteVisita::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('vendedor', $codigo_asesor)
            ->whereHas('motivos', fn($q) => $q->where('motivos_visita_id', '<>', '11'))
            ->distinct('nit')
            ->count('nit');

        $impactadosNoVentaDetalle = ReporteVisita::with('motivos')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('vendedor', $codigo_asesor)
            ->whereHas('motivos', fn($q) => $q->where('motivos_visita_id', '<>', '11'))
            ->get();


        $total = (int)($totalRow->total_clientes ?? 0);
        $totalCredito = (int)($totalCreditoRow->total_clientes ?? 0);
        $totalContado = (int)($totalContadoRow->total_clientes ?? 0);
        $conVenta = (int)($ventaRow->clientes_con_venta ?? 0);
        $conCreditoVenta = (int)($ventaCreditoRow->clientes_con_venta ?? 0);
        $conContadoVenta = (int)($ventaContadoRow->clientes_con_venta ?? 0);
        $cumplimiento = $totalCredito > 0 ? round(($conCreditoVenta / $totalCredito) * 100, 2) : 0.0;

        $presupuesto = PresupuestoComercial::where('codigo_asesor', '=', $codigo_tercero)->where('periodo', '=', $periodo)->get();


        $marcasPresu = [
            'RINOVA TIRES'          => 'rinova_tires',
            'PIRELLI'               => 'pirelli',
            'CST TIRES'             => 'cst_tires',
            'CST ATV'               => 'cst_atv',
            'CST E-SCOOTER'         => 'cst_e_scooter',
            'HAKUBA - ARMOR'        => 'hakuba_armor',
            'KOYO'                  => 'koyo',
            'PFI'                   => 'pfi',
            'RNV'                   => 'rnv',
            'BATERIAS RINOVA'       => 'baterias_rinova',
            'NARVA'                 => 'narva',
            'RINOVA LIGHTING'       => 'rinova_lighting',
            'RINOVA LIGHTING LED'   => 'rinova_lighting_led',
            'RINOVA - GOOD TUBE'    => 'good_tube',
            'RINOVA PARTS'          => 'rinova_parts',
        ];



        $query = User::query()
            ->select('users.cedula', 'users.codigo_asesor', 'users.name')
            ->addSelect(DB::raw("'{$periodo}' as periodo"))
            ->selectSub(
                PresupuestoComercial::selectRaw('MAX(clasificacion_asesor)')
                    ->whereColumn('codigo_asesor', 'users.codigo_asesor')
                    ->where('periodo', $periodo),
                'tipo_asesor'
            )
            ->where('users.codigo_asesor', $codigo_asesor);

        foreach ($marcasPresu as $marca => $alias) {
            $query->withSum(
                ['presupuestosComerciales as ' . $alias => function ($q) use ($periodo, $marca) {
                    $q->where('periodo', $periodo)
                        ->where('marca', $marca);
                    // ->where('estado',1); // si tienes esta columna
                }],
                'presupuesto'
            );
        }

        $query
            ->withSum(['presupuestosComerciales as total_llantas' => function ($q) use ($periodo) {
                $q->where('periodo', $periodo)->where('categoria', 'llantas');
            }], 'presupuesto')
            ->withSum(['presupuestosComerciales as total_repuestos' => function ($q) use ($periodo) {
                $q->where('periodo', $periodo)->where('categoria', 'repuestos');
            }], 'presupuesto')
            ->withSum(['presupuestosComerciales as total_presupuesto' => function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            }], 'presupuesto');

        $data_asesores = $query->get();

        $sqlVentasPorMarca = <<<SQL
        SELECT 
            RTRIM([f_cod_vendedor])       as vendedor,        -- suele ser CÉDULA
            RTRIM([f_cod_vendedor])   as cod_vendedor,    -- suele ser CÓDIGO ASESOR
            RTRIM(t106.f106_descripcion) as marca,
            SUM([f_cant_base])        as cantidad,
            SUM([f_valor_sub_local])  as dinero
        FROM BI_T461_1 AS t461_1
        LEFT JOIN [t120_mc_items]               AS t120 ON t120.[f120_rowid] = t461_1.[f_rowid_item]
        LEFT JOIN [t125_mc_items_criterios]     AS t125 ON t125.[f125_rowid_item] = t120.[f120_rowid]
        LEFT JOIN [t105_mc_criterios_item_planes] AS t105 ON t105.[f105_id] = t125.[f125_id_plan]
        LEFT JOIN [t106_mc_criterios_item_mayores] AS t106 ON t106.[f106_id] = t125.[f125_id_criterio_mayor]
        WHERE 
            t461_1.[f_id_cia] = 3
            AND t461_1.[f_co] = '003'
            AND t461_1.[f_parametro_biable] = 3
            AND t106.[f106_id_plan] = '003'
            AND t106.[f106_id_cia] = '3'
            AND t120.[f120_id_cia] = '3'
            AND t125.[f125_id_plan] = '003'
            AND t125.[f125_id_cia] = '3'
            AND t105.[f105_id_cia] = '3'
            AND t106.[f106_descripcion] NOT IN ('ZFLETE','NO APLICA')
            AND t461_1.f_periodo = ?
            AND [f_cod_vendedor] = ?
        GROUP BY [f_vendedor], t106.f106_descripcion, [f_cod_vendedor]
        SQL;


        $dataVentasMarca = DB::connection('sqlsrv')->select($sqlVentasPorMarca, [$periodo, $codigo_asesor]);

        $ventasPorMarca = [];

        foreach ($dataVentasMarca as $r) {
            $vendedor = (string) $r->vendedor;
            $marca    = (string) $r->marca;
            $ventasPorMarca[$vendedor][$marca] = [
                'cantidad' => (float) $r->cantidad,
                'dinero'   => (float) $r->dinero,
            ];
        }

        $mapVentas = [
            // Unidades
            'RINOVA TIRES'              => ['col' => 'venta_rinova_tires', 'tipo' => 'unidades'],
            'PIRELLI'                   => ['col' => 'venta_pirelli', 'tipo' => 'unidades'],
            'CST TIRES'                 => ['col' => 'venta_cst_tires', 'tipo' => 'unidades'],
            'CST ATV'                   => ['col' => 'venta_cst_atv', 'tipo' => 'unidades'],
            'CST E-SCOOTER'             => ['col' => 'venta_cst_e_scooter', 'tipo' => 'unidades'],
            'FORERUNNER'                => ['col' => 'venta_forerunner', 'tipo' => 'unidades'],
            'WDT BIKE'                  => ['col' => 'venta_wdt_bike', 'tipo' => 'unidades'],
            'WDT TUBE'                  => ['col' => 'venta_wdt_tube', 'tipo' => 'unidades'],
            'WDT E-SCOOTER'             => ['col' => 'venta_wdt_e_scooter', 'tipo' => 'unidades'],
            'RINOVA ATV'                => ['col' => 'venta_rinova_atv', 'tipo' => 'unidades'],
            'WDT'                       => ['col' => 'venta_wdt', 'tipo' => 'unidades'],
            'HAKUBA - ARMOR - WDT'      => ['col' => 'venta_hakuba_armor_wdt', 'tipo' => 'unidades'],

            // Dinero
            'KOYO'                   => ['col' => 'venta_koyo', 'tipo' => 'dinero'],
            'PFI'                    => ['col' => 'venta_pfi', 'tipo' => 'dinero'],
            'RNV'                    => ['col' => 'venta_rnv', 'tipo' => 'dinero'],
            'BATERIAS RINOVA'        => ['col' => 'venta_baterias_rinova', 'tipo' => 'dinero'],
            'NARVA'                  => ['col' => 'venta_narva', 'tipo' => 'dinero'],
            'RINOVA LIGHTING'        => ['col' => 'venta_rinova_lighting', 'tipo' => 'dinero'],
            'RINOVA LIGHTING LED'    => ['col' => 'venta_rinova_lighting_led', 'tipo' => 'dinero'],
            'RINOVA - GOOD TUBE'              => ['col' => 'venta_good_tube', 'tipo' => 'dinero'],
            'RINOVA PARTS'              => ['col' => 'venta_rinova_parts', 'tipo' => 'dinero'],
        ];

        $wdtToHakuba = ['WDT BIKE', 'WDT TUBE', 'WDT E-SCOOTER', 'HAKUBA - ARMOR - WDT', 'FORERUNNER', 'RINOVA ATV', 'WDT'];

        $pct = fn($venta, $presu) => $presu > 0 ? round(($venta / $presu) * 100, 2) : 0.0;

        foreach ($data_asesores as $u) {
            $cedula = (string) $u->cedula;

            // init ventas a 0
            foreach ($mapVentas as $cfg) {
                $u->{$cfg['col']} = 0;
                if (!empty($cfg['col_dinero'])) {
                    $u->{$cfg['col_dinero']} = 0;
                }
            }
            // init agregados
            $u->venta_hakuba_armor        = 0; // unidades acumuladas WDT → HAKUBA ARMOR
            $u->venta_hakuba_armor_dinero = 0; // dinero acumulado WDT → HAKUBA ARMOR
            $u->venta_total_llantas       = 0; // se sobrescribe luego con query por categoría
            $u->venta_total_accesorios    = 0;

            if (!empty($ventasPorMarca[$cedula])) {
                foreach ($ventasPorMarca[$cedula] as $marca => $vals) {
                    $cantidad = $vals['cantidad'] ?? 0;
                    $dinero   = $vals['dinero']   ?? 0;

                    // Caso especial RINOVA TIRES: guardar unidades y dinero
                    if ($marca === 'RINOVA TIRES') {
                        $u->venta_rinova_tires = $cantidad;
                        $u->venta_dinero_rinova_tires = $dinero;
                        continue;
                    }

                    // Acumular marcas WDT → HAKUBA ARMOR
                    if (in_array($marca, $wdtToHakuba, true)) {
                        $u->venta_hakuba_armor        += $cantidad;
                        $u->venta_hakuba_armor_dinero += $dinero;
                        continue;
                    }

                    // Asignación directa si está mapeada
                    if (isset($mapVentas[$marca])) {
                        $col  = $mapVentas[$marca]['col'];
                        $tipo = $mapVentas[$marca]['tipo'];
                        $u->{$col} = ($tipo === 'dinero') ? $dinero : $cantidad;
                    }
                }
            }
        }

        $sqlVentasCat = <<<SQL
            SELECT 
                RTRIM([f_cod_vendedor]) as vendedor,
                RTRIM([f_cod_vendedor]) as cod_vendedor,
                CONVERT(int, SUM(CASE 
                    WHEN t106.f106_descripcion IN ('PIRELLI','PIRELLI RADIAL','CST TIRES','CST ATV','CST E-SCOOTER','HAKUBA - ARMOR - WDT','WDT TUBE','WDT BIKE','WDT E-SCOOTER','FORERUNNER','RINOVA ATV','WDT')
                    THEN [f_cant_base] ELSE 0 END)) AS llantas,
                CONVERT(int, SUM(CASE 
                    WHEN t106.f106_descripcion IN ('KOYO','PFI','RNV','BATERIAS RINOVA','NARVA','RINOVA LIGHTING','RINOVA LIGHTING LED','RINOVA - GOOD TUBE', 'RINOVA PARTS')
                    THEN [f_valor_sub_local] ELSE 0 END)) AS accesorios
            FROM BI_T461_1 AS t461_1
            LEFT JOIN [t120_mc_items]               AS t120 ON t120.[f120_rowid] = t461_1.[f_rowid_item]
            LEFT JOIN [t125_mc_items_criterios]     AS t125 ON t125.[f125_rowid_item] = t120.[f120_rowid]
            LEFT JOIN [t105_mc_criterios_item_planes] AS t105 ON t105.[f105_id] = t125.[f125_id_plan]
            LEFT JOIN [t106_mc_criterios_item_mayores] AS t106 ON t106.[f106_id] = t125.[f125_id_criterio_mayor]
            WHERE 
                t461_1.[f_id_cia] = 3
                AND t461_1.[f_co] = '003'
                AND t461_1.[f_parametro_biable] = 3
                AND t106.[f106_id_plan] = '003'
                AND t106.[f106_id_cia] = '3'
                AND t120.[f120_id_cia] = '3'
                AND t125.[f125_id_plan] = '003'
                AND t125.[f125_id_cia] = '3'
                AND t105.[f105_id_cia] = '3'
                AND t106.[f106_descripcion] NOT IN ('ZFLETE','NO APLICA')
                AND t461_1.f_periodo = ?
                AND [f_cod_vendedor] = ?
            GROUP BY [f_vendedor],[f_cod_vendedor]
        SQL;

        $dataVentasCat = DB::connection('sqlsrv')->select($sqlVentasCat, [$periodo, $codigo_tercero]);

        $ventasCat = [];
        foreach ($dataVentasCat as $r) {
            $ventasCat[(string)$r->vendedor] = [
                'llantas'    => (int) $r->llantas,
                'accesorios' => (int) $r->accesorios,
            ];
        }

        foreach ($data_asesores as $u) {
            $cedula = (string) $u->cedula;

            $u->venta_total_llantas    = $ventasCat[$cedula]['llantas']    ?? 0;
            $u->venta_total_accesorios = $ventasCat[$cedula]['accesorios'] ?? 0;
            $u->total_venta_general    = $u->venta_total_llantas + $u->venta_total_accesorios;

            // Cumplimientos por marca principal
            $u->cumplimiento_venta_rinova_tires         = $pct($u->venta_rinova_tires,        (float) ($u->rinova_tires ?? 0));
            $u->cumplimiento_venta_pirelli              = $pct($u->venta_pirelli,             (float) ($u->pirelli ?? 0));
            $u->cumplimiento_venta_cst_tires            = $pct($u->venta_cst_tires,           (float) ($u->cst_tires ?? 0));
            $u->cumplimiento_venta_cst_atv              = $pct($u->venta_cst_atv,             (float) ($u->cst_atv ?? 0));
            $u->cumplimiento_venta_cst_e_scooter        = $pct($u->venta_cst_e_scooter,       (float) ($u->cst_e_scooter ?? 0));
            $u->cumplimiento_venta_hakuba_armor         = $pct($u->venta_hakuba_armor,        (float) ($u->hakuba_armor ?? 0));
            $u->cumplimiento_venta_koyo                 = $pct($u->venta_koyo,                (float) ($u->koyo ?? 0));
            $u->cumplimiento_venta_pfi                  = $pct($u->venta_pfi,                 (float) ($u->pfi ?? 0));
            $u->cumplimiento_venta_rnv                  = $pct($u->venta_rnv,                 (float) ($u->rnv ?? 0));
            $u->cumplimiento_venta_baterias_rinova      = $pct($u->venta_baterias_rinova,     (float) ($u->baterias_rinova ?? 0));
            $u->cumplimiento_venta_rinova_lighting      = $pct($u->venta_rinova_lighting,     (float) ($u->rinova_lighting ?? 0));
            $u->cumplimiento_venta_rinova_lighting_led  = $pct($u->venta_rinova_lighting_led, (float) ($u->rinova_lighting_led ?? 0));
            $u->cumplimiento_venta_narva                = $pct($u->venta_narva,               (float) ($u->narva ?? 0));
            $u->cumplimiento_venta_good_tube            = $pct($u->venta_good_tube,           (float) ($u->good_tube ?? 0));
            $u->cumplimiento_venta_rinova_parts           = $pct($u->venta_rinova_parts ,           (float) ($u->rinova_parts  ?? 0));

            // Cumplimientos por categorías
            $u->cumplimiento_venta_total_llantas     = $pct($u->venta_total_llantas,    (float) ($u->total_llantas ?? 0));
            $u->cumplimiento_venta_total_accesorios  = $pct($u->venta_total_accesorios, (float) ($u->total_repuestos ?? 0)); // si tu total por accesorios viene de REPUSTOS (presupuesto)
        }

        //return $data_asesores;

        return response()->json([
            'total_clientes'       => $total,
            'clientes_con_venta'   => $conVenta,
            'cumplimiento_porcent' => $cumplimiento,
            'clientesConVenta'     => $clientesConVenta,
            'clientesSinVentaCantidad' => $clientesSinVentaCantidad,
            'periodo'              => $periodo,
            'impactadosNoVenta'    => $impactadosNoVenta,
            'impactadosNoVentaDetalle' => $impactadosNoVentaDetalle,
            'asesor'               => $codigo_asesor,
            'presupuesto'          => $data_asesores,
            'presupuesto1'          => $presupuesto,
            'ventaCondPago'       => $ventaConPagoRow,
            'ventasPeriodo'        => $ventasPeriodoRow,
            'conCreditoVenta'   => $conCreditoVenta,
            'conContadoVenta' => $conContadoVenta,
            'totalCredito' => $totalCredito,
            'totalContado' => $totalContado
        ], 200, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }
    
    // HASTA ACÁ VENTAS

    public function indexCartera(Request $request)
    {
        //$periodo = $request->periodo;
        $periodo = '202601';
        $usuario = User::where('codigo_asesor','0603')->first();
        
        $recaudoPresupuesto = $this->calcularTotalRecaudado($periodo, '0603');

        $porcentajeClientes = $this->porcentajeClientesImpactadosCredito($periodo, '0603');

        $recuadoPorDias = $this->recuadoPorDias('0603', $periodo);

        $totalPresupuesto = PresupuestoRecaudo::where('periodo', $periodo)
        ->where('asesor', '0603')
        ->sum('saldo');

        return $resultado[] = [
            'user_id' => $usuario->id,
            'nombre_asesor' => $usuario->name,
            'codigo_asesor' => '0603',
            'categoria_asesor' => $usuario->categoria_asesor,
            'periodo' => $periodo,

            'catera' => [
                'totalPresupuesto' => $totalPresupuesto,
                'recaudoPresupuesto' => $recaudoPresupuesto,
                'cumplimiento' => round(($totalPresupuesto/$recaudoPresupuesto)*100,2),
                'porcentajeClientes' => $porcentajeClientes,
                'recuadoPorDias' => $recuadoPorDias
            ]
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
}
