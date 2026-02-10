<?php

namespace App\Livewire\Admin\VentasUnidadesValorPorMes;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Http\Controllers\Admin\PresupuestoComercialController;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PivotVentasPorMesExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class Index extends Component
{
    public string $periodo = '';
    public array $periodos = [];
    public string $periodoLabel = '';

    // marcas (columnas) en orden
    public array $marcas = [];

    // tablas pivot
    public array $tablaUnidades = []; // filas por asesor
    public array $tablaValor = [];    // filas por asesor

    // totales por marca (footer)
    public array $totalesUnidadesPorMarca = []; // [marca => total]
    public array $totalesValorPorMarca = [];    // [marca => total]

    // totales generales
    public float $totalUnidades = 0;
    public float $totalValor = 0;

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

    public function cargar(): void
    {
        Carbon::setLocale('es');
        $this->periodoLabel = ucfirst(
            Carbon::createFromFormat('Ym', $this->periodo)->translatedFormat('F Y')
        );

        /** @var PresupuestoComercialController $ctrl */
        $ctrl = app(PresupuestoComercialController::class);

        // Data cruda desde SQL (periodo, vendedor, marca, venta, unidades)
        $rows = collect($ctrl->cumplimientoData($this->periodo))
            ->map(fn($r) => [
                'vendedor' => trim((string)($r->vendedor ?? '')),
                'marca'    => trim((string)($r->marca ?? '')),
                'venta'    => (float)($r->venta ?? 0),
                'unidades' => (float)($r->unidades ?? 0),
            ])
            ->filter(fn($r) => $r['vendedor'] !== '' && $r['marca'] !== '');

        // Totales generales
        $this->totalValor = (float) $rows->sum('venta');
        $this->totalUnidades = (float) $rows->sum('unidades');

        // Ordenar marcas por valor total desc (para que el pivot quede “bonito”)
        $marcaOrder = $rows->groupBy('marca')
            ->map(fn(Collection $g, $marca) => [
                'marca' => (string)$marca,
                'valor' => (float)$g->sum('venta'),
            ])
            ->sortByDesc('valor')
            ->pluck('marca')
            ->values()
            ->all();

        $this->marcas = $marcaOrder;

        // Map vendedor -> nombre
        $vendedores = $rows->pluck('vendedor')->unique()->values()->all();

        $mapNombres = User::query()
            ->whereIn('codigo_asesor', $vendedores)
            ->pluck('name', 'codigo_asesor')
            ->map(fn($n) => trim((string)$n))
            ->toArray();

        // Construcción del pivot por asesor
        // base: [vendedor => [marca => ['venta'=>x, 'unidades'=>y]]]
        $byVendedor = $rows->groupBy('vendedor');

        $tablaUnidades = [];
        $tablaValor = [];

        foreach ($byVendedor as $vendedor => $g) {
            $cellsUnidades = [];
            $cellsValor = [];

            foreach ($this->marcas as $marca) {
                $sub = $g->firstWhere('marca', $marca);

                // OJO: firstWhere solo da 1 fila si ya viene agrupado. Pero tu SQL ya agrupa por vendedor+marca,
                // así que está perfecto. Si algún día viniera desagrupado, cambia por sum() filtrando.
                $u = (float)($sub['unidades'] ?? 0);
                $v = (float)($sub['venta'] ?? 0);

                $cellsUnidades[$marca] = $u;
                $cellsValor[$marca] = $v;
            }

            $rowTotalU = array_sum($cellsUnidades);
            $rowTotalV = array_sum($cellsValor);

            $tablaUnidades[] = [
                'vendedor' => (string)$vendedor,
                'nombre'   => $mapNombres[$vendedor] ?? 'Sin nombre',
                'cells'    => $cellsUnidades,
                'total'    => (float)$rowTotalU,
            ];

            $tablaValor[] = [
                'vendedor' => (string)$vendedor,
                'nombre'   => $mapNombres[$vendedor] ?? 'Sin nombre',
                'cells'    => $cellsValor,
                'total'    => (float)$rowTotalV,
            ];
        }

        // Ordenar asesores por total desc (en valor y unidades)
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
    }

    public function descargarUnidades(): BinaryFileResponse
{
    // Asegura que esté actualizado
    $this->cargar();

    $file = "pivot_unidades_{$this->periodo}.xlsx";

    return Excel::download(
        new PivotVentasPorMesExport($this->marcas, $this->tablaUnidades, 'Unidades'),
        $file
    );
}

public function descargarValor(): BinaryFileResponse
{
    $this->cargar();

    $file = "pivot_valor_{$this->periodo}.xlsx";

    return Excel::download(
        new PivotVentasPorMesExport($this->marcas, $this->tablaValor, 'Valor'),
        $file
    );
}

    

    public function render()
    {
        return view('livewire.admin.ventas-unidades-valor-por-mes.index');
    }
}
