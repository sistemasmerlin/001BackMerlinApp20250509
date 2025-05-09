<?php

namespace App\Livewire\Admin\Permisos;

use Livewire\Component;
use Spatie\Permission\Models\Permission;

class Index extends Component
{
    public $permisos;
    public $nombre;
    public $permiso_id = null;
    public $openModal = false;

    protected $rules = [
        'nombre' => 'required|string|unique:permissions,name',
    ];

    public function mount()
    {
        $this->cargarPermisos();
    }

    public function cargarPermisos()
    {
        $this->permisos = Permission::all();
    }

    public function abrirModal()
    {
        $this->reset(['nombre', 'permiso_id']);
        $this->openModal = true;
    }

    public function guardar()
    {
        $this->validate();

        Permission::create(['name' => $this->nombre]);

        $this->resetModal();
        session()->flash('success', 'Permiso creado correctamente');
    }

    public function editar($id)
    {
        $permiso = Permission::findOrFail($id);
        $this->permiso_id = $permiso->id;
        $this->nombre = $permiso->name;
        $this->openModal = true;
    }

    public function actualizar()
    {
        $this->validate([
            'nombre' => 'required|string|unique:permissions,name,' . $this->permiso_id,
        ]);

        $permiso = Permission::findOrFail($this->permiso_id);
        $permiso->update(['name' => $this->nombre]);

        $this->resetModal();
        session()->flash('success', 'Permiso actualizado correctamente');
    }

    public function resetModal()
    {
        $this->reset(['nombre', 'permiso_id', 'openModal']);
        $this->cargarPermisos();
    }

    public function render()
    {
        return view('livewire.admin.permisos.index');
    }
}
