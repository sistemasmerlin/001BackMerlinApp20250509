<?php

namespace App\Livewire\Admin\Pqrs\Solicitudes;

use App\Models\Pqrs;
use App\Models\PqrsProducto;
use App\Models\Transportadora;
use Livewire\Component;

class Detalles extends Component
{
    public Pqrs $pqrs;

    public bool $showModalOrm = false;

    public $transportadoras = [];

    public ?int $orm_id = null;
    public ?int $transportadora_id = null;
    public ?string $numero_guia = null;
    public ?string $fecha_recogida_programada = null;
    public $cajas = null;
    public $lios = null;
    public $peso = null;
    public ?string $comentarios = null;

    public function mount(Pqrs $pqrs): void
    {
        $this->pqrs = $pqrs->load([
            'orm.transportadora',
            'orm.usuarioRecibe',
            'productos.responsable',
            'productos.causal',
            'productos.adjuntos',
        ]);

        $this->transportadoras = Transportadora::query()
            ->orderBy('razon_social')
            ->get();
    }

    public function puedeRevisarProducto(PqrsProducto $producto): bool
    {
        if (!auth()->check()) return false;

        $userEmail = strtolower(trim(auth()->user()->email ?? ''));
        $correos = $producto->responsable->correos ?? [];

        return collect($correos)
            ->map(fn($c) => strtolower(trim($c)))
            ->contains($userEmail);
    }

    public function aprobarProducto(int $id): void
    {
        $producto = PqrsProducto::with('responsable')->findOrFail($id);

        abort_unless($this->puedeRevisarProducto($producto), 403);

        $producto->update([
            'estado' => 'aprobado',
            'revisado_por' => auth()->id(),
            'fecha_revision' => now(),
        ]);

        $this->recalcularValorDeclaradoOrm();
        $this->refrescar();
    }

    public function rechazarProducto(int $id): void
    {
        $producto = PqrsProducto::with('responsable')->findOrFail($id);

        abort_unless($this->puedeRevisarProducto($producto), 403);

        $producto->update([
            'estado' => 'rechazado',
            'revisado_por' => auth()->id(),
            'fecha_revision' => now(),
        ]);

        $this->recalcularValorDeclaradoOrm();
        $this->refrescar();
    }

    public function aprobarOrmProducto(int $id): void
    {
        $producto = PqrsProducto::with('responsable')->findOrFail($id);

        abort_unless($this->puedeRevisarProducto($producto), 403);

        if (!(int)$producto->requiere_recogida) return;

        $producto->update([
            'estado_orm' => 'aprobada',
            'orm_revisada_por' => auth()->id(),
            'orm_fecha_revision' => now(),
        ]);

        $this->refrescar();
    }

    public function rechazarOrmProducto(int $id): void
    {
        $producto = PqrsProducto::with('responsable')->findOrFail($id);

        abort_unless($this->puedeRevisarProducto($producto), 403);

        if (!(int)$producto->requiere_recogida) return;

        $producto->update([
            'estado_orm' => 'rechazada',
            'orm_revisada_por' => auth()->id(),
            'orm_fecha_revision' => now(),
        ]);

        $this->refrescar();
    }

    public function abrirModalOrm(): void
    {
        if (!$this->pqrs->orm) {
            return;
        }

        $orm = $this->pqrs->orm;

        $this->orm_id = $orm->id;
        $this->transportadora_id = $orm->transportadora_id;
        $this->numero_guia = $orm->numero_guia;
        $this->fecha_recogida_programada = optional($orm->fecha_recogida_programada)?->format('Y-m-d\TH:i');
        $this->cajas = $orm->cajas;
        $this->lios = $orm->lios;
        $this->peso = $orm->peso;
        $this->comentarios = $orm->comentarios;

        $this->resetValidation();
        $this->showModalOrm = true;
    }

    public function cerrarModalOrm(): void
    {
        $this->showModalOrm = false;
    }

    public function guardarOrm(): void
    {
        $rules = [
            'transportadora_id' => ['nullable', 'integer', 'exists:transportadoras,id'],
            'numero_guia' => ['nullable', 'string', 'max:100'],
            'fecha_recogida_programada' => ['nullable', 'date'],
            'cajas' => ['nullable', 'numeric', 'min:0'],
            'lios' => ['nullable', 'numeric', 'min:0'],
            'peso' => ['nullable', 'numeric', 'min:0'],
            'comentarios' => ['nullable', 'string'],
        ];

        $this->validate($rules);

        if ($this->transportadora_id) {
            $this->validate([
                'numero_guia' => ['required', 'string', 'max:100'],
                'fecha_recogida_programada' => ['required', 'date'],
            ], [
                'numero_guia.required' => 'La guía es obligatoria al seleccionar transportadora.',
                'fecha_recogida_programada.required' => 'La fecha de recogida es obligatoria al seleccionar transportadora.',
            ]);
        }

        $orm = $this->pqrs->orm;

        if (!$orm) {
            return;
        }

        $valorDeclarado = $this->pqrs->productos()
            ->where('estado', 'aprobado')
            ->sum('valor_neto');

        $estado = $orm->estado;

        if ($this->transportadora_id && $this->numero_guia && $this->fecha_recogida_programada) {
            $estado = 'programada';
        }

        $orm->update([
            'transportadora_id' => $this->transportadora_id,
            'numero_guia' => $this->numero_guia,
            'fecha_recogida_programada' => $this->fecha_recogida_programada,
            'cajas' => $this->cajas ?: 0,
            'lios' => $this->lios ?: 0,
            'peso' => $this->peso ?: 0,
            'comentarios' => $this->comentarios,
            'valor_declarado' => $valorDeclarado,
            'estado' => $estado,
        ]);

        $this->cerrarModalOrm();
        $this->refrescar();
    }

    public function marcarEnBodega(): void
    {
        if ($this->pqrs->orm->estado !== 'programada') {
            return;
        }

        if (!$this->pqrs->orm) {
            return;
        }

        $this->pqrs->orm->update([
            'estado' => 'en_bodega',
            'fecha_llegada_bodega' => now(),
            'usuario_recibe_id' => auth()->id(),
        ]);

        $this->refrescar();
    }

    private function recalcularValorDeclaradoOrm(): void
    {
        if (!$this->pqrs->orm) {
            return;
        }

        $valorDeclarado = $this->pqrs->productos()
            ->where('estado', 'aprobado')
            ->sum('valor_neto');

        $this->pqrs->orm->update([
            'valor_declarado' => $valorDeclarado,
        ]);
    }

    private function refrescar(): void
    {
        $this->pqrs->refresh()->load([
            'orm.transportadora',
            'orm.usuarioRecibe',
            'productos.responsable',
            'productos.causal',
            'productos.adjuntos',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.pqrs.solicitudes.detalles');
    }
}