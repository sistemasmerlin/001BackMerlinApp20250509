<?php

namespace App\Livewire\Admin\Promociones;

use Livewire\Component;
use App\Models\Promocion;
use App\Models\PromocionRelacion;
use App\Models\FleteCiudad;

class Index extends Component
{
    public $promociones, $promocion_id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $estado, $creado_por;
    public bool $openModal = false;
    public bool $modoEditar = false;

    public $openAsignarModal = false;
    public $promocionIdSeleccionada;

    public $tipoAsignacion = '';          // Cliente, Todos, Ciudad, Asesor
    public $nitCliente = '';
    public $subcanalSeleccionado = '';
    public $departamentoSeleccionado = '';
    public $ciudadSeleccionada = '';
    public $codigoAsesor = '';

    public $departamentoCodigoSeleccionado = '';
    public $departamentoNombreSeleccionado = '';
    public $ciudadCodigoSeleccionada = '';
    public $ciudadNombreSeleccionada = '';
    
    public $departamentos = [];
    public $ciudades = [];
    public $ciudadesFiltradas = [];

    public function mount()
    {
        $this->promociones = Promocion::with('relaciones')->where('estado','1')->get();

        //$this->ciudades = FleteCiudad::select('cod_depto','depto','cod_ciudad', 'ciudad')->get()->toArray();
        //$this->ciudades = FleteCiudad::select('nombre_departamento', 'nombre_ciudad')->get()->toArray();

        $this->departamentos = FleteCiudad::select('depto','cod_depto')
            ->groupBy('depto','cod_depto')
            ->orderBy('depto')
            ->pluck('depto', 'cod_depto') // clave: cod_depto, valor: depto
            ->toArray();

    // dd($this->departamentos);

    }

    public function abrirModal()
    {
        $this->reset(['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin']);
        $this->modoEditar = false;
        $this->openModal = true;
    }

    public function guardarPromocion()
    {
        $this->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);
    
