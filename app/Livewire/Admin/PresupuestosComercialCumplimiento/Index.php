<?php

namespace App\Livewire\Admin\PresupuestosComercialCumplimiento;

use Livewire\Component;
use Carbon\Carbon;
use App\Http\Controllers\Admin\PresupuestoComercialController;
use Illuminate\Support\Collection;
use App\Models\PresupuestoComercial;
use App\Models\User;

class Index extends Component
{
    public string $periodo = '';
    public array $periodos = [];

    // Data cruda (detalle)
    public array $rows = [];

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

    public function toggleComprometidos(): void
    {
        $this->openComprometidos = ! $this->openComprometidos;
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

        // 0) Comprometidos
        $comp = collect($ctrl->comprometidosData());
        $this->comprometidos = $comp->map(fn($r) => [
            'marca' => trim((string)($r->marca ?? '')),
            'unidades' => (float)($r->unidades_comprometidas ?? 0),
            'valor' => (float)($r->valor_bruto_menos_dscto_linea ?? 0),
        ])->values()->all();

        // 1) Ventas
        $data = collect($ctrl->cumplimientoData($this->periodo));

        $this->rows = $data->map(fn ($r) => [
            'periodo'  => (string) ($r->periodo ?? ''),
            'vendedor' => trim((string) ($r->vendedor ?? '')),
            'marca'    => trim((string) ($r->marca ?? '')),
            'venta'    => (float)  ($r->venta ?? 0),
        ])->values()->all();

        $col = collect($this->rows);

        // 2) Totales globales
        $this->totalVenta = (float) $col->sum('venta');

        $this->totalPresupuesto = (float) PresupuestoComercial::query()
            ->where('periodo', $this->periodo)
            ->sum('presupuesto');

        $this->cumplimientoTotal = $this->pct($this->totalVenta, $this->totalPresupuesto);

        // 3) Venta por marca
        $this->ventaPorMarca = $col
            ->groupBy('marca')
            ->map(fn (Collection $g, $marca) => [
                'marca' => $marca,
                'venta' => (float) $g->sum('venta'),
            ])
            ->sortByDesc('venta')
            ->values()
            ->all();

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
                    ->map(fn (Collection $x, $marca) => [
                        'marca' => $marca,
                        'venta' => (float) $x->sum('venta'),
                    ])
                    ->sortByDesc('venta')
                    ->values()
                    ->all();

                return [
                    'vendedor' => (string)$vendedor,
                    'nombre'   => $mapNombres[$vendedor] ?? 'Sin nombre',
                    'venta'    => (float) $g->sum('venta'),
                    'marcas'   => $marcas,
                ];
            })
            ->sortByDesc('venta')
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.presupuestos-comercial-cumplimiento.index');
    }
}
