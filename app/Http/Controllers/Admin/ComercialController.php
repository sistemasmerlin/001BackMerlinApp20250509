<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresupuestoComercial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ComisionesController extends Controller
{
    public function index(Request $request)
    {

        $periodo = $request->get('periodo', now()->format('Ym'));
        $busqueda = $request->get('busqueda');

        $comisiones = $this->obtenerComisionesMultiples($periodo, $busqueda);

        return view('admin.comisiones.index', compact('comisiones', 'periodo', 'busqueda'));
    }

    public function obtenerComisionesMultiples(string $periodo, ?string $busqueda = null): Collection
    {
        $usuarios = User::query()
            ->when($busqueda, function ($q) use ($busqueda) {
                $q->where(function ($sub) use ($busqueda) {
                    $sub->where('name', 'like', "%{$busqueda}%")
                        ->orWhere('codigo_asesor', 'like', "%{$busqueda}%")
                        ->orWhere('cedula', 'like', "%{$busqueda}%");
                });
            })
            ->whereNotNull('codigo_asesor')
            ->orderBy('name')
            ->get();

        return $usuarios->map(function ($user) use ($periodo) {
            return $this->calcularComisionAsesor($user->codigo_asesor, $periodo);
        });
    }

    private function calcularComisionAsesor(string|int $asesor, string $periodo): array
    {
        $usuario = User::where('codigo_asesor', '=', $asesor)->first();

        if (!$usuario) {
            return [
                'asesor' => $asesor,
                'periodo' => $periodo,
                'error' => 'Usuario no encontrado',
            ];
        }

        $periodo = preg_replace('/\D/', '', $periodo);

        if (strlen($periodo) !== 6) {
            return [
                'asesor' => $asesor,
                'periodo' => $periodo,
                'nombre' => $usuario->name,
                'error' => 'Periodo inválido (use YYYYMM)',
            ];
        }

        $asesorInt = (int) $asesor;

        $datosAsesor = DB::connection('sqlsrv')->selectOne("
            SELECT 
                t210.f210_id   AS codigo_asesor,
                t200.f200_nit  AS codigo_tercero
            FROM [t200_mm_terceros] AS t200
            INNER JOIN [t210_mm_vendedores] AS t210
                ON t210.[f210_rowid_tercero] = t200.f200_rowid
            WHERE 
                t200.f200_id_cia = 3
                AND t210.f210_id = ?
        ", [$asesorInt]);

        if (!$datosAsesor) {
            return [
                'asesor' => $asesor,
                'periodo' => $periodo,
                'nombre' => $usuario->name,
                'error' => 'Asesor no encontrado en SQL Server',
            ];
        }

        $codigo_tercero = $datosAsesor->codigo_tercero;
        $codigo_asesor  = $datosAsesor->codigo_asesor;
        $categoria_asesor = strtolower(trim($usuario->categoria_asesor ?? ''));

        [$rangosLlantas, $rangosRepuestos] = $this->obtenerRangosPorCategoria($categoria_asesor);

        $presuLlantas = (float) PresupuestoComercial::where('codigo_asesor', $codigo_asesor)
            ->where('periodo', $periodo)
            ->where('categoria', 'llantas')
            ->where('tipo_presupuesto', 'unidades')
            ->sum('presupuesto');

        $presuPirelli = (float) PresupuestoComercial::where('codigo_asesor', $codigo_asesor)
            ->where('periodo', $periodo)
            ->where('categoria', 'pirelli')
            ->where('tipo_presupuesto', 'unidades')
            ->sum('presupuesto');

        $presuRepuestos = (float) PresupuestoComercial::where('codigo_asesor', $codigo_asesor)
            ->where('periodo', $periodo)
            ->where('categoria', 'repuestos')
            ->sum('presupuesto');

        $presuTotal = (float) PresupuestoComercial::where('codigo_asesor', $codigo_asesor)
            ->where('periodo', $periodo)
            ->where('categoria', 'total')
            ->sum('presupuesto');

        $ventasRows = DB::connection('sqlsrv')->select("
            WITH base AS (
                SELECT
                    RTRIM(t461_1.[f_cod_vendedor]) AS vendedor,
                    CASE
                        WHEN t106.[f106_descripcion] IN (
                            'RINOVA TIRES','HAKUBA - ARMOR - WDT','CST TIRES','CST ATV','CST E-SCOOTER',
                            'FORERUNNER','WDT BIKE','WDT TUBE','WDT E-SCOOTER','RINOVA ATV','WDT','WORCRAFT'
                        ) THEN 'LLANTAS'

                        WHEN t106.[f106_descripcion] IN ('PIRELLI','PIRELLI RADIAL')
                        THEN 'PIRELLI'

                        WHEN t106.[f106_descripcion] IN (
                            'RINOVA LIGHTING','RINOVA LIGHTING LED','RNV','BATERIAS RINOVA','KOYO','NARVA','PFI','RINOVA - GOOD TUBE', 'RINOVA PARTS'
                        ) THEN 'REPUESTOS'

                        ELSE 'OTRAS'
                    END AS categoria,
                    t461_1.[f_cant_base]       AS cantidad,
                    t461_1.[f_valor_sub_local] AS dinero
                FROM BI_T461_1 AS t461_1
                LEFT JOIN [t120_mc_items] AS t120 ON t120.[f120_rowid] = t461_1.[f_rowid_item]
                LEFT JOIN [t125_mc_items_criterios] AS t125 ON t125.[f125_rowid_item] = t120.[f120_rowid]
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
                    AND t461_1.[f_cod_vendedor] = ?
            )
            SELECT vendedor, categoria, SUM(cantidad) AS cantidad, SUM(dinero) AS dinero
            FROM base
            GROUP BY vendedor, categoria
        ", [$periodo, $codigo_asesor]);

        $ventasGrouped = collect($ventasRows)->groupBy(fn($row) => strtolower($row->categoria));

        $ventaLlantasUnid = $this->sumCatField($ventasGrouped, 'llantas', 'cantidad');
        $ventaPirelliUnid = $this->sumCatField($ventasGrouped, 'pirelli', 'cantidad');
        $ventaRepuestosVal = $this->sumCatField($ventasGrouped, 'repuestos', 'dinero');
        $ventaLlantasVal = $this->sumCatField($ventasGrouped, 'llantas', 'dinero');
        $ventaPirreliVal = $this->sumCatField($ventasGrouped, 'pirelli', 'dinero');

        $ventaTotalVal = (float) collect($ventasRows)
            ->filter(fn($r) => strtolower(trim($r->categoria)) !== 'pirelli')
            ->sum('dinero');

        $cumplLlantas = $this->pct($ventaLlantasUnid, $presuLlantas);
        $cumplPirelli = $this->pct($ventaPirelliUnid, $presuPirelli);
        $cumplRepuestos = $this->pct($ventaRepuestosVal, $presuRepuestos);
        $cumplTotal = $this->pct($ventaTotalVal, $presuTotal);

        $factorLlantas = $this->calcularFactor($cumplLlantas, $rangosLlantas);
        $factorRepuestos = $this->calcularFactor($cumplRepuestos, $rangosRepuestos);

        $valorPagarLlantas = round($ventaTotalVal * $factorLlantas, 2);
        $valorPagarRepuestos = round($ventaTotalVal * $factorRepuestos, 2);
        $valorPagarPirelli = (($ventaPirreliVal / 100) * 1.5);
        $valorPagarTotal = $valorPagarLlantas + $valorPagarRepuestos + $valorPagarPirelli;

        return [
            'asesor' => $codigo_asesor,
            'nombre' => $usuario->name,
            'categoria_asesor' => $usuario->categoria_asesor,
            'periodo' => $periodo,

            'presupuesto_llantas' => $presuLlantas,
            'ventas_llantas' => $ventaLlantasUnid,
            'ventas_llantas_dinero' => $ventaLlantasVal,
            'cumplimiento_llantas' => $cumplLlantas,
            'factor_llantas' => $factorLlantas,
            'valor_pagar_llantas' => $valorPagarLlantas,

            'presupuesto_pirelli' => $presuPirelli,
            'ventas_pirelli' => $ventaPirelliUnid,
            'ventas_pirelli_dinero' => $ventaPirreliVal,
            'cumplimiento_pirelli' => $cumplPirelli,
            'factor_pirelli' => 1.5,
            'valor_pagar_pirelli' => $valorPagarPirelli,

            'presupuesto_repuestos' => $presuRepuestos,
            'ventas_repuestos' => $ventaRepuestosVal,
            'cumplimiento_repuestos' => $cumplRepuestos,
            'factor_repuestos' => $factorRepuestos,
            'valor_pagar_repuestos' => $valorPagarRepuestos,

            'presupuesto_total' => $presuTotal,
            'ventas_total' => $ventaTotalVal,
            'cumplimiento_total' => $cumplTotal,

            'valor_pagar_total' => $valorPagarTotal,
        ];
    }

    private function obtenerRangosPorCategoria(string $categoria): array
    {
        if ($categoria === 'master') {
            $rangosRepuestos = [
                ['min' => 0, 'max' => 79.99, 'factor' => 0.000],
                ['min' => 80, 'max' => 84.99, 'factor' => 0.0058],
                ['min' => 85, 'max' => 89.99, 'factor' => 0.0062],
                ['min' => 90, 'max' => 94.99, 'factor' => 0.0066],
                ['min' => 95, 'max' => 99.99, 'factor' => 0.0070],
                ['min' => 100, 'max' => 109.99, 'factor' => 0.0074],
                ['min' => 110, 'max' => 119.00, 'factor' => 0.0079],
                ['min' => 120, 'max' => 1000, 'factor' => 0.0084],
            ];

            $rangosLlantas = [
                ['min' => 0, 'max' => 79.99, 'factor' => 0.000],
                ['min' => 80, 'max' => 84.99, 'factor' => 0.0045],
                ['min' => 85, 'max' => 89.99, 'factor' => 0.0049],
                ['min' => 90, 'max' => 94.99, 'factor' => 0.0053],
                ['min' => 95, 'max' => 99.99, 'factor' => 0.0057],
                ['min' => 100, 'max' => 109.99, 'factor' => 0.0061],
                ['min' => 110, 'max' => 119.00, 'factor' => 0.0066],
                ['min' => 120, 'max' => 1000, 'factor' => 0.0071],
            ];
        } else {
            $rangosRepuestos = [
                ['min' => 0, 'max' => 79.99, 'factor' => 0.000],
                ['min' => 80, 'max' => 84.99, 'factor' => 0.0034],
                ['min' => 85, 'max' => 89.99, 'factor' => 0.0038],
                ['min' => 90, 'max' => 94.99, 'factor' => 0.0042],
                ['min' => 95, 'max' => 99.99, 'factor' => 0.0046],
                ['min' => 100, 'max' => 109.99, 'factor' => 0.0050],
                ['min' => 110, 'max' => 119.00, 'factor' => 0.0055],
                ['min' => 120, 'max' => 1000, 'factor' => 0.0060],
            ];

            $rangosLlantas = [
                ['min' => 0, 'max' => 79.99, 'factor' => 0.000],
                ['min' => 80, 'max' => 84.99, 'factor' => 0.0025],
                ['min' => 85, 'max' => 89.99, 'factor' => 0.0029],
                ['min' => 90, 'max' => 94.99, 'factor' => 0.0033],
                ['min' => 95, 'max' => 99.99, 'factor' => 0.0037],
                ['min' => 100, 'max' => 109.99, 'factor' => 0.0041],
                ['min' => 110, 'max' => 119.00, 'factor' => 0.0046],
                ['min' => 120, 'max' => 1000, 'factor' => 0.0051],
            ];
        }

        return [$rangosLlantas, $rangosRepuestos];
    }

    private function calcularFactor(float $cumplimiento, array $rangos): float
    {
        foreach ($rangos as $r) {
            if ($cumplimiento >= $r['min'] && $cumplimiento <= $r['max']) {
                return (float) $r['factor'];
            }
        }

        return 0.0;
    }

    private function pct($valor, $total): int
    {
        $valor = (float) str_replace(',', '.', (string) $valor);
        $total = (float) str_replace(',', '.', (string) $total);

        if ($total <= 0) {
            return 0;
        }

        return (int) ceil(($valor / $total) * 100);
    }

    private function sumCatField($ventasGrouped, string $cat, string $field): float
    {
        $g = $ventasGrouped->get($cat);
        return $g ? (float) $g->sum($field) : 0.0;
    }
}