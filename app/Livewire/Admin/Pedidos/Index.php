<?php

namespace App\Livewire\Admin\Pedidos;


use App\Models\Pedido;

use Livewire\Component;

class Index extends Component
{
    public $pedidos;

    public function mount()
    {
        $this->pedidos = Pedido::with('direccionEnvio')->get();

        //dd($this->pedidos );
    }

    public function render()
    {
        return view('livewire.admin.pedidos.index');
    }
}
