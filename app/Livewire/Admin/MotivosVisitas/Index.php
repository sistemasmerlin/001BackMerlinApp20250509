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
    public $motivoId;


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

    public function editar($id){
        $motivo = MotivosVisita::findOrFail($id);
        $this->motivoId = $motivo->id;
        $this->motivo = $motivo->motivo;
        $this->modoEditar = true;
        $this->modal = true;
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
       // $this->mount();

        return redirect(request()->header('Referer'));
    }

    public function confirmarEliminar($id)
    {
        $motivo = MotivosVisita::findOrFail($id);


        $motivo->delete();
        $this->motivo = MotivosVisita::all(); // Refrescar lista
        session()->flash('success', 'Motivo eliminado correctamente.');

        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.admin.motivos-visitas.index');
    }
}
