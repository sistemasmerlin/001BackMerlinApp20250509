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
    public bool $showModalCerrar = false;
    public ?string $tipo_acuerdo = null; // nota | no_aplica | atencion_comercial
    public ?string $nota_acuerdo = null;
    public $valor_acuerdo = null;
    public ?string $comentario_cierre = null;

    // ✅ NUEVO
    public array $seleccionProductos = [];
    public array $seleccionOrm = [];

    public function mount(Pqrs $pqrs): void
    {
        $this->pqrs = $pqrs->load([
            'orm.transportadora',
            'orm.usuarioRecibe',
            'productos.responsable',
            'productos.causal',
            'productos.adjuntos',
            'adjuntos',
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

    // =========================================================
    // INDIVIDUALES
    // =========================================================

    public function aprobarProducto(int $id): void
    {
        if ($this->pqrsEstaCerrada()) {
            return;
        }

        $producto = PqrsProducto::with('responsable')->findOrFail($id);

        abort_unless($this->puedeRevisarProducto($producto), 403);

        $producto->update([
            'estado' => 'aprobado',
            'revisado_por' => auth()->id(),
            'fecha_revision' => now(),
        ]);

        $this->marcarPqrsComoRevisadaSiAplica();

        $this->recalcularValorDeclaradoOrm();
        $this->refrescar();
    }

    public function rechazarProducto(int $id): void
    {
        if ($this->pqrsEstaCerrada()) {
            return;
        }

        $producto = PqrsProducto::with('responsable')->findOrFail($id);

        abort_unless($this->puedeRevisarProducto($producto), 403);

        $producto->update([
            'estado' => 'rechazado',
            'revisado_por' => auth()->id(),
            'fecha_revision' => now(),
        ]);

        $this->marcarPqrsComoRevisadaSiAplica();

        $this->recalcularValorDeclaradoOrm();
        $this->refrescar();
    }

    public function aprobarOrmProducto(int $id): void
    {
        if ($this->pqrsEstaCerrada()) {
            return;
        }

        $producto = PqrsProducto::with('responsable')->findOrFail($id);

        abort_unless($this->puedeRevisarProducto($producto), 403);

        if (!(int)$producto->requiere_recogida) return;

        $producto->update([
            'estado_orm' => 'aprobada',
            'orm_revisada_por' => auth()->id(),
            'orm_fecha_revision' => now(),
        ]);

        $this->recalcularValorDeclaradoOrm();
        $this->refrescar();
    }

    public function rechazarOrmProducto(int $id): void
    {
        if ($this->pqrsEstaCerrada()) {
            return;
        }

        $producto = PqrsProducto::with('responsable')->findOrFail($id);

        abort_unless($this->puedeRevisarProducto($producto), 403);

        if (!(int)$producto->requiere_recogida) return;

        $producto->update([
            'estado_orm' => 'rechazada',
            'orm_revisada_por' => auth()->id(),
            'orm_fecha_revision' => now(),
        ]);

        $this->recalcularValorDeclaradoOrm();
        $this->refrescar();
    }

    // =========================================================
    // CHECKBOX MASIVOS - PRODUCTOS
    // =========================================================

    public function toggleTodosProductos($checked): void
    {
        $this->seleccionProductos = [];

        if (filter_var($checked, FILTER_VALIDATE_BOOLEAN)) {
            foreach ($this->pqrs->productos as $p) {
                if ($this->puedeRevisarProducto($p)) {
                    $this->seleccionProductos[] = (string)$p->id;
                }
            }
        }
    }

    public function aprobarProductosMasivo(): void
    {
        if ($this->pqrsEstaCerrada()) {
            return;
        }

        if (empty($this->seleccionProductos)) {
            return;
        }

        $productos = PqrsProducto::with('responsable')
            ->whereIn('id', $this->seleccionProductos)
            ->get();

        $huboRevision = false;

        foreach ($productos as $producto) {
            if (!$this->puedeRevisarProducto($producto)) {
                continue;
            }

            $producto->update([
                'estado' => 'aprobado',
                'revisado_por' => auth()->id(),
                'fecha_revision' => now(),
            ]);

            $huboRevision = true;
        }

        if ($huboRevision) {
            $this->marcarPqrsComoRevisadaSiAplica();
        }

        $this->seleccionProductos = [];
        $this->recalcularValorDeclaradoOrm();
        $this->refrescar();
    }

    public function rechazarProductosMasivo(): void
    {
        if ($this->pqrsEstaCerrada()) {
            return;
        }

        if (empty($this->seleccionProductos)) {
            return;
        }

        $productos = PqrsProducto::with('responsable')
            ->whereIn('id', $this->seleccionProductos)
            ->get();

        $huboRevision = false;

        foreach ($productos as $producto) {
            if (!$this->puedeRevisarProducto($producto)) {
                continue;
            }

            $producto->update([
                'estado' => 'rechazado',
                'revisado_por' => auth()->id(),
                'fecha_revision' => now(),
            ]);

            $huboRevision = true;
        }

        if ($huboRevision) {
            $this->marcarPqrsComoRevisadaSiAplica();
        }

        $this->seleccionProductos = [];
        $this->recalcularValorDeclaradoOrm();
        $this->refrescar();
    }
    // =========================================================
    // CHECKBOX MASIVOS - ORM
    // =========================================================

    public function toggleTodosOrm($checked): void
    {
        $this->seleccionOrm = [];

        if (filter_var($checked, FILTER_VALIDATE_BOOLEAN)) {
            foreach ($this->pqrs->productos as $p) {
                if ((int)$p->requiere_recogida === 1 && $this->puedeRevisarProducto($p)) {
                    $this->seleccionOrm[] = (string)$p->id;
                }
            }
        }
    }

    public function aprobarOrmMasivo(): void
    {

        if ($this->pqrsEstaCerrada()) {
            return;
        }

        if (empty($this->seleccionOrm)) {
            return;
        }

        $productos = PqrsProducto::with('responsable')
            ->whereIn('id', $this->seleccionOrm)
            ->get();

        foreach ($productos as $producto) {
            if (!(int)$producto->requiere_recogida) {
                continue;
            }

            if (!$this->puedeRevisarProducto($producto)) {
                continue;
            }

            $producto->update([
                'estado_orm' => 'aprobada',
                'orm_revisada_por' => auth()->id(),
                'orm_fecha_revision' => now(),
            ]);
        }

        $this->seleccionOrm = [];
        $this->recalcularValorDeclaradoOrm();
        $this->refrescar();
    }

    public function rechazarOrmMasivo(): void
    {
        if ($this->pqrsEstaCerrada()) {
            return;
        }

        if (empty($this->seleccionOrm)) {
            return;
        }

        $productos = PqrsProducto::with('responsable')
            ->whereIn('id', $this->seleccionOrm)
            ->get();

        foreach ($productos as $producto) {
            if (!(int)$producto->requiere_recogida) {
                continue;
            }

            if (!$this->puedeRevisarProducto($producto)) {
                continue;
            }

            $producto->update([
                'estado_orm' => 'rechazada',
                'orm_revisada_por' => auth()->id(),
                'orm_fecha_revision' => now(),
            ]);
        }

        $this->seleccionOrm = [];
        $this->recalcularValorDeclaradoOrm();
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
            ->where('requiere_recogida', 1)
            ->where('estado', 'aprobado')
            ->where('estado_orm', 'aprobada')
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
        if (!$this->pqrs->orm) {
            return;
        }

        if ($this->pqrs->orm->estado !== 'programada') {
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
            ->where('requiere_recogida', 1)
            ->where('estado', 'aprobado')
            ->where('estado_orm', 'aprobada')
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
            'adjuntos',
        ]);
    }

    private function marcarPqrsComoRevisadaSiAplica(): void
    {
        // Solo marcar una vez
        if (!empty($this->pqrs->fecha_revisado)) {
            return;
        }

        // Si ya estaba en revisado, tampoco tocarla
        if (strtolower((string)$this->pqrs->estado) === 'revisado') {
            return;
        }

        $this->pqrs->update([
            'estado' => 'revisado',
            'fecha_revisado' => now(),
            'revisado_por' => auth()->id(),
        ]);
    }
    public function puedeCerrarPqrs(): bool
    {
        return $this->validacionCierrePqrs()['puede'];
    }
    public function pqrsEstaCerrada(): bool
    {
        return strtolower((string)($this->pqrs->estado ?? '')) === 'cerrado';
    }
    public function abrirModalCerrar(): void
    {
        if (!$this->puedeCerrarPqrs()) {
            return;
        }

        $this->tipo_acuerdo = $this->pqrs->tipo_acuerdo;
        $this->nota_acuerdo = $this->pqrs->nota_acuerdo;
        $this->valor_acuerdo = $this->pqrs->valor_acuerdo;
        $this->comentario_cierre = $this->pqrs->comentario_cierre;

        $this->resetValidation();
        $this->showModalCerrar = true;
    }

    public function cerrarModalCerrar(): void
    {
        $this->showModalCerrar = false;
    }

    public function guardarCierre(): void
    {
        if (!$this->puedeCerrarPqrs()) {
            return;
        }

        $this->validate([
            'tipo_acuerdo' => ['required', 'in:nota,no_aplica,atencion_comercial'],
            'comentario_cierre' => ['required', 'string'],
            'nota_acuerdo' => ['nullable', 'string', 'max:100'],
            'valor_acuerdo' => ['nullable', 'numeric', 'min:0'],
        ], [
            'tipo_acuerdo.required' => 'Debes seleccionar el tipo de acuerdo.',
            'comentario_cierre.required' => 'Debes ingresar el comentario de cierre.',
        ]);

        if ($this->tipo_acuerdo === 'nota') {
            $this->validate([
                'nota_acuerdo' => ['required', 'string', 'max:100'],
                'valor_acuerdo' => ['required', 'numeric', 'min:0'],
            ], [
                'nota_acuerdo.required' => 'El número de nota es obligatorio.',
                'valor_acuerdo.required' => 'El valor es obligatorio.',
            ]);
        }

        if ($this->tipo_acuerdo !== 'nota') {
            $this->nota_acuerdo = null;
            $this->valor_acuerdo = null;
        }

        $this->pqrs->update([
            'tipo_acuerdo' => $this->tipo_acuerdo,
            'nota_acuerdo' => $this->nota_acuerdo,
            'valor_acuerdo' => $this->valor_acuerdo ?: null,
            'comentario_cierre' => $this->comentario_cierre,
            'fecha_cierre' => now(),
            'estado' => 'cerrado',
            'cerrado_por' => auth()->id(),
        ]);

        $this->cerrarModalCerrar();
        $this->refrescar();
    }

    public function validacionCierrePqrs(): array
    {
        $faltantes = [];

        if (!$this->pqrs || !$this->pqrs->productos || $this->pqrs->productos->count() === 0) {
            $faltantes[] = 'La PQRS no tiene productos asociados.';
            return [
                'puede' => false,
                'faltantes' => $faltantes,
            ];
        }

        $productosPendientes = $this->pqrs->productos->filter(function ($p) {
            return strtolower((string)($p->estado ?? 'pendiente')) === 'pendiente';
        });

        if ($productosPendientes->count() > 0) {
            $faltantes[] = 'Hay ' . $productosPendientes->count() . ' producto(s) pendientes por revisar.';
        }

        $ormPendientes = $this->pqrs->productos
            ->where('requiere_recogida', 1)
            ->filter(function ($p) {
                return strtolower((string)($p->estado_orm ?? 'pendiente')) === 'pendiente';
            });

        if ($ormPendientes->count() > 0) {
            $faltantes[] = 'Hay ' . $ormPendientes->count() . ' producto(s) con ORM pendiente.';
        }

        if ($this->pqrs->orm && strtolower((string)$this->pqrs->orm->estado) !== 'en_bodega') {
            $faltantes[] = 'La ORM debe estar en estado "en bodega". Estado actual: ' . ($this->pqrs->orm->estado ?? 'sin estado') . '.';
        }

        if (strtolower((string)$this->pqrs->estado) === 'cerrado') {
            $faltantes[] = 'La PQRS ya está cerrada.';
        }

        return [
            'puede' => count($faltantes) === 0,
            'faltantes' => $faltantes,
        ];
    }

    public function render()
    {
        return view('livewire.admin.pqrs.solicitudes.detalles');
    }
}