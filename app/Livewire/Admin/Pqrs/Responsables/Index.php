<?php

namespace App\Livewire\Admin\Pqrs\Responsables;

use App\Models\PqrsResponsable;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public int $perPage = 25;

    public ?int $editId = null;

    public string $nombre = '';
    public string $correos_texto = ''; // separados por coma
    public ?int $sla_dias_default = null;
    public bool $activo = true;
    public int $orden = 0;

    protected $paginationTheme = 'bootstrap';

    public function updatingQ() { $this->resetPage(); }

    public function rules(): array
    {
        return [
            'nombre' => ['required','string','max:150'],
            'correos_texto' => ['nullable','string','max:2000'],
            'sla_dias_default' => ['nullable','integer','min:0','max:365'],
            'activo' => ['boolean'],
            'orden' => ['integer','min:0','max:9999'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('openModal', id: 'modalResponsable');
    }

    public function edit(int $id): void
    {
        $r = PqrsResponsable::findOrFail($id);

        $this->editId = $r->id;
        $this->nombre = $r->nombre;
        $this->correos_texto = is_array($r->correos) ? implode(', ', $r->correos) : '';
        $this->sla_dias_default = $r->sla_dias_default;
        $this->activo = (bool) $r->activo;
        $this->orden = (int) $r->orden;

        $this->dispatch('openModal', id: 'modalResponsable');
    }

    public function save(): void
    {
        $this->validate();

        $correos = collect(explode(',', $this->correos_texto))
            ->map(fn($e) => trim($e))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // valida formato email (suave)
        foreach ($correos as $mail) {
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $this->addError('correos_texto', "Correo inválido: $mail");
                return;
            }
        }

        PqrsResponsable::updateOrCreate(
            ['id' => $this->editId],
            [
                'nombre' => $this->nombre,
                'correos' => count($correos) ? $correos : null,
                'sla_dias_default' => $this->sla_dias_default,
                'activo' => $this->activo,
                'orden' => $this->orden,
            ]
        );

        $this->dispatch('closeModal', id: 'modalResponsable');
        session()->flash('success', 'Responsable guardado correctamente.');
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->editId = $id;
        $this->dispatch('openModal', id: 'modalDelete');
    }

    public function delete(): void
    {
        if (!$this->editId) return;

        $r = PqrsResponsable::findOrFail($this->editId);

        // protección: si tiene causales asociadas, no borrar
        if ($r->causales()->exists()) {
            session()->flash('error', 'No puedes eliminar: este responsable tiene causales asociadas.');
            $this->dispatch('closeModal', id: 'modalDelete');
            $this->editId = null;
            return;
        }

        $r->delete();
        $this->dispatch('closeModal', id: 'modalDelete');
        session()->flash('success', 'Responsable eliminado.');
        $this->editId = null;
    }

    private function resetForm(): void
    {
        $this->editId = null;
        $this->nombre = '';
        $this->correos_texto = '';
        $this->sla_dias_default = null;
        $this->activo = true;
        $this->orden = 0;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $items = PqrsResponsable::query()
            ->when($this->q !== '', fn($q) => $q->where('nombre', 'like', "%{$this->q}%"))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.admin.pqrs.responsables.index', compact('items'));
    }
}
