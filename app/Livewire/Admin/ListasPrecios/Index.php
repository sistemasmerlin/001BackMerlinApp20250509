<?php

namespace App\Livewire\Admin\ListasPrecios;

use Illuminate\Support\Facades\DB;
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

        $exportaciones = DB::table('listas_precios_exportaciones')
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view(
            'livewire.admin.listas-precios.index',
            compact('exportaciones')
        );
    }
}