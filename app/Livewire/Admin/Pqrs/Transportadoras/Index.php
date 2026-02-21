<?php

namespace App\Livewire\Admin\Pqrs\Transportadoras;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transportadora;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public int $perPage = 10;
    public bool $showDeleted = false;

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingId = null;
    public ?int $deleteId = null;

    public array $form = [
        'nit' => '',
        'razon_social' => '',
        'direccion' => '',
        'departamento' => '',
        'ciudad' => '',
    ];

    protected function rules(): array
    {
        return [
            'form.nit' => ['required','string','max:30'],
            'form.razon_social' => ['required','string','max:200'],
            'form.direccion' => ['nullable','string','max:200'],
            'form.departamento' => ['nullable','string','max:100'],
            'form.ciudad' => ['nullable','string','max:100'],
        ];
    }

    public function updatingQ() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }
    public function updatingShowDeleted() { $this->resetPage(); }

    public function openCreate()
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->form = [
            'nit' => '',
            'razon_social' => '',
            'direccion' => '',
            'departamento' => '',
            'ciudad' => '',
        ];
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $this->resetValidation();
        $t = Transportadora::withTrashed()->findOrFail($id);

        $this->editingId = $t->id;
        $this->form = [
            'nit' => $t->nit,
            'razon_social' => $t->razon_social,
            'direccion' => $t->direccion,
            'departamento' => $t->departamento,
            'ciudad' => $t->ciudad,
        ];

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $t = Transportadora::withTrashed()->findOrFail($this->editingId);
            $t->update($this->form);
            session()->flash('success', 'Transportadora actualizada.');
        } else {
            Transportadora::create($this->form);
            session()->flash('success', 'Transportadora creada.');
        }

        $this->showModal = false;
    }

    public function confirmDelete(int $id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function delete()
    {
        $t = Transportadora::findOrFail($this->deleteId);
        $t->delete();

        session()->flash('success', 'Transportadora eliminada (Soft Delete).');

        $this->closeDeleteModal();
    }

    public function restore(int $id)
    {
        $t = Transportadora::withTrashed()->findOrFail($id);
        $t->restore();

        session()->flash('success', 'Transportadora restaurada.');
    }

    public function render()
    {
        $query = Transportadora::query();

        if ($this->showDeleted) $query->withTrashed();

        if ($this->q !== '') {
            $q = '%' . $this->q . '%';
            $query->where(function($w) use ($q) {
                $w->where('nit', 'like', $q)
                  ->orWhere('razon_social', 'like', $q)
                  ->orWhere('ciudad', 'like', $q)
                  ->orWhere('departamento', 'like', $q);
            });
        }

        return view('livewire.admin.pqrs.transportadoras.index', [
            'items' => $query->orderByDesc('id')->paginate($this->perPage),
        ]);
    }
}
