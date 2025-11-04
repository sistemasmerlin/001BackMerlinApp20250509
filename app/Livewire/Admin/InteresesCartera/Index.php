<?php

namespace App\Livewire\Admin\InteresesCartera;

use Livewire\Component;
use App\Models\InteresesCartera;

class Index extends Component
{
    public ?string $inicio = null;
    public ?string $fin    = null;

    public array $facturas = [];

    public function mount(): void
    {
        $hoy = now()->toDateString();
        $this->inicio = $hoy;
        $this->fin    = $hoy;

        $this->cargarFacturas();
    }

    public function aplicarFiltros(): void
    {
        $this->validate([
            'inicio' => ['required','date'],
            'fin'    => ['required','date','after_or_equal:inicio'],
        ]);

        $this->cargarFacturas();
    }

    public function limpiarFiltros(): void
    {
        $hoy = now()->toDateString();
        $this->inicio = $hoy;
        $this->fin    = $hoy;

        $this->cargarFacturas();
    }

    public function updatedInicio(): void
    {
        if ($this->fin && $this->fin < $this->inicio) {
            $this->fin = $this->inicio;
        }
    }

    public function updatedFin(): void
    {
        if ($this->inicio && $this->fin < $this->inicio) {
            $this->inicio = $this->fin;
        }
    }

    private function cargarFacturas(): void
    {
        $query = InteresesCartera::query();

        if ($this->inicio && $this->fin) {
            $query->whereBetween('fecha_hoy', [$this->inicio, $this->fin]);
            // Si prefieres por fecha de factura:
            // $query->whereBetween('fecha_factura', [$this->inicio, $this->fin]);
        } elseif ($this->inicio) {
            $query->whereDate('fecha_hoy', '>=', $this->inicio);
        } elseif ($this->fin) {
            $query->whereDate('fecha_hoy', '<=', $this->fin);
        }

        $this->facturas = $query
            ->orderByDesc('fecha_hoy')
            ->get()
            ->map(fn($f) => [
                'prefijo'               => $f->prefijo,
                'consecutivo'           => $f->consecutivo,
                'nit'                   => $f->nit,
                'razon_social'          => $f->razon_social,
                'valor_base'            => (float) $f->valor_base,
                'impuestos'             => (float) $f->impuestos,
                'valor_factura'         => (float) $f->valor_factura,
                'abono'                 => (float) $f->abono,
                'saldo'                 => (float) $f->saldo,
                'fecha_factura'         => (string) $f->fecha_factura,
                'fecha_hoy'             => (string) $f->fecha_hoy,
                'dias_transcurridos'    => (int) $f->dias_transcurridos,
                'asesor'                => (string) $f->asesor,
                'condicion_pago'        => (string) $f->condicion_pago,
                'valor_diario_interes'  => (float) $f->valor_diario_interes,
                'valor_acumulado_interes'=> (float) $f->valor_acumulado_interes,
            ])
            ->toArray();

        // Enviar datos a JS para que DataTables renderice
        $this->dispatch('facturas-actualizadas', facturas: $this->facturas);
    }

    public function render()
    {
        return view('livewire.admin.intereses-cartera.index');
    }
}
