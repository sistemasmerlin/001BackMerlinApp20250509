<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Index extends Component
{
    public $roles, $nombre, $rol_id, $permisos = [], $permisosSeleccionados = [], $openModal = false;

    protected $rules = [
        'nombre' => 'required|unique:roles,name',
        'permisosSeleccionados' => 'array'
    ];

    public function mount()
    {
        $this->roles = Role::with('permissions')->get();
        $this->permisos = Permission::all();
    }

    public function abrirModal()
    {
        $this->reset(['nombre', 'rol_id', 'permisosSeleccionados']);
        $this->openModal = true;
    }

    public function editar($id)
    {
        $rol = Role::findOrFail($id);
        $this->rol_id = $rol->id;
        $this->nombre = $rol->name;
        $this->permisosSeleccionados = $rol->permissions()->pluck('id')->toArray();
        $this->openModal = true;
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required|unique:roles,name,' . $this->rol_id,
        ]);
    
        $rol = $this->rol_id
            ? Role::findOrFail($this->rol_id)
            : Role::create(['name' => $this->nombre]);
    
        // Convertir los IDs a nombres de permisos
        $nombresPermisos = Permission::whereIn('id', $this->permisosSeleccionados)->pluck('name')->toArray();
    
        $rol->syncPermissions($nombresPermisos);
    
        $this->resetModal();
        session()->flash('success', 'Rol guardado correctamente');
    }

    private function resetModal()
    {
        $this->reset(['nombre', 'rol_id', 'permisosSeleccionados', 'openModal']);
        $this->cargarRoles(); // Opcional, si deseas recargar la lista de roles después
    }

    public function eliminarRol($id)
    {
        $rol = Role::findOrFail($id);

        // Puedes proteger ciertos roles si deseas
        if (in_array($rol->name, ['Administrador General', 'Asesor'])) {
            session()->flash('error', 'No puedes eliminar este rol protegido.');
            return;
        }

        $rol->delete();
        $this->roles = Role::all(); // Recarga lista
        session()->flash('success', 'Rol eliminado correctamente.');
    }

    public function cargarRoles()
    {
        $this->roles = Role::with('permissions')->get();
    }

    public function render()
    {
        return view('livewire.admin.roles.index', [
            'permisos' => Permission::all()
        ]);
    }

    public function eliminarPermiso($id)
    {
        $permiso = Permission::findOrFail($id);

        // Opcional: proteger permisos especiales
        if (in_array($permiso->name, ['admin'])) {
            session()->flash('error', 'No puedes eliminar este permiso protegido.');
            return;
        }

        $permiso->delete();
        $this->permisos = Permission::all(); // Refrescar lista
        session()->flash('success', 'Permiso eliminado correctamente.');
    }
}
