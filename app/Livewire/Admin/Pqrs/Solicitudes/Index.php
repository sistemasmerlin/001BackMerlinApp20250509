<?php

namespace App\Livewire\Admin\Pqrs\Solicitudes;

use App\Models\Pqrs;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Filtros
    public string $q = '';               // cliente (nit / razón social)
    public string $asesor = '';          // código o nombre asesor
    public ?string $fechaInicio = null;  // YYYY-MM-DD
    public ?string $fechaFin = null;     // YYYY-MM-DD

    public int $perPage = 50;

    protected $queryString = [
        'q' => ['except' => ''],
        'asesor' => ['except' => ''],
        'fechaInicio' => ['except' => null],
        'fechaFin' => ['except' => null],
        'page' => ['except' => 1],
    ];

    public function updatingQ() { $this->resetPage(); }
    public function updatingAsesor() { $this->resetPage(); }
    public function updatingFechaInicio() { $this->resetPage(); }
    public function updatingFechaFin() { $this->resetPage(); }

    public function mount(): void
    {
        // Por defecto: últimas 50 sin rango, pero si quieres rango por defecto (ej. últimos 30 días), lo activas:
        // $this->fechaInicio = now()->subDays(30)->toDateString();
        // $this->fechaFin = now()->toDateString();
    }

    public function limpiar(): void
    {
        $this->reset(['q', 'asesor', 'fechaInicio', 'fechaFin']);
        $this->resetPage();
    }

    public function getTieneFiltrosProperty(): bool
    {
        return trim($this->q) !== ''
            || trim($this->asesor) !== ''
            || !empty($this->fechaInicio)
            || !empty($this->fechaFin);
    }

    public function render()
    {
        $q = trim($this->q);
        $asesor = trim($this->asesor);

        $rows = Pqrs::query()
            ->select([
                'id',
                'nit',
                'razon_social',
                'ciudad',
                'direccion',
                'cod_asesor',
                'nombre_asesor',
                'estado',
                'fecha_creacion',
                'fecha_revisado',
                'fecha_cierre',
                'numero_orm',
                'created_at',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('nit', 'like', "%{$q}%")
                       ->orWhere('razon_social', 'like', "%{$q}%");
                });
            })
            ->when($asesor !== '', function ($query) use ($asesor) {
                $query->where(function ($qq) use ($asesor) {
                    $qq->where('cod_asesor', 'like', "%{$asesor}%")
                       ->orWhere('nombre_asesor', 'like', "%{$asesor}%");
                });
            })
            ->when($this->fechaInicio, function ($query) {
                $query->whereDate('fecha_creacion', '>=', $this->fechaInicio);
            })
            ->when($this->fechaFin, function ($query) {
                $query->whereDate('fecha_creacion', '<=', $this->fechaFin);
            })
            ->orderByDesc('fecha_creacion')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.admin.pqrs.solicitudes.index', [
            'rows' => $rows,
        ]);
    }
}
