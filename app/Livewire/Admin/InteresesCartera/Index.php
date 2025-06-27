<?php

namespace App\Livewire\Admin\InteresesCartera;

use Livewire\Component;
use App\Models\InteresesCartera;

class Index extends Component
{
        public $facturas;

    public function mount()
    {
        $this->facturas = InteresesCartera::get();

       // dd($this->facturas );
    }

    public function render()
    {
        return view('livewire.admin.intereses-cartera.index');
    }
}
