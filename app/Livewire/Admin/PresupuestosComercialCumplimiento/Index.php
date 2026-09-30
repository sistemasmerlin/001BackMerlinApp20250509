<?php

namespace App\Livewire\Admin\PresupuestosComercialCumplimiento;

use Livewire\Component;
use Carbon\Carbon;
use App\Http\Controllers\Admin\PresupuestoComercialController;
use Illuminate\Support\Collection;
use App\Models\PresupuestoComercial;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductosComprometidosExport;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    public string $periodo = '';
    public array $periodos = [];

    public float $totalUnidades = 0;

    public array $ventaPorMarcaUnidades = [];
    public array $rows = [];

    public ?int $openMarcaComprometida = null;
    // KPIs globales
    public float $totalVenta = 0;
    public float $totalPresupuesto = 0;
    public float $cumplimientoTotal = 0;

    // Tablas
    public array $ventaPorMarca = [];
    public array $asesores = [];

    // UI
    public ?string $openAsesor = null;
    public string $periodoLabel = '';

    // Comprometidos
    public array $comprometidos = [];
    public bool $openComprometidos = false;

    //ventas hoy 

    public string $fechaHoyLabel = '';
    public float $totalVentaHoy = 0;
    public float $totalUnidadesHoy = 0;
    public array $ventaPorMarcaHoy = [];
    public array $asesoresHoy = [];

    public function mount(?string $periodo = null)
    {
        $this->periodo = $periodo ?: now()->format('Ym');
        $this->periodos = $this->buildPeriodos(18);
        $this->cargar();
    }

    public function updatedPeriodo()
    {
        $this->openAsesor = null;
        $this->cargar();
    }

    public function toggleAsesor(string $vendedor): void
    {
        $this->openAsesor = ($this->openAsesor === $vendedor) ? null : $vendedor;
    }

    public function toggleMarcaComprometida(int $indice): void
    {
        $this->openMarcaComprometida =
            $this->openMarcaComprometida === $indice
            ? null
            : $indice;
    }
    public function toggleComprometidos(): void
    {
        $this->openComprometidos = ! $this->openComprometidos;

        if (! $this->openComprometidos) {
            $this->openMarcaComprometida = null;
        }
    }
    private function buildPeriodos(int $meses = 12): array
    {
        Carbon::setLocale('es');

        $out = [];
        $base = Carbon::now()->startOfMonth();

        for ($i = 0; $i < $meses; $i++) {
            $dt = $base->copy()->subMonths($i);
            $p = $dt->format('Ym');
            $label = ucfirst($dt->translatedFormat('F Y'));
            $out[] = ['value' => $p, 'label' => $label];
        }

        return $out;
    }

    private function pct(float $venta, float $presu): float
    {
        return $presu > 0 ? round(($venta / $presu) * 100, 2) : 0;
    }

    public function cargar(): void
    {
        Carbon::setLocale('es');

        // Label periodo (Febrero 2026)
        $this->periodoLabel = ucfirst(
            Carbon::createFromFormat('Ym', $this->periodo)->translatedFormat('F Y')
        );

        /** @var PresupuestoComercialController $ctrl */
        $ctrl = app(PresupuestoComercialController::class);

        // Ventas del día actual.
$hoy = now('America/Bogota');

$this->fechaHoyLabel = $hoy->format('d/m/Y');

$ventasHoy = collect(
    $ctrl->cumplimientoDataDia($hoy->format('Ymd'))
)->map(fn ($r) => [
    'vendedor' => trim((string) ($r->vendedor ?? '')),
    'marca' => trim((string) ($r->marca ?? '')),
    'venta' => (float) ($r->venta ?? 0),
    'unidades' => (float) ($r->unidades ?? 0),
]);

$this->totalVentaHoy = (float) $ventasHoy->sum('venta');
$this->totalUnidadesHoy = (float) $ventasHoy->sum('unidades');

// Venta del día por marca.
$this->ventaPorMarcaHoy = $ventasHoy
    ->groupBy('marca')
    ->map(fn (Collection $grupo, $marca) => [
        'marca' => (string) $marca,
        'venta' => (float) $grupo->sum('venta'),
        'unidades' => (float) $grupo->sum('unidades'),
    ])
    ->sortByDesc('venta')
    ->values()
    ->all();

// Nombres de los asesores con ventas hoy.
$codigosHoy = $ventasHoy
    ->pluck('vendedor')
    ->filter()
    ->unique()
    ->values()
    ->all();

$nombresHoy = User::query()
    ->whereIn('codigo_asesor', $codigosHoy)
    ->pluck('name', 'codigo_asesor')
    ->toArray();

// Venta del día por asesor y detalle de sus marcas.
$this->asesoresHoy = $ventasHoy
    ->groupBy('vendedor')
    ->map(function (Collection $grupo, $vendedor) use ($nombresHoy) {
        return [
            'vendedor' => (string) $vendedor,
            'nombre' => $nombresHoy[$vendedor] ?? 'Sin nombre',
            'venta' => (float) $grupo->sum('venta'),
            'unidades' => (float) $grupo->sum('unidades'),
            'marcas' => $grupo
                ->groupBy('marca')
                ->map(fn (Collection $marcas, $marca) => [
                    'marca' => (string) $marca,
                    'venta' => (float) $marcas->sum('venta'),
                    'unidades' => (float) $marcas->sum('unidades'),
                ])
                ->sortByDesc('venta')
                ->values()
                ->all(),
        ];
    })
    ->sortByDesc('venta')
    ->values()
    ->all();

        // 0) Comprometidos
        $comp = collect($ctrl->comprometidosData());

        $this->comprometidos = $comp
            ->groupBy(function ($registro) {
                return trim((string) ($registro->marca ?? 'SIN MARCA'));
            })
            ->map(function (Collection $registros, string $marca) {
                $referencias = $registros
                    ->map(function ($registro) {
                        return [
                            'referencia' => trim((string) ($registro->referencia ?? '')),
                            'descripcion' => trim((string) ($registro->descripcion ?? '')),
                            'unidades' => (float) ($registro->unidades_comprometidas ?? 0),
                            'valor' => (float) ($registro->valor_bruto_menos_dscto_linea ?? 0),
                        ];
                    })
                    ->sortByDesc('valor')
                    ->values()
                    ->all();

                return [
                    'marca' => $marca,

                    'unidades' => (float) $registros->sum(function ($registro) {
                        return (float) ($registro->unidades_comprometidas ?? 0);
                    }),

                    'valor' => (float) $registros->sum(function ($registro) {
                        return (float) ($registro->valor_bruto_menos_dscto_linea ?? 0);
                    }),

                    'cantidad_referencias' => count($referencias),
                    'referencias' => $referencias,
                ];
            })
            ->sortByDesc('valor')
            ->values()
            ->all();

        // 1) Ventas
        $data = collect($ctrl->cumplimientoData($this->periodo));

        $this->rows = $data->map(fn($r) => [
            'periodo'  => (string) ($r->periodo ?? ''),
            'vendedor' => trim((string) ($r->vendedor ?? '')),
            'marca'    => trim((string) ($r->marca ?? '')),
            'venta'    => (float)  ($r->venta ?? 0),
            'unidades'  => (float) ($r->unidades ?? 0),
        ])->values()->all();

        $col = collect($this->rows);

        // 2) Totales globales
        $this->totalVenta = (float) $col->sum('venta');
        $this->totalUnidades = (float) $col->sum('unidades');

        $totalGeneral = (float) PresupuestoComercial::query()
            ->where('periodo', $this->periodo)
            ->where('categoria', 'total')
            ->sum('presupuesto');

        $totalPirelli = (float) PresupuestoComercial::query()
            ->where('periodo', $this->periodo)
            ->where('categoria', 'like', '%pirelli%')
            ->sum(DB::raw('presupuesto * 200000'));

        $this->totalPresupuesto = $totalGeneral + $totalPirelli;


        $this->cumplimientoTotal = $this->pct($this->totalVenta, $this->totalPresupuesto);

        // 3) Venta por marca
        $this->ventaPorMarca = $col
            ->groupBy('marca')
            ->map(fn(Collection $g, $marca) => [
                'marca'    => (string) $marca,
                'venta'    => (float) $g->sum('venta'),
                'unidades' => (float) $g->sum('unidades'),
            ])
            ->sortByDesc('venta')
            ->values()
            ->all();

        $this->ventaPorMarcaUnidades = $col
            ->groupBy('marca')
            ->map(fn($g, $marca) => ['marca' => $marca, 'unidades' => (float) $g->sum('unidades')])
            ->sortByDesc('unidades')->values()->all();

        // 4) Mapa vendedor -> nombre
        $vendedores = $col->pluck('vendedor')->filter()->unique()->values()->all();

        $mapNombres = User::query()
            ->whereIn('codigo_asesor', $vendedores)
            ->pluck('name', 'codigo_asesor')
            ->map(fn($n) => trim((string)$n))
            ->toArray();

        // 5) Acordeón por asesor

        $this->asesores = $col
            ->groupBy('vendedor')
            ->map(function (Collection $g, $vendedor) use ($mapNombres) {

                $marcas = $g->groupBy('marca')
                    ->map(fn(Collection $x, $marca) => [
                        'marca'    => (string) $marca,
                        'venta'    => (float) $x->sum('venta'),
                        'unidades' => (float) $x->sum('unidades'),
                    ])
                    ->sortByDesc('venta') // si quieres por unidades: ->sortByDesc('unidades')
                    ->values()
                    ->all();

                return [
                    'vendedor' => (string) $vendedor,
                    'nombre'   => $mapNombres[$vendedor] ?? 'Sin nombre',
                    'venta'    => (float) $g->sum('venta'),
                    'unidades' => (float) $g->sum('unidades'),
                    'marcas'   => $marcas,
                ];
            })
            ->sortByDesc('venta') // si quieres ranking por unidades: ->sortByDesc('unidades')
            ->values()
            ->all();
    }


    public function exportarComprometidos()
    {
        $productos = collect($this->comprometidos)
            ->flatMap(function ($marca) {
                return collect($marca['referencias'])->map(function ($producto) use ($marca) {
                    return [
                        'referencia'  => $producto['referencia'],
                        'descripcion' => $producto['descripcion'],
                        'marca'       => $marca['marca'],
                        'unidades'    => (float) $producto['unidades'],
                        'valor'       => (float) $producto['valor'],
                    ];
                });
            })
            ->sortBy([
                ['marca', 'asc'],
                ['referencia', 'asc'],
            ])
            ->values()
            ->all();

        return Excel::download(
            new ProductosComprometidosExport($productos),
            'productos_comprometidos_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    public function render()
    {
        return view('livewire.admin.presupuestos-comercial-cumplimiento.index');
    }
}