        $data = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'creado_por' => auth()->user()->email ?? 'sistema', // o lo que uses
        ];
    
        if ($this->modoEditar && $this->promocion_id) {
            Promocion::find($this->promocion_id)?->update($data);
        } else {
            Promocion::create($data);
        }
    
        $this->reset(['openModal', 'modoEditar', 'promocion_id', 'nombre', 'descripcion', 'fecha_inicio', 'fecha_fin']);
        session()->flash('success', $this->modoEditar ? 'Promoción actualizada correctamente' : 'Promoción creada correctamente');
        $this->promociones = Promocion::with('relaciones')->where('estado','1')->get();

    }

    public function editarPromocion($id)
    {
        $promocion = Promocion::findOrFail($id);

        $this->promocion_id = $promocion->id;
        $this->nombre = $promocion->nombre;
        $this->descripcion = $promocion->descripcion;
        $this->fecha_inicio = $promocion->fecha_inicio;
        $this->fecha_fin = $promocion->fecha_fin;

        $this->modoEditar = true;
        $this->openModal = true;
    }

    public function actualizarPromocion()
    {
        $this->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
        ]);

        $promocion = Promocion::find($this->promocion_id);

        if (! $promocion) {
            session()->flash('error', 'Promoción no encontrada');
            return;
        }
    
        $promocion->update([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin
        ]);

        $this->reset(['openModal', 'modoEditar', 'promocion_id', 'nombre', 'descripcion', 'fecha_inicio', 'fecha_fin']);
        session()->flash('success', 'Promoción actualizada correctamente');
        $this->promociones = Promocion::with('relaciones')->where('estado','1')->get(); // recarga
    }

    public function eliminarPromocion($id)
    {
        $promocion = Promocion::find($id);

        if (! $promocion) {
            session()->flash('error', 'Promoción no encontrada');
            return;
        }

        $promocion->update([
            'estado' => 0,
            'eliminado' => 1,
        ]);

        session()->flash('success', 'Promoción eliminada correctamente');
        //$this->promociones = Promocion::with('relaciones')->where('eliminado', false)->get();
        $this->promociones = Promocion::with('relaciones')->where('estado','1')->get();
    }

    public function asignarPorCiudad()
    {
        if (!$this->departamentoSeleccionado || !$this->ciudadSeleccionada) {
            session()->flash('error', 'Debe seleccionar un departamento y una ciudad.');
            return;
        }

        // Aquí guardarías en la tabla promocion_relaciones
        PromocionRelacion::create([
            'promocion_id' => $this->promocion->id,
            'asignado'     => $this->ciudadSeleccionada,
            'subcanal'     => $this->departamentoSeleccionado,
            'creado_por'   => auth()->user()?->name ?? 'sistema',
            'estado'       => true,
            'eliminado'    => false,
        ]);

        $this->reset(['openAsignarModal', 'departamentoSeleccionado', 'ciudadSeleccionada', 'ciudadesFiltradas']);

        session()->flash('success', 'Promoción asignada correctamente por ciudad.');
    }

    public function updatedDepartamentoSeleccionado($value)
    {
        if (!empty($value)) {
            // Obtener el nombre del departamento seleccionado
            $depto = FleteCiudad::where('cod_depto', $value)
                    ->select('cod_depto', 'depto')
                    ->first();
            
            $this->departamentoCodigoSeleccionado = $depto->cod_depto;
            $this->departamentoNombreSeleccionado = $depto->depto;
            
            // Obtener ciudades para este departamento
            $this->ciudadesFiltradas = FleteCiudad::where('cod_depto', $value)
                ->select('cod_ciudad', 'ciudad')
                ->orderBy('ciudad')
                ->get()
                ->toArray();
        } else {
            $this->reset([
                'departamentoCodigoSeleccionado',
                'departamentoNombreSeleccionado',
                'ciudadesFiltradas',
                'ciudadCodigoSeleccionada',
                'ciudadNombreSeleccionada'
            ]);
        }
    }

    public function actualizarCiudadSeleccionada($codigoCiudad)
    {
        $ciudad = FleteCiudad::where('cod_ciudad', $codigoCiudad)->first();
        
        if ($ciudad) {
            $this->ciudadCodigoSeleccionada = $ciudad->cod_ciudad;
            $this->ciudadNombreSeleccionada = $ciudad->ciudad;
        } else {
            $this->reset(['ciudadCodigoSeleccionada', 'ciudadNombreSeleccionada']);
        }
    }

    /*public function updatedDepartamentoSeleccionado($value)
    {
        $this->ciudadesFiltradas = FleteCiudad::where('depto', $value)
            ->select('cod_ciudad', 'ciudad') // Selecciona tanto el código como el nombre
            ->orderBy('ciudad')
            ->get()
            ->toArray();
        
        $this->ciudadSeleccionada = ''; // Resetear la ciudad seleccionada
    }*/

    public function abrirModalAsignar($promocionId)
    {
        $this->promocionIdSeleccionada = $promocionId;
        $this->openAsignarModal = true;
    
        $this->tipoAsignacion = '';
        $this->nitCliente = '';
        $this->subcanalSeleccionado = '';
        $this->departamentoSeleccionado = '';
        $this->ciudadSeleccionada = '';
        $this->codigoAsesor = '';
        $this->ciudadesFiltradas = [];
    
        // Cargar ciudades y departamentos desde el modelo correcto
        $this->ciudades = FleteCiudad::select('depto', 'ciudad')->get()->toArray();
    
        $this->departamentos = FleteCiudad::select('depto','cod_depto')
            ->distinct()
            ->orderBy('depto')
            ->pluck('depto','cod_depto')
            ->toArray();
    }

    public function eliminarRelacion($relacionId)
    {
        $relacion = PromocionRelacion::find($relacionId);

        if (!$relacion) {
            session()->flash('error', 'Relación no encontrada.');
            return;
        }

        $relacion->delete(); // o update(['eliminado' => true]) si manejas soft delete

        session()->flash('success', 'Asignación eliminada correctamente.');
        $this->promociones = Promocion::with('relaciones')->get(); // refrescar
    }

    public function asignarPromocion()
    {
        switch ($this->tipoAsignacion) {
            case 'cliente':

                if (!$this->nitCliente) {
                    session()->flash('error', 'Debe ingresar el NIT del cliente.');
                    return;
                }
    
                PromocionRelacion::create([
                    'promocion_id' => $this->promocionIdSeleccionada,
                    'asignado'     => $this->nitCliente,
                    'subcanal'     => 'CLIENTE',
                    'creado_por'   => auth()->user()->name ?? 'sistemas',
                    'estado'       => true,
                    'eliminado'    => false,
                ]);
                break;    

            case 'todos':

                if (!$this->subcanalSeleccionado) {
                    session()->flash('error', 'Seleccione un subcanal.');
                    return;
                }
    
                PromocionRelacion::create([
                    'promocion_id' => $this->promocionIdSeleccionada,
                    'asignado'     => 'TODOS',
                    'subcanal'     => $this->subcanalSeleccionado,
                    'creado_por'   => auth()->user()->name ?? 'sistemas',
                    'estado'       => true,
                    'eliminado'    => false,
                ]);

                break;

            case 'ciudad':
                if (!$this->departamentoCodigoSeleccionado || !$this->ciudadCodigoSeleccionada) {
                    session()->flash('error', 'Debe seleccionar un departamento y una ciudad.');
                    return;
                }

                PromocionRelacion::create([
                    'promocion_id' => $this->promocionIdSeleccionada,
                    'asignado'     => $this->departamentoCodigoSeleccionado.'-'.$this->ciudadCodigoSeleccionada,
                    'asignado_nombre' => $this->ciudadNombreSeleccionada,
                    'subcanal'     => 'MINORISTA',
                    'creado_por'   => auth()->user()->name ?? 'sistemas',
                    'estado'       => true,
                    'eliminado'    => false,
                ]);
                
                session()->flash('success', 'Promoción asignada correctamente por ciudad.');
                break;

            case 'asesor':

                if (!$this->subcanalSeleccionado) {
                    session()->flash('error', 'Seleccione un subcanal.');
                    return;
                }
    
                PromocionRelacion::create([
                    'promocion_id' => $this->promocionIdSeleccionada,
                    'asignado'     => $this->codigoAsesor,
                    'subcanal'     => $this->subcanalSeleccionado,
                    'creado_por'   => auth()->user()->name ?? 'sistemas',
                    'estado'       => true,
                    'eliminado'    => false,
                ]);

                break;

            default:
                session()->flash('error', 'Seleccione un tipo de asignación válido.');
        }

        // Limpieza
        $this->reset(['tipoAsignacion', 'nitCliente', 'subcanalSeleccionado', 'departamentoSeleccionado', 'ciudadSeleccionada', 'codigoAsesor', 'openAsignarModal']);
    }

    
    public function render()
    {
        return view('livewire.admin.promociones.index');
    }
}
