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
    public array $ventaPorMarca = [];   // global por marca
    public array $asesores = [];        // acordeón: asesor -> marcas

    public ?string $openAsesor = null;

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

    public function toggleAsesor(string $vendedor): void
    {
        $this->openAsesor = ($this->openAsesor === $vendedor) ? null : $vendedor;
    }
    private function buildPeriodos(int $meses = 12): array
    {
        $out = [];
        $base = Carbon::now()->startOfMonth();

        for ($i = 0; $i < $meses; $i++) {
            $p = $base->copy()->subMonths($i)->format('Ym');
            $label = $base->copy()->subMonths($i)->translatedFormat('F Y');
            $out[] = ['value' => $p, 'label' => ucfirst($label)];
        }

        return $out;
    }

    private function pct(float $venta, float $presu): float
    {
        return $presu > 0 ? round(($venta / $presu) * 100, 2) : 0;
    }

    public function cargar()
    {
        /** @var PresupuestoComercialController $ctrl */
        $ctrl = app(PresupuestoComercialController::class);

        $data = collect($ctrl->cumplimientoData($this->periodo));

        // 1) Normaliza detalle
        $this->rows = $data->map(fn ($r) => [
            'periodo'  => (string) ($r->periodo ?? ''),
            'vendedor' => trim((string) ($r->vendedor ?? '')),
            'marca'    => trim((string) ($r->marca ?? '')),
            'venta'    => (float)  ($r->venta ?? 0),
        ])->values()->all();

        $col = collect($this->rows);

        // 2) Totales globales
        $this->totalVenta = (float) $col->sum('venta');

        // 3) Presupuesto global (todos los asesores + todas las marcas)
        $this->totalPresupuesto = (float) PresupuestoComercial::query()
            ->where('periodo', $this->periodo)
            ->sum('presupuesto');

        $this->cumplimientoTotal = $this->pct($this->totalVenta, $this->totalPresupuesto);

        // 4) Venta global por marca (NO requiere presupuesto)
        $this->ventaPorMarca = $col
            ->groupBy('marca')
            ->map(fn (Collection $g, $marca) => [
                'marca' => $marca,
                'venta' => (float) $g->sum('venta'),
            ])
            ->sortByDesc('venta')
            ->values()
            ->all();

        // 5) Vincular vendedor -> nombre asesor
        $vendedores = $col->pluck('vendedor')->filter()->unique()->values()->all();

        $mapNombres = User::query()
            ->whereIn('codigo_asesor', $vendedores)
            ->pluck('name', 'codigo_asesor')
            ->map(fn($n) => trim((string)$n))
            ->toArray();

        // 6) Acordeón por asesor con detalle por marca
        $this->asesores = $col
            ->groupBy('vendedor')
            ->map(function (Collection $g, $vendedor) use ($mapNombres) {
                $totalAsesor = (float) $g->sum('venta');

                $marcas = $g->groupBy('marca')
                    ->map(fn (Collection $x, $marca) => [
                        'marca' => $marca,
                        'venta' => (float) $x->sum('venta'),
                    ])
                    ->sortByDesc('venta')
                    ->values()
                    ->all();

                return [
                    'vendedor' => $vendedor,
                    'nombre'   => $mapNombres[$vendedor] ?? 'Sin nombre',
                    'venta'    => $totalAsesor,
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
