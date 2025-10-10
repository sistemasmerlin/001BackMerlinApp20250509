<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PresupuestoComercial;
use App\Models\ReporteVisita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
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
                    [$periodo,$asesor]);
        
         $ventasPeriodoRow = DB::connection('sqlsrv')->select(
                                "SELECT 
                        t461_1.f_cliente_fact,
                        t200.f200_razon_social,
                        t461_1.f_id_tipo_docto        AS tipo_docto,
                        SUM(t461_1.f_valor_bruto_local)    AS total_bruto,
                        SUM(t461_1.f_valor_dscto_local)    AS total_descuento,
                        SUM(t461_1.f_valor_imp_local)      AS total_impuesto,
                        SUM(t461_1.f_valor_neto_local)     AS total_neto_local,
                        SUM(t461_1.f_valor_subtotal_local) AS total_subtotal,
                        t461_1.f_ciudad_desp
                    FROM [BI_T461_1] AS t461_1
                    LEFT JOIN [t200_mm_terceros] AS t200
                    ON t200.f200_nit = t461_1.f_cliente_fact
                    AND t200.f200_id_cia = 3
                    WHERE t461_1.f_periodo = ?
                    AND t461_1.f_ref_item NOT IN ('ZLE99998','ZLE99999')
                    AND t461_1.f_parametro_biable = 3 
                    AND t461_1.f_cod_vendedor = ?
                    GROUP BY t461_1.f_cliente_fact, t461_1.f_ciudad_desp, t200.f200_razon_social, t461_1.f_id_tipo_docto 
                    ORDER BY t461_1.f_ciudad_desp, t461_1.f_id_tipo_docto ASC;
                    ",
                    [$periodo,$asesor]);

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
            [$year, $month, $asesor]
        );


        $impactadosNoVenta = ReporteVisita::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('vendedor', $asesor)
            ->whereHas('motivos', fn($q) => $q->where('motivos_visita_id', '<>', '11'))
            ->distinct('nit')
            ->count('nit');


        $total = (int)($totalRow->total_clientes ?? 0);
        $conVenta = (int)($ventaRow->clientes_con_venta ?? 0);
        $cumplimiento = $total > 0 ? round(($conVenta / $total) * 100, 2) : 0.0;

        $presupuesto = PresupuestoComercial::where('codigo_asesor','=',$asesor)->where('periodo','=', $periodo)->get();


        $marcasPresu = [
            'RINOVA TIRES'         => 'rinova_tires',
            'PIRELLI'              => 'pirelli',
            'CST TIRES'            => 'cst_tires',
            'HAKUBA - ARMOR'       => 'hakuba_armor',
            'KOYO'                 => 'koyo',
            'PFI'                  => 'pfi',
            'RNV'                  => 'rnv',
            'BATERIAS RINOVA'      => 'baterias_rinova',
            'NARVA'                => 'narva',
            'RINOVA LIGHTING'      => 'rinova_lighting',
            'RINOVA LIGHTING LED'  => 'rinova_lighting_led',
            'GOOD TUBE'            => 'good_tube',
        ];



        $query = User::query()
        ->select('users.cedula','users.codigo_asesor','users.name')
        ->addSelect(DB::raw("'{$periodo}' as periodo"))
        ->selectSub(
            PresupuestoComercial::selectRaw('MAX(clasificacion_asesor)')
                ->whereColumn('codigo_asesor','users.codigo_asesor')
                ->where('periodo', $periodo),
            'tipo_asesor'
        )
        ->where('users.codigo_asesor', $asesor);

    foreach ($marcasPresu as $marca => $alias) {
        $query->withSum(
            ['presupuestosComerciales as '.$alias => function($q) use ($periodo, $marca) {
                $q->where('periodo', $periodo)
                ->where('marca', $marca);
                // ->where('estado',1); // si tienes esta columna
            }],
            'presupuesto'
        );
    }

    $query
        ->withSum(['presupuestosComerciales as total_llantas' => function($q) use ($periodo) {
            $q->where('periodo', $periodo)->where('categoria', 'llantas');
        }], 'presupuesto')
        ->withSum(['presupuestosComerciales as total_repuestos' => function($q) use ($periodo) {
            $q->where('periodo', $periodo)->where('categoria', 'repuestos');
        }], 'presupuesto')
        ->withSum(['presupuestosComerciales as total_presupuesto' => function($q) use ($periodo) {
            $q->where('periodo', $periodo);
        }], 'presupuesto');

    $data_asesores = $query->get();

    $sqlVentasPorMarca = <<<SQL
        SELECT 
            RTRIM([f_vendedor])       as vendedor,        -- suele ser CÉDULA
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

        
        $dataVentasMarca = DB::connection('sqlsrv')->select($sqlVentasPorMarca, [$periodo, $asesor]);

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
            'RINOVA TIRES'           => ['col' => 'venta_rinova_tires', 'tipo' => 'unidades', 'col_dinero' => 'venta_dinero_rinova_tires'],
            'PIRELLI'                => ['col' => 'venta_pirelli', 'tipo' => 'unidades'],
            'CST TIRES'              => ['col' => 'venta_cst_tires', 'tipo' => 'unidades'],
            'FORERUNNER'             => ['col' => 'venta_forerunner', 'tipo' => 'unidades'],
            'WDT BIKE'               => ['col' => 'venta_wdt_bike', 'tipo' => 'unidades'],
            'WDT TUBE'               => ['col' => 'venta_wdt_tube', 'tipo' => 'unidades'],
            'WDT E-SCOOTER'          => ['col' => 'venta_wdt_e_scooter', 'tipo' => 'unidades'],
            'RINOVA ATV'             => ['col' => 'venta_rinova_atv', 'tipo' => 'unidades'],
            'WDT'                    => ['col' => 'venta_wdt', 'tipo' => 'unidades'],
            'HAKUBA - ARMOR - WDT'   => ['col' => 'venta_hakuba_armor_wdt', 'tipo' => 'unidades'],

            // Dinero
            'KOYO'                   => ['col' => 'venta_koyo', 'tipo' => 'dinero'],
            'PFI'                    => ['col' => 'venta_pfi', 'tipo' => 'dinero'],
            'RNV'                    => ['col' => 'venta_rnv', 'tipo' => 'dinero'],
            'BATERIAS RINOVA'        => ['col' => 'venta_baterias_rinova', 'tipo' => 'dinero'],
            'NARVA'                  => ['col' => 'venta_narva', 'tipo' => 'dinero'],
            'RINOVA LIGHTING'        => ['col' => 'venta_rinova_lighting', 'tipo' => 'dinero'],
            'RINOVA LIGHTING LED'    => ['col' => 'venta_rinova_lighting_led', 'tipo' => 'dinero'],
            'GOOD TUBE'              => ['col' => 'venta_good_tube', 'tipo' => 'dinero'],
        ];

        $wdtToHakuba = ['WDT BIKE','WDT TUBE','WDT E-SCOOTER','HAKUBA - ARMOR - WDT','FORERUNNER','RINOVA ATV','WDT'];

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
                RTRIM([f_vendedor]) as vendedor,
                RTRIM([f_cod_vendedor]) as cod_vendedor,
                CONVERT(int, SUM(CASE 
                    WHEN t106.f106_descripcion IN ('PIRELLI','PIRELLI RADIAL','CST TIRES','HAKUBA - ARMOR - WDT','WDT TUBE','WDT BIKE','WDT E-SCOOTER','FORERUNNER','RINOVA ATV','WDT')
                    THEN [f_cant_base] ELSE 0 END)) AS llantas,
                CONVERT(int, SUM(CASE 
                    WHEN t106.f106_descripcion IN ('KOYO','PFI','RNV','BATERIAS RINOVA','NARVA','RINOVA LIGHTING','RINOVA LIGHTING LED','GOOD TUBE')
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

        $dataVentasCat = DB::connection('sqlsrv')->select($sqlVentasCat, [$periodo, $asesor]);

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
            $u->cumplimiento_venta_rinova_tires        = $pct($u->venta_rinova_tires,        (float) ($u->rinova_tires ?? 0));
            $u->cumplimiento_venta_pirelli             = $pct($u->venta_pirelli,             (float) ($u->pirelli ?? 0));
            $u->cumplimiento_venta_cst_tires           = $pct($u->venta_cst_tires,           (float) ($u->cst_tires ?? 0));
            $u->cumplimiento_venta_hakuba_armor        = $pct($u->venta_hakuba_armor,        (float) ($u->hakuba_armor ?? 0));
            $u->cumplimiento_venta_koyo                = $pct($u->venta_koyo,                (float) ($u->koyo ?? 0));
            $u->cumplimiento_venta_pfi                 = $pct($u->venta_pfi,                 (float) ($u->pfi ?? 0));
            $u->cumplimiento_venta_rnv                 = $pct($u->venta_rnv,                 (float) ($u->rnv ?? 0));
            $u->cumplimiento_venta_baterias_rinova     = $pct($u->venta_baterias_rinova,     (float) ($u->baterias_rinova ?? 0));
            $u->cumplimiento_venta_rinova_lighting     = $pct($u->venta_rinova_lighting,     (float) ($u->rinova_lighting ?? 0));
            $u->cumplimiento_venta_rinova_lighting_led = $pct($u->venta_rinova_lighting_led, (float) ($u->rinova_lighting_led ?? 0));
            $u->cumplimiento_venta_narva               = $pct($u->venta_narva,               (float) ($u->narva ?? 0));
            $u->cumplimiento_venta_good_tube           = $pct($u->venta_good_tube,           (float) ($u->good_tube ?? 0));

            // Cumplimientos por categorías
            $u->cumplimiento_venta_total_llantas     = $pct($u->venta_total_llantas,    (float) ($u->total_llantas ?? 0));
            $u->cumplimiento_venta_total_accesorios  = $pct($u->venta_total_accesorios, (float) ($u->total_repuestos ?? 0)); // si tu total por accesorios viene de REPUSTOS (presupuesto)
            }


        return response()->json([
            'total_clientes'       => $total,
            'clientes_con_venta'   => $conVenta,
            'cumplimiento_porcent' => $cumplimiento,
            'clientesConVenta'     => $clientesConVenta,
            'periodo'              => $periodo,
            'impactadosNoVenta'    => $impactadosNoVenta,
            'asesor'               => $asesor,
            'presupuesto'          => $data_asesores,
            'presupuesto1'          => $presupuesto,
            'ventaCondPago'       => $ventaConPagoRow,
            'ventasPeriodo'        => $ventasPeriodoRow
        ], 200, [], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }
}
