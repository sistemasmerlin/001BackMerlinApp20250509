<?php

namespace App\Livewire\Admin\BackOrder;

use App\Models\Backorder;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BackordersExport;

class Index extends Component
{
    public $backorders = [];
    public $modalBackorder = null;
    public $fechaInicio;
    public $fechaFin;

    public function mount()
    {
        $this->backorders = Backorder::with(['pedido', 'detalles'])->get();
    }

    public function mostrarDetalle($id)
    {
        $this->modalBackorder = $this->backorders->firstWhere('id', $id);
    }

    public function cerrarDetalle()
    {
        $this->modalBackorder = null;
    }

    public function render()
    {
        return view('livewire.admin.back-order.index');
    }

    public function filtrar()
    {
        $query = Backorder::with(['pedido', 'detalles']);

        if ($this->fechaInicio) {
            $query->whereDate('created_at', '>=', $this->fechaInicio);
        }

        if ($this->fechaFin) {
            $query->whereDate('created_at', '<=', $this->fechaFin);
        }

        $this->backorders = $query->get();
    }

    public function exportarExcel()
    {
        return Excel::download(new BackordersExport($this->fechaInicio, $this->fechaFin), 'backorders.xlsx');
    }
}