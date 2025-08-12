<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;

class Index extends Component
{
    
    public $usuarios, $name, $email, $password, $cedula, $codigo_asesor, $codigo_recibos, $usuario_id;
    public $roles = [], $rolesSeleccionados = [];
    public $nuevaPassword;
    public $mostrarPassword = false;
    public bool $openModal = false;
    public bool $modoEditar = false;

    public function mount()
    {
        $this->usuarios = User::with('roles')->get();
        $this->roles = Role::all();

    }

    public function abrirModal()
    {
        $this->reset(['name', 'email', 'password', 'cedula', 'codigo_asesor', 'codigo_recibos', 'rolesSeleccionados', 'usuario_id']);
        $this->modoEditar = false;
        $this->openModal = true;
    }

    public function actualizarUsuario()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $this->usuario_id,
            'cedula' => 'required|unique:users,cedula,' . $this->usuario_id,
            'codigo_asesor' => 'required|unique:users,codigo_asesor,' . $this->usuario_id,
            'codigo_recibos' => 'required|unique:users,codigo_recibos,' . $this->usuario_id,
        ]);

        $usuario = User::find($this->usuario_id);

        $usuario->update([
            'name' => $this->name,
            'email' => $this->email,
            'cedula' => $this->cedula,
            'codigo_asesor' => $this->codigo_asesor,
            'codigo_recibos' => $this->codigo_recibos,
        ]);

        if (!empty($this->nuevaPassword)) {
            $usuario->update([
                'password' => Hash::make($this->nuevaPassword),
            ]);
        }
        
        // ✅ Asegúrate de convertir los IDs a nombres de roles
        $nombresRoles = Role::whereIn('id', $this->rolesSeleccionados)->pluck('name')->toArray();
        $usuario->syncRoles($nombresRoles);

        $this->reset(['openModal', 'modoEditar', 'usuario_id', 'rolesSeleccionados']);
        session()->flash('success', 'Usuario actualizado correctamente');

        return redirect(request()->header('Referer'));
    }

    public function editarUsuario($id)
    {
        $usuario = User::with('roles')->findOrFail($id);

        $this->usuario_id = $usuario->id;
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        $this->cedula = $usuario->cedula;
        $this->codigo_asesor = $usuario->codigo_asesor;
        $this->codigo_recibos = $usuario->codigo_recibos;
        $this->rolesSeleccionados = $usuario->roles->pluck('id')->toArray();

        $this->modoEditar = true;
        $this->openModal = true;
    }

    public function guardarUsuario()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $this->usuario_id,
            'password' => $this->modoEditar ? 'nullable|min:6' : 'required|min:6',
            'cedula' => 'required|unique:users,cedula,' . $this->usuario_id,
            'codigo_asesor' => 'required|unique:users,codigo_asesor,' . $this->usuario_id,
            'codigo_recibos' => 'required|unique:users,codigo_recibos,' . $this->usuario_id,
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'cedula' => $this->cedula,
            'codigo_asesor' => $this->codigo_asesor,
            'codigo_recibos' => $this->codigo_recibos,
        ];

        if (!$this->modoEditar) {
            $data['password'] = Hash::make($this->password);
            $usuario = User::create($data);
        } else {
            $usuario = User::findOrFail($this->usuario_id);
            $usuario->update($data);
        }

        $nombresRoles = Role::whereIn('id', $this->rolesSeleccionados)->pluck('name')->toArray();
        $usuario->syncRoles($nombresRoles);

        $this->reset(['openModal', 'modoEditar', 'usuario_id']);
        session()->flash('success', $this->modoEditar ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente');

        $this->usuarios = User::with('roles')->get();
        
        return redirect(request()->header('Referer'));
    }

    public function eliminarUsuario($id)
    {
        $usuario = User::findOrFail($id);

        // Por seguridad podrías evitar que elimine a sí mismo
        if (auth()->id() === $usuario->id) {
            session()->flash('error', 'No puedes eliminar tu propio usuario.');
            return;
        }

        $usuario->delete();

        $this->usuarios = User::with('roles')->get();
        session()->flash('success', 'Usuario eliminado correctamente.');
    }

    public function render()
    {

        return view('livewire.admin.usuarios.index');

    }
    
}
