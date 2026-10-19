<?php

namespace App\Livewire\Admin\ProductosEmpaques;

use App\Exports\ProductosEmpaquesExport;
use App\Imports\ProductosEmpaquesImport;
use App\Models\ProductoEmpaque;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $buscar = '';

    public $archivo;

    public string $mensaje = '';

    public function mount(): void
    {
        $this->autorizar();
    }

    private function autorizar(): void
    {
        Gate::authorize('Logistica');
        Gate::authorize('Administrar fletes');
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedArchivo(): void
    {
        $this->resetValidation();
        $this->mensaje = '';
    }

    public function importar(): void
    {
        $this->autorizar();
        $this->mensaje = '';
        $this->resetValidation();

        $this->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'archivo.required' => 'Seleccione un archivo Excel.',
            'archivo.mimes' => 'El archivo debe ser XLSX o XLS.',
            'archivo.max' => 'El archivo no debe superar 5 MB.',
        ]);

        try {
            $import = new ProductosEmpaquesImport();

            Excel::import($import, $this->archivo);

            $this->mensaje =
                "Importación completada: {$import->procesadas} referencias procesadas.";

            $this->reset('archivo');
            $this->resetPage();
        } catch (ValidationException $e) {
            foreach ($e->errors() as $campo => $mensajes) {
                foreach ($mensajes as $mensaje) {
                    $this->addError($campo, $mensaje);
                }
            }
        } catch (Throwable $e) {
            report($e);

            $this->addError(
                'archivo',
                'No fue posible importar el archivo. Revise el formato y el registro de Laravel.'
            );
        }
    }

    public function exportar()
    {
        $this->autorizar();

        return Excel::download(
            new ProductosEmpaquesExport($this->buscar),
            'unidades_por_empaque_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function plantilla()
    {
        $this->autorizar();

        return Excel::download(
            new ProductosEmpaquesExport(plantilla: true),
            'plantilla_unidades_por_empaque.xlsx'
        );
    }

    public function render()
    {
        $this->autorizar();

        return view('livewire.admin.productos-empaques.index', [
            'productos' => ProductoEmpaque::query()
                ->buscar($this->buscar)
                ->orderBy('referencia')
                ->paginate(25),

            'totalReferencias' => ProductoEmpaque::count(),
        ]);
    }
}