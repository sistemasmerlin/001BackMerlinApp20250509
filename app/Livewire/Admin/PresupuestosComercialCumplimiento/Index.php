<?php

namespace App\Livewire\Admin\PresupuestosComercialCumplimiento;

use Livewire\Component;
use Carbon\Carbon;
use App\Http\Controllers\Admin\PresupuestoComercialController;
use Illuminate\Support\Collection;
use App\Models\PresupuestoComercial;
use App\Models\User;

// ...

class Index extends Component
{
    public string $periodo = '';
    public array $periodos = [];

    public array $rows = [];

    public float $totalVenta = 0;
    public float $totalPresupuesto = 0;
    public float $cumplimientoTotal = 0;

    public array $ventaPorMarca = [];
    public array $asesores = [];

    public ?string $openAsesor = null;     // 👈 para el acordeón
    public string $periodoLabel = '';      // 👈 “Febrero 2026”

    public function mount(?string $periodo = null)
    {
        $this->periodo = $periodo ?: now()->format('Ym');
        $this->periodos = $this->buildPeriodos(18);
        $this->cargar();
    }

    public function updatedPeriodo()
    {
        $this->openAsesor = null; // cierra acordeón al cambiar periodo
        $this->cargar();
    }

    public function toggleAsesor(string $vendedor): void
    {
        $this->openAsesor = ($this->openAsesor === $vendedor) ? null : $vendedor;
    }

    private function buildPeriodos(int $meses = 12): array
    {
        Carbon::setLocale('es'); // 👈 español

        $out = [];
        $base = Carbon::now()->startOfMonth();

        for ($i = 0; $i < $meses; $i++) {
            $dt = $base->copy()->subMonths($i);
            $p = $dt->format('Ym'); // 202602
            $label = ucfirst($dt->translatedFormat('F Y')); // Febrero 2026
            $out[] = ['value' => $p, 'label' => $label];
        }

        return $out;
    }

    private function pct(float $venta, float $presu): float
    {
        return $presu > 0 ? round(($venta / $presu) * 100, 2) : 0;
    }

    public function cargar()
    {
        Carbon::setLocale('es');
        $this->periodoLabel = ucfirst(
            Carbon::createFromFormat('Ym', $this->periodo)->translatedFormat('F Y')
        );

        /** @var PresupuestoComercialController $ctrl */
        $ctrl = app(PresupuestoComercialController::class);
        $data = collect($ctrl->cumplimientoData($this->periodo));

        $this->rows = $data->map(fn ($r) => [
            'periodo'  => (string) ($r->periodo ?? ''),
            'vendedor' => trim((string) ($r->vendedor ?? '')),
            'marca'    => trim((string) ($r->marca ?? '')),
            'venta'    => (float)  ($r->venta ?? 0),
        ])->values()->all();

        $col = collect($this->rows);

        $this->totalVenta = (float) $col->sum('venta');

        $this->totalPresupuesto = (float) PresupuestoComercial::query()
            ->where('periodo', $this->periodo)
            ->sum('presupuesto');

        $this->cumplimientoTotal = $this->pct($this->totalVenta, $this->totalPresupuesto);

        $this->ventaPorMarca = $col->groupBy('marca')
            ->map(fn ($g, $marca) => ['marca' => $marca, 'venta' => (float) $g->sum('venta')])
            ->sortByDesc('venta')->values()->all();

        $vendedores = $col->pluck('vendedor')->filter()->unique()->values()->all();

        $mapNombres = User::query()
            ->whereIn('codigo_asesor', $vendedores)
            ->pluck('name', 'codigo_asesor')
            ->map(fn($n) => trim((string)$n))
            ->toArray();

        $this->asesores = $col->groupBy('vendedor')
            ->map(function ($g, $vendedor) use ($mapNombres) {

                $marcas = $g->groupBy('marca')
                    ->map(fn ($x, $marca) => ['marca' => $marca, 'venta' => (float) $x->sum('venta')])
                    ->sortByDesc('venta')->values()->all();

                return [
                    'vendedor' => $vendedor,
                    'nombre'   => $mapNombres[$vendedor] ?? 'Sin nombre',
                    'venta'    => (float) $g->sum('venta'),
                    'marcas'   => $marcas,
                ];
            })
            ->sortByDesc('venta')->values()->all();
    }

    public function render()
    {
        return view('livewire.admin.presupuestos-comercial-cumplimiento.index');
    }
}

