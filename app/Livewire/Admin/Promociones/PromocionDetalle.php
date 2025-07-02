<?php

namespace App\Livewire\Admin\Promociones;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportPromociones;


class PromocionDetalle extends Component
{

    use WithFileUploads;

    public $excel_promo;

    protected $rules = [
        'excel_promo' => 'required|file|mimes:xls,xlsx|max:2048',
    ];

    public function importarPromo()
    {
        $this->validate();

        Excel::import(new ImportPromociones, $this->excel_promo->getRealPath());

        session()->flash('success', 'Archivo importado correctamente.');
        $this->reset('excel_promo'); // limpia el input

    }

    public function render()
    {
        return view('livewire.admin.promociones.promocion-detalle');
    }
}
