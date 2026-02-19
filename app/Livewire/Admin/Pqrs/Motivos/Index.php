<?php

namespace App\Livewire\Admin\Pqrs\Motivos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PqrsMotivo;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public int $perPage = 10;

    public ?int $editId = null;

    public string $nombre = '';
    public int $orden = 0;
    public $activo = 1; // select string/int

    protected $paginationTheme = 'tailwind';

    public function updatingQ() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function rules(): array
    {
        return [
            'nombre' => ['required','string','max:150', 'unique:pqrs_motivos,nombre,' . ($this->editId ?? 'NULL') ],
            'orden'  => ['nullable','integer','min:0'],
            'activo' => ['required','in:0,1'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('openModal', name: 'modal-motivo');
    }

    public function edit(int $id): void
    {
        $m = PqrsMotivo::findOrFail($id);

        $this->editId = $m->id;
        $this->nombre = (string) $m->nombre;
        $this->orden  = (int) ($m->orden ?? 0);
        $this->activo = $m->activo ? 1 : 0;

        $this->dispatch('openModal', name: 'modal-motivo');
    }

    public function save(): void
    {
        $data = $this->validate();

        PqrsMotivo::updateOrCreate(
            ['id' => $this->editId],
            [
                'nombre' => trim($data['nombre']),
                'orden'  => (int) ($data['orden'] ?? 0),
                'activo' => (bool) $data['activo'],
            ]
        );

        session()->flash('success', $this->editId ? 'Motivo actualizado.' : 'Motivo creado.');
        $this->dispatch('closeModal', name: 'modal-motivo');
        $this->resetForm();
    }

    public function toggleActivo(int $id): void
    {
        $m = PqrsMotivo::findOrFail($id);
        $m->activo = !$m->activo;
        $m->save();

        session()->flash('success', 'Estado actualizado.');
    }

    private function resetForm(): void
    {
        $this->reset(['editId','nombre','orden','activo']);
        $this->activo = 1;
        $this->orden = 0;
        $this->resetValidation();
    }

    public function render()
    {
        $items = PqrsMotivo::query()
            ->when($this->q !== '', function ($qq) {
                $qq->where('nombre', 'like', '%' . $this->q . '%');
            })
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.admin.pqrs.motivos.index', compact('items'));
    }
}
