<?php

namespace App\Livewire\Admin\Noticias;

use Livewire\Component;
use App\Models\Noticias;

class Index extends Component
{
    public $noticias;
    public $modal = false;
    public $confirmarEliminacion = false;
    public $noticiaId;
    public $titulo;
    public $detalle;
    public $fecha_activacion;
    public $modoEditar = false;

    public function mount()
    {
        $this->cargarNoticias();
    }

    public function cargarNoticias()
    {
        $this->noticias = Noticias::orderBy('id', 'desc')->get();
    }

    public function crear()
    {
        $this->resetCampos();
        $this->modoEditar = false;
        $this->modal = true;
    }

    public function editar($id)
    {
        $noticia = Noticias::findOrFail($id);
        $this->noticiaId = $noticia->id;
        $this->titulo = $noticia->titulo;
        $this->detalle = $noticia->detalle;
        $this->fecha_activacion = $noticia->fecha_activacion;
        $this->modoEditar = true;
        $this->modal = true;
    }

    public function guardar()
    {
        $this->validate([
            'titulo' => 'required|string|max:255',
            'detalle' => 'required|string',
            'fecha_activacion' => 'required|date',
        ]);

        if ($this->modoEditar) {
            $noticia = Noticias::find($this->noticiaId);
            $noticia->update([
                'titulo' => $this->titulo,
                'detalle' => $this->detalle,
                'fecha_activacion' => $this->fecha_activacion,
            ]);
            session()->flash('success', 'Noticia actualizada.');
        } else {
            Noticias::create([
                'titulo' => $this->titulo,
                'detalle' => $this->detalle,
                'fecha_activacion' => $this->fecha_activacion,
            ]);
            session()->flash('success', 'Noticia creada.');
        }

        $this->modal = false;
        $this->cargarNoticias();
    }

    public function confirmarEliminar($id)
    {
        $this->noticiaId = $id;
        $this->confirmarEliminacion = true;
    }

    public function eliminar()
    {
        Noticias::destroy($this->noticiaId);
        $this->confirmarEliminacion = false;
        $this->cargarNoticias();
        session()->flash('success', 'Noticia eliminada.');
    }

    public function resetCampos()
    {
        $this->noticiaId = null;
        $this->titulo = '';
        $this->detalle = '';
        $this->fecha_activacion = '';
    }

    public function render()
    {
        return view('livewire.admin.noticias.index');
    }
}

