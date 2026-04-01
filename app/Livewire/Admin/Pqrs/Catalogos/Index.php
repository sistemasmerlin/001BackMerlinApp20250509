<?php

namespace App\Livewire\Admin\Pqrs\Catalogos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PqrsMotivo;
use App\Models\PqrsSubmotivo;
use App\Models\PqrsCausal;
use App\Models\PqrsResponsable;

class Index extends Component
{
    use WithPagination;

    // selección
    public ?int $motivoId = null;
    public ?int $submotivoId = null;

    // búsquedas
    public string $qMotivos = '';
    public string $qSubmotivos = '';
    public string $qCausales = '';

    public ?int $causal_responsable_id = null;

    // ------- Modal flags -------
    public bool $showMotivoModal = false;
    public bool $showSubmotivoModal = false;
    public bool $showCausalModal = false;
    public bool $showDeleteModal = false;

    // ------- Modal Motivo -------
    public ?int $editMotivoId = null;
    public string $motivo_nombre = '';
    public int $motivo_orden = 1;
    public int $motivo_activo = 1;

    // ------- Modal Submotivo -------
    public ?int $editSubmotivoId = null;
    public string $submotivo_nombre = '';
    public int $submotivo_orden = 1;
    public int $submotivo_activo = 1;

    // ------- Modal Causal -------
    public ?int $editCausalId = null;
    public string $causal_nombre = '';
    public int $causal_orden = 1;
    public int $causal_activo = 1;
    public int $causal_requiere_adjunto = 0; // 1/0
    public int $causal_dias_limite_factura = 0; // 0 = sin límite (si quieres)
    public int $causal_visible_asesor = 1; // 1/0
    public int $causal_permite_recogida = 0; // 1/0
    // ------- Delete -------
    public ?int $deleteId = null;
    public string $deleteType = ''; // motivo|submotivo|causal

    protected function rules(): array
    {
        return [
            'motivo_nombre' => ['required','string','min:3','max:120'],
            'motivo_orden'  => ['required','integer','min:1','max:9999'],
            'motivo_activo' => ['required','integer','in:0,1'],

            'submotivo_nombre' => ['required','string','min:3','max:120'],
            'submotivo_orden'  => ['required','integer','min:1','max:9999'],
            'submotivo_activo' => ['required','integer','in:0,1'],

            'causal_nombre' => ['required','string','min:3','max:160'],
            'causal_orden'  => ['required','integer','min:1','max:9999'],
            'causal_activo' => ['required','integer','in:0,1'],

            'causal_responsable_id' => ['required','integer'],
            'causal_requiere_adjunto' => ['required','integer','in:0,1'],
            'causal_dias_limite_factura' => ['required','integer','min:0','max:9999'],
            'causal_visible_asesor' => ['required','integer','in:0,1'],

            'causal_permite_recogida' => ['required','integer','in:0,1'],

        ];
    }

    // reset paginación al buscar
    public function updatedQMotivos(): void { $this->resetPage('pm'); }
    public function updatedQSubmotivos(): void { $this->resetPage('ps'); }
    public function updatedQCausales(): void { $this->resetPage('pc'); }

    // selección
    public function seleccionarMotivo(int $id): void
    {
        $this->motivoId = $id;
        $this->submotivoId = null;
        $this->resetPage('ps');
        $this->resetPage('pc');
    }

    public function seleccionarSubmotivo(int $id): void
    {
        $this->submotivoId = $id;
        $this->resetPage('pc');
    }

    // helpers modales
    public function cerrarModales(): void
    {
        $this->showMotivoModal = false;
        $this->showSubmotivoModal = false;
        $this->showCausalModal = false;
        $this->showDeleteModal = false;
    }

    // Motivo CRUD
    public function nuevoMotivo(): void
    {
        $this->reset(['editMotivoId','motivo_nombre','motivo_orden','motivo_activo']);
        $this->motivo_orden = 1;
        $this->motivo_activo = 1;
        $this->showMotivoModal = true;
    }

    public function editarMotivo(int $id): void
    {
        $m = PqrsMotivo::findOrFail($id);

        $this->editMotivoId = $m->id;
        $this->motivo_nombre = (string) $m->nombre;
        $this->motivo_orden  = (int) $m->orden;
        $this->motivo_activo = $m->activo ? 1 : 0;

        $this->showMotivoModal = true;
    }

