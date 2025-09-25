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
                'marca' => $detalle->marca,
                'descripcion' => $detalle->descripcion,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'descuento' => $detalle->descuento,
                'subtotal' => $detalle->subtotal,
            ];
        })->toArray();
    }
    public function render()
    {
        return view('livewire.admin.pedidos.detalle');
    }

    public function guardarLinea($index){

                $detalle = $this->detalles[$index];

                /* $validated = validator($detalle, [
                    'cantidad' => 'required|integer|min:1',
                    'descuento' => 'nullable|numeric|min:0',
                ])->validate(); */

                if ($detalle['cantidad'] < 1) {
                    session()->flash('error', 'La cantidad mínima es 1 en todos los productos.');
                    return redirect(request()->header('Referer'));
                }

                $registro = DetallePedido::find($detalle['id']);

                if ($registro) {
                    $registro->cantidad = $validated['cantidad'];
                    $registro->descuento = $validated['descuento'] ?? 0;
                    $registro->save();

                    session()->flash('success', 'Producto actualizado');
                }

                return redirect(request()->header('Referer'));
            }

    public function aplicarDescuentoGlobal()
    {
        if (is_null($this->descuentoGlobal) || $this->descuentoGlobal === '') {
            session()->flash('error', 'Valor vacío: ingresa un descuento para aplicarlo.');
            return;
        }

        foreach ($this->detalles as $i => $detalle) {
            $registro = DetallePedido::find($detalle['id']);
            if ($registro) {
                $registro->descuento = $this->descuentoGlobal;
                $registro->save();

                $this->detalles[$i]['descuento'] = $this->descuentoGlobal;
                
            }
        }

        session()->flash('success', 'Descuento aplicado a todos los productos');

        return redirect(request()->header('Referer'));
    }

    public function guardarCambiosGeneral(){
        
            $actualizados = 0;
        
            foreach ($this->detalles as $detalle) {

                $registro = DetallePedido::find($detalle['id']);

                if ($detalle['cantidad'] < 1) {
                    session()->flash('error', 'La cantidad mínima es 1 en todos los productos.');
                    return redirect(request()->header('Referer'));
                }
        
                if ($registro) {
                    $cantidadNueva = $detalle['cantidad'];
                    $descuentoNuevo = $detalle['descuento'];
        
                    $cantidadOriginal = $registro->cantidad;
                    $descuentoOriginal = $registro->descuento;
        
                    // Solo guarda si alguno cambió
                    if ($cantidadNueva != $cantidadOriginal || $descuentoNuevo != $descuentoOriginal) {
                        $registro->cantidad = $cantidadNueva;
                        $registro->descuento = $descuentoNuevo;
                        $registro->save();
                        $actualizados++;
                    }
                }
            }
        
            if ($actualizados > 0) {
                session()->flash('success', "Se actualizaron $actualizados producto(s).");
            } else {
                session()->flash('success', "No se detectaron cambios.");
            }

            return redirect(request()->header('Referer'));
        }

        public function eliminarItem($id){

            $item = DetallePedido::find($id);
    
            if (! $item) {
                session()->flash('error', 'Promoción no encontrada');
                return;
            }
    
            $item->delete();
    
            session()->flash('success', 'Cotizacion eliminada correctamente');

            return redirect(request()->header('Referer')); //Recarga la pagina y asi no se dañan los script con el render
    
        }
}
