<?php

namespace App\Livewire\Admin\RelacionAsesores;

use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public $usuarios; // Usuarios con rol Televentas o Coordinador
    public $asesores; // Usuarios con rol Asesor
    public $relaciones = [];

    public function mount()
    {
        $this->usuarios = User::role(['Televentas', 'Coordinador Comercial'])->get();
        $this->asesores = User::role('Asesor')->get();

        foreach ($this->usuarios as $usuario) {
            $this->relaciones[$usuario->id] = $usuario->relacionados()->pluck('users.id')->toArray();
        }
    }

    public function toggleRelacion($asesorId, $relacionadoId)
    {
        logger("toggleRelacion ejecutado", [
            'asesorId' => $asesorId,
            'relacionadoId' => $relacionadoId,
        ]);

        //return $asesorId;
        //dd($asesorId);
        $asesor = User::find($asesorId);

        if ($asesor->relacionados()->where('users.id', $relacionadoId)->exists()) {
            $asesor->relacionados()->detach($relacionadoId);
        } else {
            $asesor->relacionados()->attach($relacionadoId);
        }

        // Refrescar relaciones
        $this->relaciones[$asesorId] = $asesor->relacionados()->pluck('users.id')->toArray();
    }

    public function render()
    {
        return view('livewire.admin.relacion-asesores.index', [
            'asesoresDisponibles' => $this->asesores,
        ]);
    }
}
