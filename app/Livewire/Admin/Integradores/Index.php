<?php

namespace App\Livewire\Admin\Integradores;

use App\Models\Integrador;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public bool $modal = false;

    public ?int $integradorId = null;

    public ?int $user_id = null;
    public string $nit = '';
    public ?string $nombre_comercial = null;
    public string $prefijo_pedido = '';
    public string $lista_precio = '001';
    public string $id_sucursal = '020';
    public string $punto_envio = '000';
    public string $condicion_pago = '30D';
    public ?string $codigo_asesor = null;
    public ?string $nombre_asesor = null;
    public ?string $correo_notificacion = null;
    public bool $activo = true;
    public bool $calcula_flete = true;

    protected function rules()
    {
        return [
            'user_id' => 'required|exists:users,id|unique:integradores,user_id,' . $this->integradorId,
            'nit' => 'required|string|max:30|unique:integradores,nit,' . $this->integradorId,
            'nombre_comercial' => 'nullable|string|max:255',
            'prefijo_pedido' => 'required|string|max:10',
            'lista_precio' => 'required|string|max:10',
            'id_sucursal' => 'required|string|max:10',
            'punto_envio' => 'required|string|max:10',
            'condicion_pago' => 'required|string|max:20',
            'codigo_asesor' => 'nullable|string|max:50',
            'nombre_asesor' => 'nullable|string|max:255',
            'correo_notificacion' => 'nullable|string|max:255',
            'activo' => 'boolean',
            'calcula_flete' => 'boolean',
        ];
    }

    public function updatingQ()
    {
        $this->resetPage();
    }

    public function crear()
    {
        $this->resetForm();
        $this->modal = true;
    }

    public function editar($id)
    {
        $integrador = Integrador::findOrFail($id);

        $this->integradorId = $integrador->id;
        $this->user_id = $integrador->user_id;
        $this->nit = $integrador->nit;
        $this->nombre_comercial = $integrador->nombre_comercial;
        $this->prefijo_pedido = $integrador->prefijo_pedido;
        $this->lista_precio = $integrador->lista_precio;
        $this->id_sucursal = $integrador->id_sucursal;
        $this->punto_envio = $integrador->punto_envio;
        $this->condicion_pago = $integrador->condicion_pago;
        $this->codigo_asesor = $integrador->codigo_asesor;
        $this->nombre_asesor = $integrador->nombre_asesor;
        $this->correo_notificacion = $integrador->correo_notificacion;
        $this->activo = (bool) $integrador->activo;
        $this->calcula_flete = (bool) $integrador->calcula_flete;

        $this->modal = true;
    }

    public function guardar()
    {
        $this->validate();

        Integrador::updateOrCreate(
            ['id' => $this->integradorId],
            [
                'user_id' => $this->user_id,
                'nit' => trim($this->nit),
                'nombre_comercial' => $this->nombre_comercial,
                'prefijo_pedido' => strtoupper(trim($this->prefijo_pedido)),
                'lista_precio' => $this->lista_precio,
                'id_sucursal' => $this->id_sucursal,
                'punto_envio' => $this->punto_envio,
                'condicion_pago' => $this->condicion_pago,
                'codigo_asesor' => $this->codigo_asesor,
                'nombre_asesor' => $this->nombre_asesor,
                'correo_notificacion' => $this->correo_notificacion,
                'activo' => $this->activo,
                'calcula_flete' => $this->calcula_flete,
            ]
        );

        $this->modal = false;
        $this->resetForm();

        session()->flash('success', 'Integrador guardado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $integrador = Integrador::findOrFail($id);

        $integrador->update([
            'activo' => !$integrador->activo,
        ]);
    }

    private function resetForm()
    {
        $this->reset([
            'integradorId',
            'user_id',
            'nit',
            'nombre_comercial',
            'prefijo_pedido',
            'codigo_asesor',
            'nombre_asesor',
            'correo_notificacion',
        ]);

        $this->lista_precio = '001';
        $this->id_sucursal = '020';
        $this->punto_envio = '000';
        $this->condicion_pago = '30D';
        $this->activo = true;
        $this->calcula_flete = true;
    }

    public function render()
    {
        $integradores = Integrador::with('user')
            ->when($this->q, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('nit', 'like', "%{$this->q}%")
                        ->orWhere('nombre_comercial', 'like', "%{$this->q}%")
                        ->orWhere('prefijo_pedido', 'like', "%{$this->q}%")
                        ->orWhereHas('user', function ($u) {
                            $u->where('name', 'like', "%{$this->q}%")
                                ->orWhere('email', 'like', "%{$this->q}%");
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(20);

            $usuarios = User::query()
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Integrador');
                })
                ->where(function ($q) {
                    $q->whereDoesntHave('integrador');

                    if ($this->user_id) {
                        $q->orWhere('id', $this->user_id);
                    }
                })
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

        return view('livewire.admin.integradores.index', compact('integradores', 'usuarios'));
    }
}