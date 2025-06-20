<?php

namespace App\Livewire\Admin\Pedidos;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Livewire\Component;

class Detalle extends Component
{
    public $pedido;
    public $detalles;
    public $descuentoGlobal;

    public function mount(Pedido $pedido)
    {

        $this->pedido = $pedido;
        $this->detalles = $pedido->detalles()->get()->map(function ($detalle) {
            return [
                'id' => $detalle->id,
                'pedido_id' => $detalle->pedido_id,
                'referencia' => $detalle->referencia,
                'descripcion' => $detalle->descripcion,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'descuento' => $detalle->descuento,
            ];
        })->toArray();
    }
    public function render()
    {
        return view('livewire.admin.pedidos.detalle');
    }

    public function guardarLinea($index)
    {
        $detalle = $this->detalles[$index];
        $registro = DetallePedido::find($detalle['id']);

        if ($registro) {
            $registro->cantidad = $detalle['cantidad'];
            $registro->descuento = $detalle['descuento'];
            $registro->save();

            session()->flash('success', 'Producto actualizado');
        }
    }

    public function aplicarDescuentoGlobal()
    {
        foreach ($this->detalles as $i => $detalle) {
            $registro = DetallePedido::find($detalle['id']);
            if ($registro) {
                $registro->descuento = $this->descuentoGlobal;
                $registro->save();

                $this->detalles[$i]['descuento'] = $this->descuentoGlobal;
            }
        }

        session()->flash('success', 'Descuento aplicado a todos los productos');
    }
}
