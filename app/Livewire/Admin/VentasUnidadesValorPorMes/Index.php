<?php

namespace App\Livewire\Admin\VentasUnidadesValorPorMes;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Http\Controllers\Admin\PresupuestoComercialController;
use App\Models\User;
use App\Models\PresupuestoComercial;

class Index extends Component
{
    public string $periodo = '';
    public array $periodos = [];
    public string $periodoLabel = '';

    // marcas (columnas) en orden
    public array $marcas = [];

    // tablas pivot simples
    public array $tablaUnidades = []; // filas por asesor
    public array $tablaValor = [];    // filas por asesor

    // totales por marca (footer)
    public array $totalesUnidadesPorMarca = []; // [marca => total]
    public array $totalesValorPorMarca = [];    // [marca => total]

    // totales generales
    public float $totalUnidades = 0;
    public float $totalValor = 0;

    // ✅ NUEVO: tabla mix cumplimiento (por marca: presu/real/% según regla unidades/valor)
    public array $tablaCumplMix = []; // filas por asesor

    public function mount(?string $periodo = null)
    {
        $this->periodo = $periodo ?: now()->format('Ym');
        $this->periodos = $this->buildPeriodos(18);
        $this->cargar();
    }

    public function updatedPeriodo()
    {
        $this->cargar();
    }

    private function buildPeriodos(int $meses = 12): array
    {
        Carbon::setLocale('es');

        $out = [];
        $base = Carbon::now()->startOfMonth();

        for ($i = 0; $i < $meses; $i++) {
            $dt = $base->copy()->subMonths($i);
            $out[] = [
                'value' => $dt->format('Ym'),
                'label' => ucfirst($dt->translatedFormat('F Y')),
            ];
        }

        return $out;
    }

    private function pct(float $real, float $presu): float
    {
        return $presu > 0 ? round(($real / $presu) * 100, 2) : 0;
    }

    /**
     * ✅ Regla negocio:
     * - Llantas + Pirelli => UNIDADES
     * - Repuestos => VALOR
     *
     * Ajusta aquí si tus marcas/categorías vienen con otros nombres.
     */
    private function marcaEsUnidades(string $marca): bool
    {
        $m = strtoupper(trim($marca));

        // marcas / categorías que cuentan por unidades
        return str_contains($m, 'RINOVA TIRES')
            || str_contains($m, 'PIRELLI')
            || str_contains($m, 'LLANTA');
    }

