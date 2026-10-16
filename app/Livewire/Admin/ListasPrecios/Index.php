<?php

namespace App\Livewire\Admin\ListasPrecios;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    public function mount(): void
    {
        Gate::authorize('Administrar imagenes productos');
    }

    public function render()
    {
        Gate::authorize('Administrar imagenes productos');

        return view('livewire.admin.listas-precios.index');
    }
}