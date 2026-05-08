<?php

namespace App\Livewire\Admin\SolicitudesCredito;

use Livewire\Component;
use App\Models\SolicitudCredito;
use App\Models\TipoDocumentoCredito;

class Detalle extends Component
{
    public SolicitudCredito $solicitud;

    public $tiposDocumentos = [];

    public function mount(SolicitudCredito $solicitud): void
    {
        $this->solicitud = $solicitud->load([
            'user',
            'referencias',
            'direcciones',
            'documentos.tipoDocumento',
            'documentos.aprobadoPor',
        ]);

        $this->tiposDocumentos = TipoDocumentoCredito::where('estado', true)
            ->orderBy('orden')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.solicitudes-credito.detalle');
    }
}