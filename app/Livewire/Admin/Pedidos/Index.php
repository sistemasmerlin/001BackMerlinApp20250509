<?php

namespace App\Livewire\Admin\Pedidos;


use App\Models\Pedido;

use Livewire\Component;
use Carbon\Carbon;

class Index extends Component
{
    public $pedidos;

    public $notaId;
    public $observacion;

    public $fecha_inicio;
    public $fecha_final;

    public function mount()
    {
        /* $this->pedidos = Pedido::with('direccionEnvio')->orderBy('id', 'desc')->get(); */
        
        $this->fecha_inicio = request()->query('fecha_inicio');
        $this->fecha_final = request()->query('fecha_final');

        $query = Pedido::with('direccionEnvio')->orderBy('id', 'asc');

        if ($this->fecha_inicio && $this->fecha_final) {
            $fechaInicio = Carbon::parse($this->fecha_inicio)->startOfDay();
            $fechaFinal = Carbon::parse($this->fecha_final)->endOfDay();

            $query->whereBetween('fecha_pedido', [$fechaInicio, $fechaFinal]);
        
            $this->pedidos = $query->get();

        }else{
            
            $this->pedidos = $query->limit(40)->get();
        }

            //dd($this->pedidos );
        }

    public function eliminarCotizacion($id){

        $cotizacion = Pedido::find($id);

        if (! $cotizacion) {
            session()->flash('error', 'Promoción no encontrada');
            return;
        }

        $cotizacion->delete();

        session()->flash('success', 'Cotizacion eliminada correctamente');

        return redirect()->route('pedidos.index');

    }

    public function render()
    {
      return view('livewire.admin.pedidos.index', ['pedidos' => $this->pedidos]);

    }


    public function editarNota($id){
            $pedido = Pedido::find($id);

            if (! $pedido) {
                session()->flash('error', 'Pedido no encontrado.');
                return;
            }

            $this->notaId = $pedido->id;
            $this->observacion = $pedido->observaciones;
        }

    public function guardarNota(){

            $pedido = Pedido::find($this->notaId);

            if ($pedido) {
                $pedido->observaciones = $this->observacion;
                $pedido->save();

                session()->flash('success', 'Nota actualizada correctamente.');
                $this->dispatch('cerrarModal');

            }

            return redirect()->route('pedidos.index');
        }

       
}
