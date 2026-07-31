<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BancoRecaudo;
use App\Models\ReciboCaja;
use App\Models\ReciboCajaCxc;
use App\Models\ReciboCajaIngreso;
use App\Models\RecibosEncabezado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class RecaudoController extends Controller
{
    /**
     * Consultar cartera del cliente en UnoEE.
     */
    public function consultar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nit' => [
                'required',
                'string',
                'max:50',
            ],
            'vendedor' => [
                'required',
                'string',
                'max:50',
            ],
        ], [
            'nit.required' => 'El NIT del cliente es obligatorio.',
            'vendedor.required' => 'El código del vendedor es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Se encontraron errores de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $facturas = DB::connection('sqlsrv')->select("
                SELECT
                    t200.f200_rowid AS rowid,
                    t200.f200_nit AS nit,
                    t200.f200_razon_social AS razon_social,

                    t353.f353_id_tipo_docto_cruce AS factura,
                    t353.f353_consec_docto_cruce AS cons_factura,

                    t353.f353_total_db AS valor_factura,
                    t353.f353_valor_base AS valor_base,
                    t353.f353_total_cr AS valor_abonos,

                    (
                        t353.f353_total_db - t353.f353_total_cr
                    ) AS saldo,

                    t353.f353_fecha_docto_cruce AS fecha_factura,
                    GETDATE() AS fecha_hoy,

                    DATEDIFF(
                        DAY,
                        t353.f353_fecha_docto_cruce,
                        GETDATE()
                    ) AS dias_vcto,

                    t353.f353_id_cond_pago AS cond_pag,
                    t201.f201_id_sucursal AS suc_cliente,
                    t353.f353_valor_impuesto AS iva,

                    CASE
                        WHEN (
                            SELECT TOP 1
                                f047_id_valor_tercero
                            FROM UnoEE.dbo.t047_mm_cliente_base_retencion
                            WHERE f047_rowid_tercero = t200.f200_rowid
                                AND f047_id_clase_retencion = 1
                                AND f047_id_cia = 3
                                AND f047_id_sucursal = t201.f201_id_sucursal
                        ) = 1
                        THEN 1
                        ELSE 0
                    END AS esRetenedor,

                    CASE
                        WHEN (
                            SELECT TOP 1
                                f047_id_valor_tercero
                            FROM UnoEE.dbo.t047_mm_cliente_base_retencion
                            WHERE f047_rowid_tercero = t200.f200_rowid
                                AND f047_id_clase_retencion = 2
                                AND f047_id_cia = 3
                                AND f047_id_sucursal = t201.f201_id_sucursal
                        ) = 1
                        THEN 1
                        ELSE 0
                    END AS esReteIva,

                    CASE
                        WHEN (
                            SELECT TOP 1
                                f047_id_valor_tercero
                            FROM UnoEE.dbo.t047_mm_cliente_base_retencion
                            WHERE f047_rowid_tercero = t200.f200_rowid
                                AND f047_id_clase_retencion = 2
                                AND f047_id_cia = 3
                                AND f047_id_sucursal = t201.f201_id_sucursal
                        ) = 1
                        THEN (
                            (t353.f353_valor_impuesto / 100) * 15
                        )
                        ELSE 0
                    END AS rete_iva,

                    (
                        SELECT TOP 1
                            f210_id
                        FROM UnoEE.dbo.t210_mm_vendedores
                        WHERE f210_rowid_tercero = t353.f353_rowid_vend
                            AND f210_id_cia = 3
                    ) AS vendedor

                FROM UnoEE.dbo.t353_co_saldo_abierto AS t353

                INNER JOIN UnoEE.dbo.t201_mm_clientes AS t201
                    ON t201.f201_rowid_tercero = t353.f353_rowid_tercero
                    AND t201.f201_id_sucursal = t353.f353_id_sucursal
                    AND t201.f201_id_cia = t353.f353_id_cia

                INNER JOIN UnoEE.dbo.t200_mm_terceros AS t200
                    ON t200.f200_rowid = t201.f201_rowid_tercero

                INNER JOIN UnoEE.dbo.t253_co_auxiliares AS t253
                    ON t253.f253_rowid = t353.f353_rowid_auxiliar
                    AND t253.f253_ind_sa = 1

                WHERE t353.f353_id_cia = 3
                    AND t353.f353_fecha_cancelacion IS NULL
                    AND t353.f353_id_tipo_docto_cruce = 'FVM'
                    AND t200.f200_nit = ?

                    AND (
                        SELECT TOP 1
                            f210_id
                        FROM UnoEE.dbo.t210_mm_vendedores
                        WHERE f210_rowid_tercero = t353.f353_rowid_vend
                            AND f210_id_cia = 3
                    ) = ?

                ORDER BY
                    t353.f353_fecha_docto_cruce ASC,
                    t353.f353_consec_docto_cruce ASC
            ", [
                $request->input('nit'),
                $request->input('vendedor'),
            ]);

            return response()->json([
                'success' => true,
                'data' => $facturas,
            ]);
        } catch (Throwable $e) {
            Log::error('Error consultando cartera para recaudo', [
                'message' => $e->getMessage(),
                'nit' => $request->input('nit'),
                'vendedor' => $request->input('vendedor'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible consultar la cartera del cliente.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * Listar bancos activos para recaudo.
     */
    public function bancos(): JsonResponse
    {
        try {
            $bancos = BancoRecaudo::query()
                ->where('estado', true)
                ->orderBy('descripcion_banco')
                ->get([
                    'id',
                    'id_banco',
                    'descripcion_banco',
                    'id_cuenta',
                    'descripcion_cuenta',
                    'numero_cuenta',
                    'id_medio_pago',
                    'tipo_cuenta',
                ]);

            return response()->json([
                'success' => true,
                'data' => $bancos,
            ]);
        } catch (Throwable $e) {
            Log::error('Error consultando bancos de recaudo', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible consultar los bancos de recaudo.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * Guardar un recaudo.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nit' => [
                'required',
                'string',
                'max:50',
            ],
            'vendedor' => [
                'required',
                'string',
                'max:50',
            ],
            'banco' => [
                'required',
                'string',
            ],
            'fecha_comprobante' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'valor_comprobante' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'correo_cliente' => [
                'required',
                'email',
                'max:255',
            ],
            'total_aplicado' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'valor_pendiente_aplicar' => [
                'required',
                'numeric',
                'min:0',
            ],
            'facturas' => [
                'required',
                'string',
            ],
            'comprobante' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:10240',
            ],
            'numero_soporte' => [
                'nullable',
                'string',
                'max:255',
            ],
            'notas' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'nit.required' => 'El NIT del cliente es obligatorio.',
            'vendedor.required' => 'El código del vendedor es obligatorio.',
            'banco.required' => 'Debes seleccionar un banco.',
            'fecha_comprobante.required' => 'La fecha del comprobante es obligatoria.',
            'fecha_comprobante.date' => 'La fecha del comprobante no es válida.',
            'fecha_comprobante.before_or_equal' => 'La fecha del comprobante no puede ser mayor a hoy.',
            'valor_comprobante.required' => 'El valor del comprobante es obligatorio.',
            'valor_comprobante.numeric' => 'El valor del comprobante debe ser numérico.',
            'valor_comprobante.gt' => 'El valor del comprobante debe ser mayor que cero.',
            'correo_cliente.required' => 'El correo del cliente es obligatorio.',
            'correo_cliente.email' => 'El correo del cliente no tiene un formato válido.',
            'total_aplicado.required' => 'El total aplicado es obligatorio.',
            'total_aplicado.gt' => 'Debes aplicar un valor al menos a una factura.',
            'facturas.required' => 'Debes enviar las facturas aplicadas.',
            'comprobante.required' => 'Debes adjuntar el comprobante.',
            'comprobante.mimes' => 'El comprobante debe ser PDF, JPG, JPEG, PNG o WEBP.',
            'comprobante.max' => 'El comprobante no puede superar los 10 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Se encontraron errores de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Decodificar banco
        |--------------------------------------------------------------------------
        */

        $bancoFront = json_decode(
            $request->input('banco'),
            true
        );

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($bancoFront) ||
            empty($bancoFront['id'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'La información del banco no tiene un formato válido.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Decodificar facturas
        |--------------------------------------------------------------------------
        */

        $facturasFront = json_decode(
            $request->input('facturas'),
            true
        );

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($facturasFront)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'La información de las facturas no tiene un formato válido.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Consultar el banco real
        |--------------------------------------------------------------------------
        */

        $banco = BancoRecaudo::query()
            ->whereKey($bancoFront['id'])
            ->where('estado', true)
            ->first();

        if (!$banco) {
            return response()->json([
                'success' => false,
                'message' => 'El banco seleccionado no existe o está inactivo.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Filtrar facturas con valor aplicado
        |--------------------------------------------------------------------------
        */

        $facturasAplicadas = collect($facturasFront)
            ->filter(function (array $factura): bool {
                return round(
                    (float) ($factura['valor_aplicar'] ?? 0),
                    2
                ) > 0;
            })
            ->values();

        if ($facturasAplicadas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes aplicar un valor al menos a una factura.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar facturas recibidas
        |--------------------------------------------------------------------------
        */

        $erroresFacturas = [];

        foreach ($facturasAplicadas as $indice => $factura) {
            $tipoDocumento = trim(
                (string) ($factura['factura'] ?? '')
            );

            $numeroFactura = trim(
                (string) ($factura['cons_factura'] ?? '')
            );

            $nitFactura = trim(
                (string) ($factura['nit'] ?? '')
            );

            $vendedorFactura = trim(
                (string) ($factura['vendedor'] ?? '')
            );

            $saldo = round(
                (float) ($factura['saldo'] ?? 0),
                2
            );

            $valorAplicar = round(
                (float) ($factura['valor_aplicar'] ?? 0),
                2
            );

            if ($tipoDocumento === '') {
                $erroresFacturas["facturas.{$indice}.factura"][] =
                    "La factura {$numeroFactura} no tiene tipo de documento.";
            }

            if ($numeroFactura === '') {
                $erroresFacturas["facturas.{$indice}.cons_factura"][] =
                    'Una de las facturas no tiene consecutivo.';
            }

            if ($saldo <= 0) {
                $erroresFacturas["facturas.{$indice}.saldo"][] =
                    "La factura {$numeroFactura} no tiene saldo pendiente.";
            }

            if ($valorAplicar <= 0) {
                $erroresFacturas["facturas.{$indice}.valor_aplicar"][] =
                    "El valor aplicado a la factura {$numeroFactura} debe ser mayor que cero.";
            }

            if ($valorAplicar > $saldo) {
                $erroresFacturas["facturas.{$indice}.valor_aplicar"][] =
                    "El valor aplicado a la factura {$numeroFactura} supera su saldo.";
            }

            if (
                $nitFactura !== '' &&
                $nitFactura !== trim((string) $request->input('nit'))
            ) {
                $erroresFacturas["facturas.{$indice}.nit"][] =
                    "La factura {$numeroFactura} no pertenece al cliente enviado.";
            }

            if (
                $vendedorFactura !== '' &&
                $vendedorFactura !== trim((string) $request->input('vendedor'))
            ) {
                $erroresFacturas["facturas.{$indice}.vendedor"][] =
                    "La factura {$numeroFactura} no pertenece al vendedor enviado.";
            }
        }

        if (!empty($erroresFacturas)) {
            return response()->json([
                'success' => false,
                'message' => 'Algunas facturas contienen valores no válidos.',
                'errors' => $erroresFacturas,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Recalcular totales en backend
        |--------------------------------------------------------------------------
        */

        $valorComprobante = round(
            (float) $request->input('valor_comprobante'),
            2
        );

        $totalAplicadoCalculado = round(
            $facturasAplicadas->sum(function (array $factura): float {
                return round(
                    (float) ($factura['valor_aplicar'] ?? 0),
                    2
                );
            }),
            2
        );

        $totalRestanteCalculado = round(
            $valorComprobante - $totalAplicadoCalculado,
            2
        );

        if ($totalAplicadoCalculado > $valorComprobante) {
            return response()->json([
                'success' => false,
                'message' => 'El total aplicado no puede superar el valor del comprobante.',
                'data' => [
                    'valor_comprobante' => $valorComprobante,
                    'total_aplicado' => $totalAplicadoCalculado,
                    'valor_excedido' => abs($totalRestanteCalculado),
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar que los totales del front coincidan
        |--------------------------------------------------------------------------
        */

        $totalAplicadoFront = round(
            (float) $request->input('total_aplicado'),
            2
        );

        $totalRestanteFront = round(
            (float) $request->input('valor_pendiente_aplicar'),
            2
        );

        if (abs($totalAplicadoFront - $totalAplicadoCalculado) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'El total aplicado enviado no coincide con las facturas.',
                'data' => [
                    'total_aplicado_enviado' => $totalAplicadoFront,
                    'total_aplicado_calculado' => $totalAplicadoCalculado,
                ],
            ], 422);
        }

        if (abs($totalRestanteFront - $totalRestanteCalculado) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'El valor pendiente enviado no coincide con el valor calculado.',
                'data' => [
                    'valor_pendiente_enviado' => $totalRestanteFront,
                    'valor_pendiente_calculado' => $totalRestanteCalculado,
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Guardar recaudo
        |--------------------------------------------------------------------------
        */

        $rutaComprobante = null;

        DB::beginTransaction();

        try {
            $archivo = $request->file('comprobante');

            $nombreOriginal = $archivo->getClientOriginalName();

            $rutaComprobante = $archivo->store(
                'recibos-caja/comprobantes',
                'public'
            );

            if (!$rutaComprobante) {
                throw new \RuntimeException(
                    'No fue posible almacenar el archivo del comprobante.'
                );
            }

            $primeraFactura = $facturasAplicadas->first();
            $usuario = $request->user();

            /*
            |--------------------------------------------------------------------------
            | Encabezado
            |--------------------------------------------------------------------------
            */

            $encabezado = RecibosEncabezado::create([
                'codigo_docto' => 'RCM',
                'fecha_recibo' => now(),

                'id_vendedor' => $request->input('vendedor'),

                'nombre_asesor' =>
                    $usuario?->nombre_asesor
                    ?? $usuario?->name
                    ?? null,

                'email_asesor' =>
                    $usuario?->email_asesor
                    ?? $usuario?->email
                    ?? null,

                'codigo_recibo' => null,

                'razon_social' =>
                    $primeraFactura['razon_social']
                    ?? null,

                'nit_cliente' => $request->input('nit'),
                'email_cliente' => $request->input('correo_cliente'),

                'numero_soporte' => $request->input('numero_soporte'),
                'fecha_comprobante' => $request->input('fecha_comprobante'),

                'total_recibido' => $valorComprobante,
                'total_restante' => $totalRestanteCalculado,

                'banco_recaudo_id' => $banco->id,

                'notas' => $request->input('notas'),
                'notas_rechazo' => null,
                'notas_pendiente' => null,

                'retencion' => 0,
                'reteIva' => 0,

                'estado' => 'RECIBIDO',

                'id_recibo_efectivo' => null,
                'valor_recibo_efectivo' => 0,

                'usuarioAsignado' => null,

                'fecha_revision' => null,
                'fecha_aprobacion' => null,
                'fecha_exportacion' => null,
                'fecha_cliente_creado' => null,

                'adjunto_nombre_archivo' => $nombreOriginal,
                'ubicacion' => $rutaComprobante,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Código interno
            |--------------------------------------------------------------------------
            */

            $codigoRecibo = 'RCM-' . str_pad(
                (string) $encabezado->id,
                8,
                '0',
                STR_PAD_LEFT
            );

            $encabezado->update([
                'codigo_recibo' => $codigoRecibo,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Caja
            |--------------------------------------------------------------------------
            */

            ReciboCaja::create([
                'recibo_encabezado_id' => $encabezado->id,

                'F350_ID_TIPO_DOCTO' => 'RCM',
                'F350_CONSEC_DOCTO' => $encabezado->id,

                'F358_ID_MEDIOS_PAGO' => $banco->id_medio_pago,
                'F358_VALOR' => $valorComprobante,

                'F358_REFERENCIA_OTROS' =>
                    $request->input('numero_soporte')
                    ?: $codigoRecibo,

                'F358_FECHA_CONSIGNACION' =>
                    $request->input('fecha_comprobante'),

                'f358_docto_banco_cg' => $banco->id_cuenta,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Ingreso
            |--------------------------------------------------------------------------
            */

            ReciboCajaIngreso::create([
                'recibo_encabezado_id' => $encabezado->id,

                /*
                 * Ajustar si F350_ID_CO tiene un valor fijo distinto.
                 */
                'F350_ID_CO' => $banco->id_banco,

                'TIPO_DOCTO' => 'RCM',
                'F350_CONSEC_DOCTO' => $encabezado->id,
                'F350_FECHA' => now()->toDateString(),

                'F357_ID_CAJA' => $banco->id_cuenta,

                'F357_FECHA_RECAUDO' =>
                    $request->input('fecha_comprobante'),

                'F350_ID_TERCERO' => $request->input('nit'),
                'F357_VALOR_INGRESO' => $valorComprobante,

                'F357_ID_COBRADORCOD' =>
                    $request->input('vendedor'),

                'F357_ID_UN' => null,
                'F357_ID_FE' => null,

                'F350_NOTAS' =>
                    $request->input('notas')
                    ?: "Recaudo {$codigoRecibo}",

                'F351_ID_AUXILIAR_AJUSTE' => null,
                'F351_ID_AUXILIAR_PP' => null,
                'F351_ID_CCOSTO_PP' => null,

                'F351_ID_AUXILIAR_OTRO_ING' => null,
                'F351_ID_TERCERO_OTRO_ING' => null,
                'F351_ID_SUCURSAL_OTRO_ING' => null,
                'F351_ID_CO_OTRO_ING' => null,
                'F351_ID_UN_OTRO_ING' => null,

                'F357_REFERENCIA' =>
                    $request->input('numero_soporte')
                    ?: $codigoRecibo,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Facturas aplicadas
            |--------------------------------------------------------------------------
            */

            foreach ($facturasAplicadas as $factura) {
                $valorAplicar = round(
                    (float) $factura['valor_aplicar'],
                    2
                );

                ReciboCajaCxc::create([
                    'recibo_encabezado_id' => $encabezado->id,

                    'F350_ID_TIPO_DOCTO' => 'RCM',
                    'F350_CONSEC_DOCTO' => $encabezado->id,

                    'F353_ID_AUXILIAR_DOCTO_CRUCE' =>
                        $factura['id_auxiliar']
                        ?? null,

                    'F353_ID_SUCURSAL_DOCTO_CRUCE' =>
                        $factura['suc_cliente']
                        ?? null,

                    'F353_ID_TIPO_DOCTO_CRUCE' =>
                        $factura['factura'],

                    'F353_CONSEC_DOCTO_CRUCE' =>
                        (string) $factura['cons_factura'],

                    'F354_VALOR_CR' => $valorAplicar,
                    'F354_VALOR_APLICADO_PP' => 0,
                    'F354_VALOR_APROVECHA' => 0,
                    'F354_VALOR_RETENCION' => 0,
                ]);
            }

            DB::commit();

            $encabezado->load([
                'bancoRecaudo',
                'caja',
                'ingresos',
                'cxcs',
                'retenciones',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'El recaudo fue registrado correctamente.',
                'data' => [
                    'id' => $encabezado->id,
                    'codigo_recibo' => $encabezado->codigo_recibo,
                    'estado' => $encabezado->estado,

                    'valor_comprobante' =>
                        (float) $encabezado->total_recibido,

                    'total_aplicado' =>
                        $totalAplicadoCalculado,

                    'valor_pendiente_aplicar' =>
                        $totalRestanteCalculado,

                    'cantidad_facturas' =>
                        $facturasAplicadas->count(),

                    'recibo' => $encabezado,
                ],
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();

            if ($rutaComprobante) {
                Storage::disk('public')->delete(
                    $rutaComprobante
                );
            }

            Log::error('Error guardando recaudo', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'nit' => $request->input('nit'),
                'vendedor' => $request->input('vendedor'),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible registrar el recaudo.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * Consultar un recaudo guardado.
     */
    public function show(
        RecibosEncabezado $recibo
    ): JsonResponse {
        $recibo->load([
            'bancoRecaudo',
            'caja',
            'ingresos',
            'cxcs',
            'retenciones',
        ]);

        return response()->json([
            'success' => true,
            'data' => $recibo,
        ]);
    }
}