<?php

namespace App\Livewire\Admin\BancosRecaudo;

use App\Models\BancoRecaudo;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $bancoId = null;

    public $id_banco = '';
    public $descripcion_banco = '';
    public $id_cuenta = '';
    public $descripcion_cuenta = '';
    public $numero_cuenta = '';
    public $id_medio_pago = '';
    public $tipo_cuenta = 8;
    public $estado = true;

    public $modal = false;

    protected function rules()
    {
        return [
            'id_banco' => 'nullable|string|max:50',
            'descripcion_banco' => 'required|string|max:255',
            'id_cuenta' => 'nullable|string|max:50',
            'descripcion_cuenta' => 'nullable|string|max:255',
            'numero_cuenta' => 'nullable|string|max:100',
            'id_medio_pago' => 'nullable|string|max:50',
            'tipo_cuenta' => 'required|integer',
            'estado' => 'boolean',
        ];
    }

    public function render()
    {
        $bancos = BancoRecaudo::query()
            ->when($this->search, function ($query) {
                $query->where('descripcion_banco', 'like', "%{$this->search}%")
                    ->orWhere('numero_cuenta', 'like', "%{$this->search}%")
                    ->orWhere('descripcion_cuenta', 'like', "%{$this->search}%")
                    ->orWhere('id_banco', 'like', "%{$this->search}%");
            })
            ->orderBy('descripcion_banco')
            ->paginate(10);

        return view('livewire.admin.bancos-recaudo.index', [
            'bancos' => $bancos,
        ]);
    }

    public function crear()
    {
        $this->resetForm();
        $this->modal = true;
    }

    public function editar($id)
    {
        $banco = BancoRecaudo::findOrFail($id);

        $this->bancoId = $banco->id;
        $this->id_banco = $banco->id_banco;
        $this->descripcion_banco = $banco->descripcion_banco;
        $this->id_cuenta = $banco->id_cuenta;
        $this->descripcion_cuenta = $banco->descripcion_cuenta;
        $this->numero_cuenta = $banco->numero_cuenta;
        $this->id_medio_pago = $banco->id_medio_pago;
        $this->tipo_cuenta = $banco->tipo_cuenta;
        $this->estado = $banco->estado;

        $this->modal = true;
    }

    public function guardar()
    {
        $this->validate();

        BancoRecaudo::updateOrCreate(
            ['id' => $this->bancoId],
            [
                'id_banco' => strtoupper($this->id_banco),
                'descripcion_banco' => strtoupper($this->descripcion_banco),
                'id_cuenta' => strtoupper($this->id_cuenta),
                'descripcion_cuenta' => strtoupper($this->descripcion_cuenta),
                'numero_cuenta' => strtoupper($this->numero_cuenta),
                'id_medio_pago' => strtoupper($this->id_medio_pago),
                'tipo_cuenta' => $this->tipo_cuenta ?: 8,
                'estado' => $this->estado,
            ]
        );

        $this->modal = false;
        $this->resetForm();

        session()->flash('success', 'Banco guardado correctamente.');
    }

    public function eliminar($id)
    {
        BancoRecaudo::findOrFail($id)->delete();

        session()->flash('success', 'Banco eliminado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $banco = BancoRecaudo::findOrFail($id);
        $banco->estado = ! $banco->estado;
        $banco->save();
    }

    public function cerrarModal()
    {
        $this->modal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'bancoId',
            'id_banco',
            'descripcion_banco',
            'id_cuenta',
            'descripcion_cuenta',
            'numero_cuenta',
            'id_medio_pago',
            'estado',
        ]);

        $this->tipo_cuenta = 8;
        $this->estado = true;

        $this->resetValidation();
    }
}