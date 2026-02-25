<?php

namespace App\Livewire\Admin\Pqrs\Solicitudes;

use App\Models\Pqrs;
use Livewire\Component;

class Detalles extends Component
{
    public Pqrs $pqrs;

    public function mount(Pqrs $pqrs): void
    {
        $this->pqrs = $pqrs->load([
            'orm',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.pqrs.solicitudes.detalles');
    }
}