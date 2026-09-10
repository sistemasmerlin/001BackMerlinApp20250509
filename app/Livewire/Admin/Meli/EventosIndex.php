<?php

namespace App\Livewire\Admin\Meli;

use App\Models\MeliEvento;
use Livewire\Component;
use Livewire\WithPagination;

class EventosIndex extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $buscar = '';
    public string $tipo = '';
    public string $estado = '';

    public ?int $eventoSeleccionadoId = null;

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingTipo(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'buscar',
            'tipo',
            'estado',
            'eventoSeleccionadoId',
        ]);

        $this->resetPage();
    }

    public function verEvento(int $id): void
    {
        $this->eventoSeleccionadoId = $id;
    }

    public function cerrarDetalle(): void
    {
        $this->eventoSeleccionadoId = null;
    }

public function render()
{
    $eventos = MeliEvento::query()
        ->when($this->tipo !== '', function ($query) {
            $query->where('tipo', $this->tipo);
        })
        ->when($this->estado !== '', function ($query) {
            $query->where('estado', $this->estado);
        })
        ->when($this->buscar !== '', function ($query) {
            $buscar = '%' . trim($this->buscar) . '%';

            $query->where(function ($subquery) use ($buscar) {
                $subquery
                    ->where('topic', 'like', $buscar)
                    ->orWhere('resource', 'like', $buscar)
                    ->orWhere('user_id', 'like', $buscar)
                    ->orWhere('application_id', 'like', $buscar)
                    ->orWhere('payload', 'like', $buscar);
            });
        })
        ->latest('id')
        ->paginate(30);

    $eventoSeleccionado = $this->eventoSeleccionadoId
        ? MeliEvento::find($this->eventoSeleccionadoId)
        : null;

    return view('livewire.admin.meli.eventos-index', [
        'eventos' => $eventos,
        'eventoSeleccionado' => $eventoSeleccionado,
    ])->layout('components.layouts.app', [
        'title' => 'Notificaciones Mercado Libre',
    ]);
}
}