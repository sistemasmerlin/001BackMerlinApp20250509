<?php

namespace App\Livewire\Admin\Fletes;

use App\Exports\FletesExport;
use App\Imports\ImportFletes;
use App\Models\FleteCiudad;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $archivoCsv;
    public $excel_fletes;

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */

    public string $buscar = '';

    public int $perPage = 50;

    /*
    |--------------------------------------------------------------------------
    | Modal de edición
    |--------------------------------------------------------------------------
    */

    public bool $modalEditar = false;

    public ?int $fleteId = null;

    public $depto;
    public $cod_depto;
    public $ciudad;
    public $cod_ciudad;
    public $menor;
    public $mayor;
    public $minimo;
    public $entrega;
    public $monto;
    public $monto_minimo;

    protected $paginationTheme = 'tailwind';

    protected $queryString = [
        'buscar' => ['except' => ''],
        'perPage' => ['except' => 50],
        'page' => ['except' => 1],
    ];

    protected $rules = [
        'excel_fletes' => 'required|file|mimes:xls,xlsx|max:2048',
    ];

    /*
    |--------------------------------------------------------------------------
    | Eventos de filtros
    |--------------------------------------------------------------------------
    */

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltro(): void
    {
        $this->reset('buscar');
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Consulta centralizada
    |--------------------------------------------------------------------------
    */

    private function consultaFletes(): Builder
    {
        $buscar = trim($this->buscar);

        return FleteCiudad::query()
            ->where('estado', 1)
            ->when($buscar !== '', function (Builder $query) use ($buscar) {
                $query->where(function (Builder $subQuery) use ($buscar) {
                    $subQuery
                        ->where('ciudad', 'like', "%{$buscar}%")
                        ->orWhere('depto', 'like', "%{$buscar}%")
                        ->orWhere('cod_ciudad', 'like', "%{$buscar}%")
                        ->orWhere('cod_depto', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('depto')
            ->orderBy('ciudad');
    }

    /*
    |--------------------------------------------------------------------------
    | Importación CSV
    |--------------------------------------------------------------------------
    */

    public function importarCsv(): void
    {
        $this->validate([
            'archivoCsv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        FleteCiudad::truncate();

        $path = $this->archivoCsv->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            session()->flash('error', 'No fue posible abrir el archivo CSV.');
            return;
        }

        $index = 0;

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            if ($index === 0) {
                $index++;
                continue;
            }

            if (count($row) < 10) {
                $index++;
                continue;
            }

            FleteCiudad::create([
                'depto'        => trim($row[0] ?? ''),
                'cod_depto'    => trim($row[1] ?? ''),
                'ciudad'       => trim($row[2] ?? ''),
                'cod_ciudad'   => trim($row[3] ?? ''),
                'menor'        => trim($row[4] ?? 0),
                'mayor'        => trim($row[5] ?? 0),
                'minimo'       => trim($row[6] ?? 0),
                'entrega'      => trim($row[7] ?? 0),
                'monto'        => trim($row[8] ?? 0),
                'monto_minimo' => trim($row[9] ?? 0),
            ]);

            $index++;
        }

        fclose($handle);

        $this->reset('archivoCsv');
        $this->resetPage();

        session()->flash(
            'success',
            'Detalles importados desde CSV correctamente.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Importación Excel
    |--------------------------------------------------------------------------
    */

    public function importarFlete(): void
    {
        $this->validate();

        try {
            FleteCiudad::truncate();

            Excel::import(
                new ImportFletes(),
                $this->excel_fletes->getRealPath()
            );

            $this->reset('excel_fletes');
            $this->resetPage();

            session()->flash(
                'success',
                'Archivo importado correctamente.'
            );
        } catch (\Throwable $e) {
            report($e);

            session()->flash(
                'error',
                'No fue posible importar el archivo.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Exportación
    |--------------------------------------------------------------------------
    */

    public function exportar(): BinaryFileResponse
    {
        $fletes = $this->consultaFletes()->get();

        $nombreArchivo = 'fletes-ciudades-'
            . now()->format('Y-m-d_H-i-s')
            . '.xlsx';

        return Excel::download(
            new FletesExport($fletes),
            $nombreArchivo
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar
    |--------------------------------------------------------------------------
    */

    public function eliminarFlete(int $id): void
    {
        try {
            FleteCiudad::findOrFail($id)->delete();

            $this->resetPage();

            session()->flash(
                'success',
                'Flete eliminado correctamente.'
            );
        } catch (\Throwable $e) {
            report($e);

            session()->flash(
                'error',
                'Hubo un problema al eliminar el flete.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Editar
    |--------------------------------------------------------------------------
    */

    public function editarFlete(int $id): void
    {
        $flete = FleteCiudad::findOrFail($id);

        $this->fleteId = $flete->id;
        $this->depto = $flete->depto;
        $this->cod_depto = $flete->cod_depto;
        $this->ciudad = $flete->ciudad;
        $this->cod_ciudad = $flete->cod_ciudad;
        $this->menor = $flete->menor;
        $this->mayor = $flete->mayor;
        $this->minimo = $flete->minimo;
        $this->entrega = $flete->entrega;
        $this->monto = $flete->monto;
        $this->monto_minimo = $flete->monto_minimo;

        $this->resetValidation();

        $this->modalEditar = true;
    }

    public function cerrarModal(): void
    {
        $this->modalEditar = false;
        $this->resetValidation();

        $this->reset([
            'fleteId',
            'depto',
            'cod_depto',
            'ciudad',
            'cod_ciudad',
            'menor',
            'mayor',
            'minimo',
            'entrega',
            'monto',
            'monto_minimo',
        ]);
    }

    public function actualizarFlete(): void
    {
        $datos = $this->validate([
            'depto'        => 'required|string|max:191',
            'cod_depto'    => 'required|string|max:50',
            'ciudad'       => 'required|string|max:191',
            'cod_ciudad'   => 'required|string|max:50',
            'menor'        => 'required|numeric',
            'mayor'        => 'required|numeric',
            'minimo'       => 'required|numeric|min:0',
            'entrega'      => 'required|integer|min:0',
            'monto'        => 'required|numeric|min:0',
            'monto_minimo' => 'required|numeric|min:0',
        ]);

        try {
            FleteCiudad::findOrFail($this->fleteId)->update($datos);

            $this->cerrarModal();

            session()->flash(
                'success',
                'Flete actualizado correctamente.'
            );
        } catch (\Throwable $e) {
            report($e);

            session()->flash(
                'error',
                'No fue posible actualizar el flete.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Vista
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view('livewire.admin.fletes.index', [
            'fletes' => $this->consultaFletes()
                ->paginate($this->perPage),
        ]);
    }
}