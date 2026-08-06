<?php

namespace App\Livewire\Admin\MovimientosBancarios;

use App\Imports\MovimientosBancariosImport;
use App\Models\MovimientoBancario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $archivo;

    public string $buscar = '';
    public string $tipoMovimiento = '';
    public string $estadoProcesado = '';

    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;

    public int $perPage = 50;

    public bool $importando = false;

    public array $resultadoImportacion = [];
    public array $erroresImportacion = [];

    protected $queryString = [
        'buscar' => ['except' => ''],
        'tipoMovimiento' => ['except' => ''],
        'estadoProcesado' => ['except' => ''],
        'fechaDesde' => ['except' => null],
        'fechaHasta' => ['except' => null],
    ];

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingTipoMovimiento(): void
    {
        $this->resetPage();
    }

    public function updatingEstadoProcesado(): void
    {
        $this->resetPage();
    }

    public function updatingFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFechaHasta(): void
    {
        $this->resetPage();
    }

    public function importar(): void
    {
        $this->validate([
            'archivo' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:15360',
            ],
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.mimes' => 'El archivo debe ser Excel XLSX o XLS.',
            'archivo.max' => 'El archivo no puede superar los 15 MB.',
        ]);

        $this->importando = true;
        $this->resultadoImportacion = [];
        $this->erroresImportacion = [];

        try {
            $importador = new MovimientosBancariosImport(
                $this->archivo->getClientOriginalName()
            );

            DB::beginTransaction();

            Excel::import(
                $importador,
                $this->archivo
            );

            DB::commit();

            $this->resultadoImportacion = [
                'importados' => $importador->importados,
                'duplicados' => $importador->duplicados,
                'omitidos' => $importador->omitidos,
            ];

            $this->erroresImportacion = array_slice(
                $importador->errores,
                0,
                50
            );

            $this->reset('archivo');
            $this->resetPage();

            session()->flash(
                'success',
                "Importación finalizada: {$importador->importados} movimientos importados."
            );
        } catch (Throwable $e) {
            DB::rollBack();

            session()->flash(
                'error',
                'No fue posible importar el archivo: ' . $e->getMessage()
            );
        } finally {
            $this->importando = false;
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'buscar',
            'tipoMovimiento',
            'estadoProcesado',
            'fechaDesde',
            'fechaHasta',
        ]);

        $this->resetPage();
    }

    private function consultaMovimientos(): Builder
    {
        return MovimientoBancario::query()
            ->with('recibo')
            ->when(
                trim($this->buscar) !== '',
                function (Builder $query): void {
                    $buscar = trim($this->buscar);

                    $query->where(function (Builder $subquery) use ($buscar): void {
                        $subquery
                            ->where(
                                'descripcion_movimiento',
                                'like',
                                "%{$buscar}%"
                            )
                            ->orWhere(
                                'oficina_canal',
                                'like',
                                "%{$buscar}%"
                            )
                            ->orWhere(
                                'referencia_1',
                                'like',
                                "%{$buscar}%"
                            )
                            ->orWhere(
                                'referencia_2',
                                'like',
                                "%{$buscar}%"
                            )
                            ->orWhere(
                                'referencia_3',
                                'like',
                                "%{$buscar}%"
                            )
                            ->orWhere(
                                'archivo_origen',
                                'like',
                                "%{$buscar}%"
                            );
                    });
                }
            )
            ->when(
                $this->tipoMovimiento !== '',
                fn (Builder $query) => $query->where(
                    'tipo_movimiento',
                    $this->tipoMovimiento
                )
            )
            ->when(
                $this->estadoProcesado !== '',
                fn (Builder $query) => $query->where(
                    'procesado',
                    $this->estadoProcesado === '1'
                )
            )
            ->when(
                $this->fechaDesde,
                fn (Builder $query) => $query->whereDate(
                    'fecha_movimiento',
                    '>=',
                    $this->fechaDesde
                )
            )
            ->when(
                $this->fechaHasta,
                fn (Builder $query) => $query->whereDate(
                    'fecha_movimiento',
                    '<=',
                    $this->fechaHasta
                )
            )
            ->latest('fecha_movimiento')
            ->latest('id');
    }

    public function render()
    {
        $consulta = $this->consultaMovimientos();

        $movimientos = (clone $consulta)
            ->paginate($this->perPage);

        $resumen = [
            'cantidad' => (clone $consulta)->count(),

            'creditos' => (float) (
                (clone $consulta)
                    ->where('tipo_movimiento', 'CREDITO')
                    ->sum('valor')
            ),

            'debitos' => (float) (
                (clone $consulta)
                    ->where('tipo_movimiento', 'DEBITO')
                    ->sum('valor')
            ),

            'saldo' => (float) (
                (clone $consulta)->sum('valor')
            ),
        ];

        return view(
            'livewire.admin.movimientos-bancarios.index',
            [
                'movimientos' => $movimientos,
                'resumen' => $resumen,
            ]
        );
    }
}