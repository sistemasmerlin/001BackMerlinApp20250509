<?php

namespace App\Livewire\Admin\Pqrs\Orm;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Orm;
use App\Models\Transportadora;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // ========================
    // Filtros
    // ========================
    public $q = '';
    public $estado = '';

    // ========================
    // Modal
    // ========================
    public $modal = false;
    public $editId = null;

    // ========================
    // Form fields
    // ========================
    public $pqrs_id;
    public $estado_form = 'creada';
    public $transportadora_id;

    public $razon_social;
    public $nit;
    public $direccion;
    public $departamento;
    public $ciudad;
    public $telefono;

    public $lps;
    public $cajas;
    public $peso;
    public $valor_declarado;

    public $comentarios;
    public $fecha_recogida_programada;
    public $fecha_recibido_transportadora;

    public $transportadoras = [];

    // ========================
    // Render
    // ========================
    public function render()
    {
        $query = Orm::query()
            ->with(['transportadora', 'pqrs']);

        if ($this->q) {
            $query->where(function ($q) {
                $q->where('nit', 'like', "%{$this->q}%")
                  ->orWhere('razon_social', 'like', "%{$this->q}%")
                  ->orWhere('ciudad', 'like', "%{$this->q}%")
                  ->orWhere('pqrs_id', 'like', "%{$this->q}%");
            });
        }

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        return view('livewire.admin.pqrs.orm.index', [
            'rows' => $query->orderByDesc('id')->paginate(10),
        ]);
    }

    // ========================
    // Abrir modal
    // ========================
    public function crear()
    {
        $this->resetForm();
        $this->modal = true;
        $this->transportadoras = Transportadora::orderBy('razon_social')->get();
    }

    public function editar($id)
    {
        $orm = Orm::findOrFail($id);

        $this->editId = $orm->id;
        $this->pqrs_id = $orm->pqrs_id;
        $this->estado_form = $orm->estado;
        $this->transportadora_id = $orm->transportadora_id;

        $this->razon_social = $orm->razon_social;
        $this->nit = $orm->nit;
        $this->direccion = $orm->direccion;
        $this->departamento = $orm->departamento;
        $this->ciudad = $orm->ciudad;
        $this->telefono = $orm->telefono;

        $this->lps = $orm->lps;
        $this->cajas = $orm->cajas;
        $this->peso = $orm->peso;
        $this->valor_declarado = $orm->valor_declarado;

        $this->comentarios = $orm->comentarios;
        $this->fecha_recogida_programada = optional($orm->fecha_recogida_programada)->format('Y-m-d');
        $this->fecha_recibido_transportadora = optional($orm->fecha_recibido_transportadora)->format('Y-m-d\TH:i');

        $this->transportadoras = Transportadora::orderBy('razon_social')->get();
        $this->modal = true;
    }

    public function cerrarModal()
    {
        $this->modal = false;
        $this->resetForm();
    }

    // ========================
    // Guardar
    // ========================
    public function guardar()
    {
        $this->validate([
            'pqrs_id' => 'required|exists:pqrs,id',
            'nit' => 'required|string|max:30',
            'razon_social' => 'required|string|max:200',
            'estado_form' => 'required|in:creada,en_tramite,cerrada',
        ]);

        $data = [
            'pqrs_id' => $this->pqrs_id,
            'estado' => $this->estado_form,
            'transportadora_id' => $this->transportadora_id,

            'razon_social' => $this->razon_social,
            'nit' => $this->nit,
            'direccion' => $this->direccion,
            'departamento' => $this->departamento,
            'ciudad' => $this->ciudad,
            'telefono' => $this->telefono,

            'lps' => $this->lps,
            'cajas' => $this->cajas,
            'peso' => $this->peso,
            'valor_declarado' => $this->valor_declarado,

            'comentarios' => $this->comentarios,
            'fecha_recogida_programada' => $this->fecha_recogida_programada,
            'fecha_recibido_transportadora' => $this->fecha_recibido_transportadora,

            'usuario_recibe_id' => Auth::id(),
        ];

        if ($this->editId) {
            Orm::where('id', $this->editId)->update($data);
            session()->flash('success', 'ORM actualizada correctamente.');
        } else {
            Orm::create($data);
            session()->flash('success', 'ORM creada correctamente.');
        }

        $this->cerrarModal();
    }

    // ========================
    // Cambio rápido de estado
    // ========================
    public function cambiarEstado($id, $estado)
    {
        if (!in_array($estado, ['creada', 'en_tramite', 'cerrada'])) {
            return;
        }

        $orm = Orm::findOrFail($id);
        $orm->estado = $estado;
        $orm->save();

        session()->flash('success', 'Estado actualizado.');
    }

    // ========================
    // Reset form
    // ========================
    private function resetForm()
    {
        $this->reset([
            'editId',
            'pqrs_id',
            'estado_form',
            'transportadora_id',
            'razon_social',
            'nit',
            'direccion',
            'departamento',
            'ciudad',
            'telefono',
            'lps',
            'cajas',
            'peso',
            'valor_declarado',
            'comentarios',
            'fecha_recogida_programada',
            'fecha_recibido_transportadora',
        ]);

        $this->estado_form = 'creada';
    }

    public function limpiarFiltros()
    {
        $this->reset(['q', 'estado']);
    }
}