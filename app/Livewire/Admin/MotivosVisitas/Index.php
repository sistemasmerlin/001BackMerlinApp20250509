<?php

namespace App\Livewire\Admin\MotivosVisitas;

use Livewire\Component;
use App\Models\MotivosVisita;

class Index extends Component
{
    public $motivos;
    public $modal = false;
    public $modoEditar = false;
    public $motivo;

    public function cargarMotivos()
    {
        $this->motivos = MotivosVisita::orderBy('id', 'desc')->get();
    }

    public function mount()
    {
        $this->cargarMotivos();
    }


    public function crear()
    {
        $this->resetCampos();
        $this->modoEditar = false;
        $this->modal = true;
    }

    public function resetCampos()
    {
        $this->motivoId = null;
        $this->motivo = '';
    }

    public function guardar()
    {
        $this->validate([
            'motivo' => 'required|string|max:255',
        ]);

        if ($this->modoEditar) {
            $motivos = MotivosVisita::find($this->motivoId);
            $motivos->update([
                'motivo' => $this->motivo,
            ]);
            session()->flash('success', 'Noticia actualizada.');
        } else {
            MotivosVisita::create([
                'motivo' => $this->motivo,
            ]);
            session()->flash('success', 'Motivo creado.');
        }

        $this->modal = false;
        $this->mount();
    }

    public function render()
    {
        return view('livewire.admin.motivos-visitas.index');
    }
}