    public function cargar(): void
    {
        Carbon::setLocale('es');
        $this->periodoLabel = ucfirst(
            Carbon::createFromFormat('Ym', $this->periodo)->translatedFormat('F Y')
        );

        /** @var PresupuestoComercialController $ctrl */
        $ctrl = app(PresupuestoComercialController::class);

        // 1) Data ventas cruda desde SQL (vendedor, marca, venta, unidades)
        $rows = collect($ctrl->cumplimientoData($this->periodo))
            ->map(fn($r) => [
                'vendedor' => trim((string)($r->vendedor ?? '')),
                'marca'    => trim((string)($r->marca ?? '')),
                'venta'    => (float)($r->venta ?? 0),
                'unidades' => (float)($r->unidades ?? 0),
            ])
            ->filter(fn($r) => $r['vendedor'] !== '' && $r['marca'] !== '')
            ->values();

        // Totales generales
        $this->totalValor = (float) $rows->sum('venta');
        $this->totalUnidades = (float) $rows->sum('unidades');

        // Orden de marcas por valor desc (para que quede “bonito”)
        $this->marcas = $rows->groupBy('marca')
            ->map(fn(Collection $g, $marca) => [
                'marca' => (string)$marca,
                'valor' => (float)$g->sum('venta'),
            ])
            ->sortByDesc('valor')
            ->pluck('marca')
            ->values()
            ->all();

        // Map vendedor -> nombre
        $vendedores = $rows->pluck('vendedor')->unique()->values()->all();

        $mapNombres = User::query()
            ->whereIn('codigo_asesor', $vendedores)
            ->pluck('name', 'codigo_asesor')
            ->map(fn($n) => trim((string)$n))
            ->toArray();

        // 2) Pivot simple por asesor
        $byVendedor = $rows->groupBy('vendedor');

        $tablaUnidades = [];
        $tablaValor = [];

        foreach ($byVendedor as $vendedor => $g) {
            $cellsUnidades = [];
            $cellsValor = [];

            foreach ($this->marcas as $marca) {
                $sub = $g->firstWhere('marca', $marca);

                $u = (float)($sub['unidades'] ?? 0);
                $v = (float)($sub['venta'] ?? 0);

                $cellsUnidades[$marca] = $u;
                $cellsValor[$marca] = $v;
            }

            $tablaUnidades[] = [
                'vendedor' => (string)$vendedor,
                'nombre'   => $mapNombres[$vendedor] ?? 'Sin nombre',
                'cells'    => $cellsUnidades,
                'total'    => (float) array_sum($cellsUnidades),
            ];

            $tablaValor[] = [
                'vendedor' => (string)$vendedor,
                'nombre'   => $mapNombres[$vendedor] ?? 'Sin nombre',
                'cells'    => $cellsValor,
                'total'    => (float) array_sum($cellsValor),
            ];
        }

        $this->tablaUnidades = collect($tablaUnidades)->sortByDesc('total')->values()->all();
        $this->tablaValor = collect($tablaValor)->sortByDesc('total')->values()->all();

        // Totales por marca (footer)
        $totU = [];
        $totV = [];
        foreach ($this->marcas as $marca) {
            $totU[$marca] = (float) $rows->where('marca', $marca)->sum('unidades');
            $totV[$marca] = (float) $rows->where('marca', $marca)->sum('venta');
        }
        $this->totalesUnidadesPorMarca = $totU;
        $this->totalesValorPorMarca = $totV;

        // ============================================================
        // ✅ 3) TABLA CUMPLIMIENTO MIX (Presu/Real/% por marca)
        // - presupuesto sale de presupuestos_comerciales (periodo, asesor, marca)
        // - real: unidades para llantas/pirelli, venta para repuestos
        // ============================================================

        // Presupuesto por asesor+marca (sum por si hay varias filas)
        $presuMap = PresupuestoComercial::query()
            ->where('periodo', $this->periodo)
            ->select('codigo_asesor', 'marca', 'presupuesto')
            ->get()
            ->map(fn($p) => [
                'vendedor' => trim((string)($p->codigo_asesor ?? '')),
                'marca'    => trim((string)($p->marca ?? '')),
                'presu'    => (float)($p->presupuesto ?? 0),
            ])
            ->filter(fn($r) => $r['vendedor'] !== '' && $r['marca'] !== '')
            ->groupBy(fn($r) => $r['vendedor'].'|'.$r['marca'])
            ->map(fn($g) => (float) $g->sum('presu'))
            ->toArray();

        $tablaCumpl = [];

        foreach ($vendedores as $vend) {
            $cells = [];
            $totalPresu = 0.0;
            $totalReal  = 0.0;

            foreach ($this->marcas as $marca) {
                $key = $vend.'|'.$marca;

                $presupuesto = (float)($presuMap[$key] ?? 0);

                // real según regla de negocio
                if ($this->marcaEsUnidades($marca)) {
                    $real = (float) $rows
                        ->where('vendedor', $vend)
                        ->where('marca', $marca)
                        ->sum('unidades');
                } else {
                    $real = (float) $rows
                        ->where('vendedor', $vend)
                        ->where('marca', $marca)
                        ->sum('venta');
                }

                $cells[$marca] = [
                    'presu' => $presupuesto,
                    'real'  => $real,
                    'pct'   => $this->pct($real, $presupuesto),
                    'modo'  => $this->marcaEsUnidades($marca) ? 'UNIDADES' : 'VALOR',
                ];

                $totalPresu += $presupuesto;
                $totalReal  += $real;
            }

            $tablaCumpl[] = [
                'vendedor'  => (string)$vend,
                'nombre'    => $mapNombres[$vend] ?? 'Sin nombre',
                'cells'     => $cells,
                'tot_presu' => (float) $totalPresu,
                'tot_real'  => (float) $totalReal,
                'tot_pct'   => $this->pct($totalReal, $totalPresu),
            ];
        }

        // orden por "real" total
        $this->tablaCumplMix = collect($tablaCumpl)->sortByDesc('tot_real')->values()->all();
    }

    public function render()
    {
        return view('livewire.admin.ventas-unidades-valor-por-mes.index');
    }
}
