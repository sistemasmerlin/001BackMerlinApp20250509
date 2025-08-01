<?php

namespace App\Livewire\Admin\ReporteVisitas;

use Livewire\Component;
use App\Models\ReporteVisita;

class Index extends Component
{
    public $visitas;
    public $filtroVendedor = '';
    public $filtroFechaInicio;
    public $filtroFechaFin;
    public $mostrarDebug = false;
    public $mapLoaded = false; // Controlar carga del mapa

    public function mount()
    {
        $this->cargarVisitas();
    }

    public function cargarVisitas()
    {
        $query = ReporteVisita::with('motivos');

        if ($this->filtroVendedor) {
            $query->where('vendedor', 'like', '%' . $this->filtroVendedor . '%');
        }

        if ($this->filtroFechaInicio) {
            $query->whereDate('created_at', '>=', $this->filtroFechaInicio);
        }

        if ($this->filtroFechaFin) {
            $query->whereDate('created_at', '<=', $this->filtroFechaFin);
        }

        $this->visitas = $query->orderBy('created_at', 'desc')->get();
    }

    public function toggleDebug()
    {
        $this->mostrarDebug = !$this->mostrarDebug;
    }

    public function render()
    {
        if (!$this->visitas) {
            $this->cargarVisitas();
        }

        $ordenadas = $this->visitas ? $this->visitas->sortBy('created_at')->values() : collect();

        return view('livewire.admin.reporte-visitas.index', [
            'visitas' => $this->visitas,
            'visitasOrdenadas' => $ordenadas,
        ]);
    }

}