<?php

namespace App\Livewire\Admin\ReporteVisitas;

use Livewire\Component;
use App\Models\ReporteVisita;
use App\Models\User;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public $visitas;
    public $filtroVendedor = '';
    public $vendedores = [];
    public $filtroFechaInicio;
    public $filtroFechaFin;
    public $mostrarDebug = false;
    public $mapLoaded = false; // Controlar carga del mapa

    public function mount()
    {
        //$this->cargarVisitas();
        $this->visitas = collect();

        $this->vendedores = User::role('asesor')
            ->select('codigo_asesor', 'name')
            ->get()
            ->map(function ($user) {
                return (object)[
                    'codigo_asesor' => $user->codigo_asesor,
                    'nombre' => $user->name
                ];
            })
            ->toArray();
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['filtroVendedor','filtroFechaInicio','filtroFechaFin']);
        // Opcional: recargar listado por defecto
        // $this->cargarVisitas();
    }
    public function cargarVisitas()
    {
        $query = ReporteVisita::with('motivos');

        if ($this->filtroVendedor) {
            $query->where('vendedor', '=', $this->filtroVendedor );
        }

        if ($this->filtroFechaInicio) {
            $query->whereDate('created_at', '>=', $this->filtroFechaInicio);
        }

        if ($this->filtroFechaFin) {
            $query->whereDate('created_at', '<=', $this->filtroFechaFin);
        }

        $resultados = $query->orderBy('created_at', 'desc')->get();
        $this->visitas = $resultados;

    $this->dispatch('visitasActualizadas', $this->visitas->map(function ($visita) {
        return [
            'latitud' => $visita->latitud,
            'longitud' => $visita->longitud,
            'razon_social' => $visita->razon_social,
            'vendedor' => $visita->vendedor,
            'sucursal' => $visita->sucursal,
            'motivo' => optional($visita->motivos)->pluck('descripcion')->join(', '),
            'created_at' => $visita->created_at?->format('Y-m-d H:i'),
        ];
    })->values());
    }
    public function toggleDebug()
    {
        $this->mostrarDebug = !$this->mostrarDebug;
    }

    public function rangoRapido(string $tipo): void
    {
        $hoy = now()->toDateString();

        if ($tipo === 'hoy') {
            $this->filtroFechaInicio = $hoy;
            $this->filtroFechaFin    = $hoy;
            return;
        }

        if ($tipo === 'semana') {
            $this->filtroFechaInicio = now()->startOfWeek()->toDateString(); // Lunes
            $this->filtroFechaFin    = now()->endOfWeek()->toDateString();   // Domingo
            return;
        }

        if ($tipo === 'mes') {
            $this->filtroFechaInicio = now()->startOfMonth()->toDateString();
            $this->filtroFechaFin    = now()->endOfMonth()->toDateString();
            return;
        }
    }
    public function render()
    {
        $ordenadas = $this->visitas ? $this->visitas->sortBy('created_at')->values() : collect();

        return view('livewire.admin.reporte-visitas.index', [
            'visitas' => $this->visitas,
            'visitasOrdenadas' => $ordenadas,
        ]);
    }

}