    public function guardarMotivo(): void
    {
        $this->validateOnly('motivo_nombre');
        $this->validateOnly('motivo_orden');
        $this->validateOnly('motivo_activo');

        $m = PqrsMotivo::updateOrCreate(
            ['id' => $this->editMotivoId],
            [
                'nombre' => $this->motivo_nombre,
                'orden'  => $this->motivo_orden,
                'activo' => (int) $this->motivo_activo,
            ]
        );

        if (!$this->motivoId) $this->motivoId = $m->id;

        session()->flash('success', 'Motivo guardado.');
        $this->showMotivoModal = false;
    }

    // Submotivo CRUD
    public function nuevoSubmotivo(): void
    {
        if (!$this->motivoId) {
            session()->flash('error', 'Selecciona un motivo primero.');
            return;
        }

        $this->reset(['editSubmotivoId','submotivo_nombre','submotivo_orden','submotivo_activo']);
        $this->submotivo_orden = 1;
        $this->submotivo_activo = 1;

        $this->showSubmotivoModal = true;
    }

    public function editarSubmotivo(int $id): void
    {
        $s = PqrsSubmotivo::findOrFail($id);

        $this->motivoId = (int) $s->motivo_id;
        $this->submotivoId = (int) $s->id;

        $this->editSubmotivoId = (int) $s->id;
        $this->submotivo_nombre = (string) $s->nombre;
        $this->submotivo_orden  = (int) $s->orden;
        $this->submotivo_activo = $s->activo ? 1 : 0;

        $this->showSubmotivoModal = true;
    }

    public function guardarSubmotivo(): void
    {
        if (!$this->motivoId) {
            session()->flash('error', 'Selecciona un motivo primero.');
            return;
        }

        $this->validateOnly('submotivo_nombre');
        $this->validateOnly('submotivo_orden');
        $this->validateOnly('submotivo_activo');

        $s = PqrsSubmotivo::updateOrCreate(
            ['id' => $this->editSubmotivoId],
            [
                'motivo_id' => (int) $this->motivoId,
                'nombre'    => $this->submotivo_nombre,
                'orden'     => $this->submotivo_orden,
                'activo'    => (int) $this->submotivo_activo,
            ]
        );

        $this->submotivoId = $s->id;

        session()->flash('success', 'Submotivo guardado.');
        $this->showSubmotivoModal = false;
        $this->resetPage('pc');
    }

    // Causal CRUD
    public function nuevaCausal(): void
    {
        if (!$this->submotivoId) {
            session()->flash('error', 'Selecciona un submotivo primero.');
            return;
        }

        $this->reset([
            'editCausalId',
            'causal_nombre',
            'causal_orden',
            'causal_activo',
            'causal_requiere_adjunto',
            'causal_permite_recogida',
            'causal_dias_limite_factura',
            'causal_visible_asesor',
        ]);

        $this->causal_responsable_id = null;
        $this->causal_orden = 1;
        $this->causal_activo = 1;
        $this->causal_requiere_adjunto = 0;
        $this->causal_dias_limite_factura = 0;
        $this->causal_permite_recogida = 0;
        $this->causal_visible_asesor = 1;
        $this->showCausalModal = true;
    }

    public function editarCausal(int $id): void
    {
        $c = PqrsCausal::findOrFail($id);

        $this->submotivoId = (int) $c->submotivo_id;

        $this->editCausalId = (int) $c->id;
        $this->causal_nombre = (string) $c->nombre;
        $this->causal_orden  = (int) $c->orden;
        $this->causal_activo = $c->activo ? 1 : 0;
        $this->causal_responsable_id = (int) $c->responsable_id;
        $this->causal_requiere_adjunto = (int) $c->requiere_adjunto;
        $this->causal_dias_limite_factura = (int) ($c->sla_dias ?? 0);
        $this->causal_visible_asesor = (int) $c->visible_asesor;
        $this->causal_permite_recogida = (int) $c->permite_recogida;

        $this->showCausalModal = true;
    }

