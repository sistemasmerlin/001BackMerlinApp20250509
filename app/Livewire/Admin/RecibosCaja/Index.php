<?php

namespace App\Livewire\Admin\RecibosCaja;

use App\Exports\RecibosExport;
use App\Models\RecibosEncabezado;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MovimientoBancario;
use Illuminate\Support\Facades\DB;
use Throwable;


class Index extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $estado = '';
    public string $asesor = '';
    public ?string $fechaDesde = null;
    public ?string $fechaHasta = null;
    public int $perPage = 100;
    public bool $cruzandoMovimientos = false;
    public array $resultadoCruce = [];
    protected $queryString = [
        'buscar' => ['except' => ''],
        'estado' => ['except' => ''],
        'asesor' => ['except' => ''],
        'fechaDesde' => ['except' => null],
        'fechaHasta' => ['except' => null],
        'perPage' => ['except' => 25],
    ];

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function updatingAsesor(): void
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

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'buscar',
            'estado',
            'asesor',
            'fechaDesde',
            'fechaHasta',
        ]);

        $this->resetPage();
    }

    private function consultaRecibos(): Builder
    {
        return RecibosEncabezado::query()
            ->with('bancoRecaudo')
            ->withCount([
                'cxcs',
                'retenciones',
            ])
            ->when(
                trim($this->buscar) !== '',
                function (Builder $query): void {
                    $buscar = trim($this->buscar);

                    $query->where(function (Builder $subquery) use ($buscar): void {
                        $subquery
                            ->where('codigo_recibo', 'like', "%{$buscar}%")
                            ->orWhere('nit_cliente', 'like', "%{$buscar}%")
                            ->orWhere('razon_social', 'like', "%{$buscar}%")
                            ->orWhere('email_cliente', 'like', "%{$buscar}%")
                            ->orWhere('numero_soporte', 'like', "%{$buscar}%")
                            ->orWhere('id_vendedor', 'like', "%{$buscar}%")
                            ->orWhere('nombre_asesor', 'like', "%{$buscar}%");
                    });
                }
            )
            ->when(
                $this->estado !== '',
                fn(Builder $query) => $query->where(
                    'estado',
                    $this->estado
                )
            )
            ->when(
                trim($this->asesor) !== '',
                function (Builder $query): void {
                    $asesor = trim($this->asesor);

                    $query->where(function (Builder $subquery) use ($asesor): void {
                        $subquery
                            ->where('id_vendedor', 'like', "%{$asesor}%")
                            ->orWhere('nombre_asesor', 'like', "%{$asesor}%")
                            ->orWhere('email_asesor', 'like', "%{$asesor}%");
                    });
                }
            )
            ->when(
                $this->fechaDesde,
                fn(Builder $query) => $query->whereDate(
                    'fecha_recibo',
                    '>=',
                    $this->fechaDesde
                )
            )
            ->when(
                $this->fechaHasta,
                fn(Builder $query) => $query->whereDate(
                    'fecha_recibo',
                    '<=',
                    $this->fechaHasta
                )
            )
            ->latest('id');
    }

    public function exportarExcel()
    {
        $estadoExportar = $this->estado ?: 'APROBADO';

        $recibos = $this->consultaRecibos()
            ->where('estado', $estadoExportar)
            ->pluck('id')
            ->all();

        if (empty($recibos)) {
            session()->flash(
                'error',
                "No existen recibos en estado {$estadoExportar} para exportar."
            );

            return null;
        }

        $nombreArchivo = 'PlanoRC_'
            . now()->format('Ymd_His')
            . '.xlsx';

        return Excel::download(
            new RecibosExport(
                estado: $estadoExportar,
                reciboIds: $recibos,
            ),
            $nombreArchivo
        );
    }

    public function cruzarRecibosConMovimientos(): void
    {
        if ($this->cruzandoMovimientos) {
            return;
        }

        $this->cruzandoMovimientos = true;

        $resultado = [
            'revisados' => 0,
            'aplicados' => 0,
            'sin_coincidencia' => 0,
            'multiples' => 0,
            'omitidos' => 0,
            'errores' => 0,
        ];

        try {
            RecibosEncabezado::query()
                ->with([
                    'bancoRecaudo',
                    'movimientoBancario',
                ])
                ->whereDoesntHave('movimientoBancario')
                ->orderBy('id')
                ->chunkById(100, function ($recibos) use (&$resultado): void {
                    foreach ($recibos as $recibo) {
                        $resultado['revisados']++;

                        $nombreBanco = strtoupper(
                            trim(
                                (string) $recibo
                                    ->bancoRecaudo
                                    ?->descripcion_banco
                            )
                        );

                        if (!str_contains($nombreBanco, 'BANCOLOMBIA')) {
                            $resultado['omitidos']++;
                            continue;
                        }

                        $numeroSoporte = trim(
                            (string) $recibo->numero_soporte
                        );

                        $nitCliente = trim(
                            (string) $recibo->nit_cliente
                        );

                        $fechaComprobante = $recibo
                            ->fecha_comprobante
                            ?->format('Y-m-d');

                        $valorRecibo = round(
                            (float) $recibo->total_recibido,
                            2
                        );

                        if (
                            ($numeroSoporte === '' && $nitCliente === '')
                            || !$fechaComprobante
                            || $valorRecibo <= 0
                        ) {
                            $resultado['omitidos']++;
                            continue;
                        }

                        try {
                            DB::transaction(function () use (
                                $recibo,
                                $numeroSoporte,
                                $nitCliente,
                                $fechaComprobante,
                                $valorRecibo,
                                &$resultado
                            ): void {
                                /*
                            |--------------------------------------------------------------------------
                            | Evitar que otro proceso haya relacionado el recibo
                            |--------------------------------------------------------------------------
                            */

                                $reciboBloqueado = RecibosEncabezado::query()
                                    ->whereKey($recibo->id)
                                    ->lockForUpdate()
                                    ->first();

                                if (!$reciboBloqueado) {
                                    $resultado['errores']++;
                                    return;
                                }

                                $yaRelacionado = MovimientoBancario::query()
                                    ->where(
                                        'recibo_encabezado_id',
                                        $reciboBloqueado->id
                                    )
                                    ->exists();

                                if ($yaRelacionado) {
                                    $resultado['omitidos']++;
                                    return;
                                }

                                /*
                            |--------------------------------------------------------------------------
                            | Buscar máximo dos coincidencias
                            | Con dos ya sabemos que es ambiguo
                            |--------------------------------------------------------------------------
                            */

                                $movimientos = MovimientoBancario::query()
                                    ->whereNull('recibo_encabezado_id')
                                    ->where('procesado', false)
                                    ->whereDate(
                                        'fecha_movimiento',
                                        $fechaComprobante
                                    )
                                    ->whereRaw(
                                        'ROUND(ABS(valor), 2) = ?',
                                        [
                                            number_format(
                                                $valorRecibo,
                                                2,
                                                '.',
                                                ''
                                            ),
                                        ]
                                    )
                                    ->where(function ($query) use (
                                        $numeroSoporte,
                                        $nitCliente
                                    ): void {
                                        if ($numeroSoporte !== '') {
                                            $query->where(function ($subquery) use (
                                                $numeroSoporte
                                            ): void {
                                                $subquery
                                                    ->whereRaw(
                                                        'TRIM(referencia_1) = ?',
                                                        [$numeroSoporte]
                                                    )
                                                    ->orWhereRaw(
                                                        'TRIM(referencia_2) = ?',
                                                        [$numeroSoporte]
                                                    )
                                                    ->orWhereRaw(
                                                        'TRIM(referencia_3) = ?',
                                                        [$numeroSoporte]
                                                    );
                                            });
                                        }

                                        if ($nitCliente !== '') {
                                            $metodo = $numeroSoporte !== ''
                                                ? 'orWhere'
                                                : 'where';

                                            $query->{$metodo}(
                                                function ($subquery) use (
                                                    $nitCliente
                                                ): void {
                                                    $subquery
                                                        ->whereRaw(
                                                            'TRIM(referencia_1) = ?',
                                                            [$nitCliente]
                                                        )
                                                        ->orWhereRaw(
                                                            'TRIM(referencia_2) = ?',
                                                            [$nitCliente]
                                                        )
                                                        ->orWhereRaw(
                                                            'TRIM(referencia_3) = ?',
                                                            [$nitCliente]
                                                        );
                                                }
                                            );
                                        }
                                    })
                                    ->orderBy('id')
                                    ->lockForUpdate()
                                    ->limit(2)
                                    ->get();

                                if ($movimientos->isEmpty()) {
                                    $resultado['sin_coincidencia']++;
                                    return;
                                }

                                if ($movimientos->count() > 1) {
                                    $resultado['multiples']++;
                                    return;
                                }

                                $movimiento = $movimientos->first();

                                $movimiento->update([
                                    'recibo_encabezado_id' =>
                                    $reciboBloqueado->id,

                                    'procesado' => true,

                                    'estado' => 'APLICADO',

                                    'fecha_aplicacion' => now(),
                                ]);

                                $resultado['aplicados']++;
                            });
                        } catch (Throwable $e) {
                            report($e);

                            $resultado['errores']++;
                        }
                    }
                });

            $this->resultadoCruce = $resultado;

            session()->flash(
                'success',
                'Cruce finalizado. '
                    . "Aplicados: {$resultado['aplicados']}. "
                    . "Sin coincidencia: {$resultado['sin_coincidencia']}. "
                    . "Con múltiples coincidencias: {$resultado['multiples']}."
            );

            $this->resetPage();
        } catch (Throwable $e) {
            report($e);

            session()->flash(
                'error',
                'No fue posible completar el cruce: '
                    . $e->getMessage()
            );
        } finally {
            $this->cruzandoMovimientos = false;
        }
    }

    public function render()
    {
        $recibos = $this->consultaRecibos()
            ->paginate($this->perPage);

        $consultaResumen = $this->consultaRecibos();

        $resumen = [
            'cantidad' => (clone $consultaResumen)->count(),

            'total_recibido' => (float) (
                (clone $consultaResumen)->sum('total_recibido')
            ),

            'total_restante' => (float) (
                (clone $consultaResumen)->sum('total_restante')
            ),
        ];

        return view('livewire.admin.recibos-caja.index', [
            'recibos' => $recibos,
            'resumen' => $resumen,
        ]);
    }
}
