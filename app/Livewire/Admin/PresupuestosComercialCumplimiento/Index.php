<?php

namespace App\Livewire\Admin\PresupuestosComercialCumplimiento;

use Livewire\Component;
use Carbon\Carbon;
use App\Http\Controllers\Admin\PresupuestoComercialController;
use Illuminate\Support\Collection;

class Index extends Component
{
    public string $periodo = '';

    /** combos de periodos */
    public array $periodos = [];

    /** data cruda */
    public array $rows = [];

    /** totales */
    public float $totalVenta = 0;

    /** tablas */
    public array $totalesPorAsesor = [];         // vendedor => venta
    public array $totalesPorAsesorMarca = [];    // vendedor+marca => venta

    public function mount(string $periodo)
    {
        $this->periodo = $periodo ?: now()->format('Ym');
        $this->periodos = $this->buildPeriodos(18); // últimos 18 meses
        $this->cargar();
    }

    public function updatedPeriodo()
    {
        $this->cargar();
    }

    private function buildPeriodos(int $meses = 12): array
    {
        $out = [];
        $base = Carbon::now()->startOfMonth();

        for ($i = 0; $i < $meses; $i++) {
            $p = $base->copy()->subMonths($i)->format('Ym');   // 202602
            $label = $base->copy()->subMonths($i)->translatedFormat('F Y'); // febrero 2026
            $out[] = ['value' => $p, 'label' => ucfirst($label)];
        }

        return $out;
    }

    public function cargar()
    {
        /** @var PresupuestoComercialController $ctrl */
        $ctrl = app(PresupuestoComercialController::class);

        $data = collect($ctrl->cumplimientoData($this->periodo));

        // Normaliza a array simple (por si viene como stdClass)
        $this->rows = $data->map(fn ($r) => [
            'periodo'  => (string) ($r->periodo ?? ''),
            'vendedor' => (string) ($r->vendedor ?? ''),
            'marca'    => (string) ($r->marca ?? ''),
            'venta'    => (float)  ($r->venta ?? 0),
        ])->values()->all();

        $col = collect($this->rows);

        $this->totalVenta = (float) $col->sum('venta');

        // Total por asesor
        $this->totalesPorAsesor = $col
            ->groupBy('vendedor')
            ->map(fn (Collection $g, $vendedor) => [
                'vendedor' => $vendedor,
                'venta'    => (float) $g->sum('venta'),
            ])
            ->sortByDesc('venta')
            ->values()
            ->all();

        // Total por asesor y marca
        $this->totalesPorAsesorMarca = $col
            ->groupBy(fn ($r) => $r['vendedor'] . '||' . $r['marca'])
            ->map(function (Collection $g, $key) {
                [$vendedor, $marca] = explode('||', $key);
                return [
                    'vendedor' => $vendedor,
                    'marca'    => $marca,
                    'venta'    => (float) $g->sum('venta'),
                ];
            })
            ->sortBy(fn ($x) => $x['vendedor'])
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.presupuestos-comercial-cumplimiento.index');
    }
}