    public function guardarCausal(): void
    {
        if (!$this->submotivoId) {
            session()->flash('error', 'Selecciona un submotivo primero.');
            return;
        }

        $this->validateOnly('causal_nombre');
        $this->validateOnly('causal_orden');
        $this->validateOnly('causal_activo');
        $this->validateOnly('causal_responsable_id');
        $this->validateOnly('causal_requiere_adjunto');
        $this->validateOnly('causal_dias_limite_factura');
        $this->validateOnly('causal_visible_asesor');

        PqrsCausal::updateOrCreate(
            ['id' => $this->editCausalId],
            [
                'submotivo_id'     => (int) $this->submotivoId,
                'nombre'           => $this->causal_nombre,
                'orden'            => $this->causal_orden,
                'responsable_id'   => (int) $this->causal_responsable_id,
                'requiere_adjunto' => (int) $this->causal_requiere_adjunto,
                'sla_dias' => (int) $this->causal_dias_limite_factura,
                'visible_asesor' => (int) $this->causal_visible_asesor,
                'permite_recogida' => (int) $this->causal_permite_recogida,
                'activo'           => (int) $this->causal_activo,
            ]
        );

        session()->flash('success', 'Causal guardada.');
        $this->showCausalModal = false;
    }

    // Delete
    public function confirmarDelete(string $type, int $id): void
    {
        $this->deleteType = $type;
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function eliminar(): void
    {
        if (!$this->deleteId || $this->deleteType === '') return;

        try {
            if ($this->deleteType === 'motivo') {
                PqrsMotivo::findOrFail($this->deleteId)->delete();
                if ($this->motivoId === $this->deleteId) {
                    $this->motivoId = null;
                    $this->submotivoId = null;
                }
                $this->resetPage('pm');
                $this->resetPage('ps');
                $this->resetPage('pc');
            }

            if ($this->deleteType === 'submotivo') {
                PqrsSubmotivo::findOrFail($this->deleteId)->delete();
                if ($this->submotivoId === $this->deleteId) {
                    $this->submotivoId = null;
                }
                $this->resetPage('ps');
                $this->resetPage('pc');
            }

            if ($this->deleteType === 'causal') {
                PqrsCausal::findOrFail($this->deleteId)->delete();
                $this->resetPage('pc');
            }

            session()->flash('success', 'Registro eliminado.');
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo eliminar. Puede estar relacionado con otros registros.');
        }

        $this->showDeleteModal = false;
        $this->reset(['deleteId','deleteType']);
    }

    public function render()
    {
        $motivos = PqrsMotivo::query()
            ->when($this->qMotivos !== '', fn($q) => $q->where('nombre','like','%'.$this->qMotivos.'%'))
            ->orderBy('orden')
            ->paginate(10, pageName: 'pm');

        $submotivos = PqrsSubmotivo::query()
            ->when($this->motivoId, fn($q) => $q->where('motivo_id', $this->motivoId))
            ->when($this->qSubmotivos !== '', fn($q) => $q->where('nombre','like','%'.$this->qSubmotivos.'%'))
            ->orderBy('orden')
            ->paginate(10, pageName: 'ps');

        $causales = PqrsCausal::query()
            ->when($this->submotivoId, fn($q) => $q->where('submotivo_id', $this->submotivoId))
            ->when($this->qCausales !== '', fn($q) => $q->where('nombre','like','%'.$this->qCausales.'%'))
            ->orderBy('orden')
            ->paginate(10, pageName: 'pc');

        $responsables = PqrsResponsable::query()
            ->where('activo', 1)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
        
        $relaciones = PqrsCausal::query()
            ->with(['submotivo.motivo', 'responsable'])
            ->when($this->motivoId, function ($q) {
                $q->whereHas('submotivo', fn($qq) => $qq->where('motivo_id', $this->motivoId));
            })
            ->when($this->submotivoId, fn($q) => $q->where('submotivo_id', $this->submotivoId))
            ->orderBy('submotivo_id')
            ->orderBy('orden')
            ->paginate(15, pageName: 'pr');

        return view('livewire.admin.pqrs.catalogos.index', compact('motivos','submotivos','causales','responsables','relaciones'));
    }
}
