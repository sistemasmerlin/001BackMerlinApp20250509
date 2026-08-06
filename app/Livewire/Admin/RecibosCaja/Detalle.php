<?php

namespace App\Livewire\Admin\RecibosCaja;

use App\Exports\RecibosExport;
use App\Models\RecibosEncabezado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MovimientoBancario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class Detalle extends Component
{
    public RecibosEncabezado $recibo;
    public string $nuevoEstado = '';
    public string $notasEstado = '';
    public int $cantidadMovimientosCoincidentes = 0;
    public bool $modalEstado = false;
    public ?MovimientoBancario $movimientoBancario = null;
    public bool $esBancolombia = false;
    public bool $busquedaComprobanteRealizada = false;
    public bool $comprobanteValido = false;
    public float $totalAplicadoRecibo = 0;
    public string $mensajeValidacionComprobante = '';
    public function mount(RecibosEncabezado $recibo): void
    {
        $this->recibo = $recibo->load([
            'bancoRecaudo',
            'caja',
            'cxcs',
            'ingresos',
            'retenciones',
        ]);

        $this->nuevoEstado = $this->recibo->estado;

        $this->buscarComprobanteBancolombia();
    }

    public function abrirModalEstado(): void
    {
        $this->nuevoEstado = $this->recibo->estado;
        $this->notasEstado = '';
        $this->modalEstado = true;
    }

    public function cerrarModalEstado(): void
    {
        $this->modalEstado = false;
        $this->notasEstado = '';
        $this->resetValidation();
    }

    public function actualizarEstado(): void
    {
        $this->validate([
            'nuevoEstado' => [
                'required',
                Rule::in([
                    'RECIBIDO',
                    'EN_REVISION',
                    'APROBADO',
                    'RECHAZADO',
                    'EXPORTADO',
                ]),
            ],

            'notasEstado' => [
                Rule::requiredIf(
                    in_array($this->nuevoEstado, [
                        'RECHAZADO',
                        'EN_REVISION',
                    ], true)
                ),
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'nuevoEstado.required' => 'Debes seleccionar un estado.',
            'nuevoEstado.in' => 'El estado seleccionado no es válido.',
            'notasEstado.required' => 'Debes escribir una observación.',
        ]);

        $datosActualizar = [
            'estado' => $this->nuevoEstado,

            'usuarioAsignado' =>
            Auth::user()?->name
                ?? Auth::user()?->email
                ?? null,
        ];

        if ($this->nuevoEstado === 'RECIBIDO') {
            $datosActualizar['notas_pendiente'] = null;
            $datosActualizar['notas_rechazo'] = null;
        }

        if ($this->nuevoEstado === 'EN_REVISION') {
            $datosActualizar['fecha_revision'] = now();
            $datosActualizar['notas_pendiente'] = $this->notasEstado;
        }

        if ($this->nuevoEstado === 'APROBADO') {
            $datosActualizar['fecha_revision'] =
                $this->recibo->fecha_revision ?? now();

            $datosActualizar['fecha_aprobacion'] = now();
            $datosActualizar['notas_rechazo'] = null;
            $datosActualizar['notas_pendiente'] = null;
        }

        if ($this->nuevoEstado === 'RECHAZADO') {
            $datosActualizar['fecha_revision'] =
                $this->recibo->fecha_revision ?? now();

            $datosActualizar['notas_rechazo'] = $this->notasEstado;
        }

        if ($this->nuevoEstado === 'EXPORTADO') {
            $datosActualizar['fecha_exportacion'] = now();
        }

        $this->recibo->update($datosActualizar);

        $this->recibo->refresh()->load([
            'bancoRecaudo',
            'caja',
            'cxcs',
            'ingresos',
            'retenciones',
        ]);

        $this->cerrarModalEstado();

        session()->flash(
            'success',
            'El estado del recibo fue actualizado correctamente.'
        );
    }

    public function descargarComprobante()
    {
        if (
            !$this->recibo->ubicacion ||
            !Storage::disk('public')->exists($this->recibo->ubicacion)
        ) {
            session()->flash(
                'error',
                'El archivo comprobante no fue encontrado.'
            );

            return null;
        }

        return Storage::disk('public')->download(
            $this->recibo->ubicacion,
            $this->recibo->adjunto_nombre_archivo
                ?: basename($this->recibo->ubicacion)
        );
    }

    public function exportarExcel()
    {
        return Excel::download(
            new RecibosExport(
                estado: $this->recibo->estado,
                reciboIds: [$this->recibo->id],
            ),
            'PlanoRC_' . $this->recibo->codigo_recibo . '.xlsx'
        );
    }


    public function buscarComprobanteBancolombia(): void
{
    $this->movimientoBancario = null;
    $this->busquedaComprobanteRealizada = false;
    $this->comprobanteValido = false;
    $this->mensajeValidacionComprobante = '';
    $this->cantidadMovimientosCoincidentes = 0;

    /*
    |--------------------------------------------------------------------------
    | 1. Validar que el recibo sea de Bancolombia
    |--------------------------------------------------------------------------
    */

    $nombreBanco = strtoupper(
        trim(
            (string) $this->recibo
                ->bancoRecaudo
                ?->descripcion_banco
        )
    );

    $this->esBancolombia = str_contains(
        $nombreBanco,
        'BANCOLOMBIA'
    );

    if (!$this->esBancolombia) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Revisar si el recibo ya tiene un movimiento relacionado
    |--------------------------------------------------------------------------
    */

    $movimientoRelacionado = MovimientoBancario::query()
        ->where('recibo_encabezado_id', $this->recibo->id)
        ->first();

    if ($movimientoRelacionado) {
        $this->movimientoBancario = $movimientoRelacionado;
        $this->busquedaComprobanteRealizada = true;
        $this->comprobanteValido = true;
        $this->cantidadMovimientosCoincidentes = 1;

        $this->totalAplicadoRecibo = round(
            (float) $this->recibo->total_recibido,
            2
        );

        $this->mensajeValidacionComprobante =
            'Este movimiento ya se encuentra aplicado al recibo.';

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Datos del recibo
    |--------------------------------------------------------------------------
    */

    $numeroComprobante = trim(
        (string) $this->recibo->numero_soporte
    );

    $nitCliente = trim(
        (string) $this->recibo->nit_cliente
    );

    $digitoVerificacion = trim(
        (string) $this->recibo->digito_verificacion
    );

    /*
    |--------------------------------------------------------------------------
    | Generar NIT con dígito de verificación
    |
    | Ejemplo:
    | NIT: 45678
    | DV:  1
    | Resultado: 456781
    |--------------------------------------------------------------------------
    */

    $nitClienteConDv = $nitCliente;

    if ($nitCliente !== '' && $digitoVerificacion !== '') {
        $nitClienteConDv =
            $nitCliente . $digitoVerificacion;
    }

    $fechaComprobante = $this->recibo
        ->fecha_comprobante
        ?->format('Y-m-d');

    /*
    |--------------------------------------------------------------------------
    | Se conserva exactamente tu validación original del valor
    |--------------------------------------------------------------------------
    */

    $valorRecibo = round(
        (float) $this->recibo->total_recibido,
        2
    );

    $valorComparacion = number_format(
        $valorRecibo,
        2,
        '.',
        ''
    );

    $this->totalAplicadoRecibo = $valorRecibo;

    /*
    |--------------------------------------------------------------------------
    | 4. Validaciones básicas
    |--------------------------------------------------------------------------
    */

    if ($numeroComprobante === '' && $nitCliente === '') {
        $this->busquedaComprobanteRealizada = true;

        $this->mensajeValidacionComprobante =
            'El recibo no tiene número de soporte ni NIT del cliente.';

        return;
    }

    if (!$fechaComprobante) {
        $this->busquedaComprobanteRealizada = true;

        $this->mensajeValidacionComprobante =
            'El recibo no tiene fecha de comprobante.';

        return;
    }

    if ($valorRecibo <= 0) {
        $this->busquedaComprobanteRealizada = true;

        $this->mensajeValidacionComprobante =
            'El recibo no tiene un valor recibido válido.';

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Buscar todos los movimientos coincidentes
    |--------------------------------------------------------------------------
    */

    $movimientosCoincidentes = MovimientoBancario::query()
        ->whereNull('recibo_encabezado_id')
        ->where('procesado', false)
        ->where(function ($query) {
            $query
                ->whereNull('estado')
                ->orWhere('estado', 'DISPONIBLE');
        })
        ->whereDate(
            'fecha_movimiento',
            $fechaComprobante
        )
        ->whereRaw(
            'ROUND(ABS(valor), 2) = ?',
            [$valorComparacion]
        )
        ->where(function ($query) use (
            $numeroComprobante,
            $nitCliente,
            $nitClienteConDv
        ) {
            $primeraCondicion = true;

            /*
            |--------------------------------------------------------------------------
            | Buscar por número de soporte
            |--------------------------------------------------------------------------
            */

            if ($numeroComprobante !== '') {
                $query->where(function ($subQuery) use (
                    $numeroComprobante
                ) {
                    $subQuery
                        ->whereRaw(
                            'TRIM(referencia_1) = ?',
                            [$numeroComprobante]
                        )
                        ->orWhereRaw(
                            'TRIM(referencia_2) = ?',
                            [$numeroComprobante]
                        )
                        ->orWhereRaw(
                            'TRIM(referencia_3) = ?',
                            [$numeroComprobante]
                        );
                });

                $primeraCondicion = false;
            }

            /*
            |--------------------------------------------------------------------------
            | Buscar por NIT normal o NIT + dígito de verificación
            |--------------------------------------------------------------------------
            */

            if ($nitCliente !== '') {
                $metodo = $primeraCondicion
                    ? 'where'
                    : 'orWhere';

                $query->{$metodo}(function ($subQuery) use (
                    $nitCliente,
                    $nitClienteConDv
                ) {
                    $subQuery
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

                    /*
                    | Agregar NIT + DV solamente cuando sea diferente
                    */

                    if ($nitClienteConDv !== $nitCliente) {
                        $subQuery
                            ->orWhereRaw(
                                'TRIM(referencia_1) = ?',
                                [$nitClienteConDv]
                            )
                            ->orWhereRaw(
                                'TRIM(referencia_2) = ?',
                                [$nitClienteConDv]
                            )
                            ->orWhereRaw(
                                'TRIM(referencia_3) = ?',
                                [$nitClienteConDv]
                            );
                    }
                });
            }
        })
        ->orderBy('fecha_movimiento')
        ->orderBy('id')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | 6. Evaluar cantidad de coincidencias
    |--------------------------------------------------------------------------
    */

    $this->busquedaComprobanteRealizada = true;

    $this->cantidadMovimientosCoincidentes =
        $movimientosCoincidentes->count();

    if ($this->cantidadMovimientosCoincidentes === 0) {
        $this->mensajeValidacionComprobante =
            'No se encontró un movimiento disponible que coincida en fecha, '
            . 'valor y número de soporte, NIT o NIT con dígito de verificación.';

        return;
    }

    if ($this->cantidadMovimientosCoincidentes > 1) {
        $this->mensajeValidacionComprobante =
            'Se encontraron '
            . $this->cantidadMovimientosCoincidentes
            . ' movimientos que cumplen las condiciones. '
            . 'No se aplicó ninguno automáticamente para evitar relacionar '
            . 'el movimiento equivocado.';

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | 7. Existe una única coincidencia: aplicar al recibo
    |--------------------------------------------------------------------------
    */

    $this->movimientoBancario =
        $movimientosCoincidentes->first();

    $this->movimientoBancario->update([
        'recibo_encabezado_id' => $this->recibo->id,
        'estado' => 'APLICADO',
        'procesado' => true,
        'fecha_aplicacion' => now(),
    ]);

    $this->movimientoBancario->refresh();

    $this->comprobanteValido = true;

    $this->mensajeValidacionComprobante =
        'Se encontró una única coincidencia. '
        . 'El movimiento fue validado y aplicado correctamente al recibo.';
}

    public function render()
    {
        return view('livewire.admin.recibos-caja.detalle');
    }
}
