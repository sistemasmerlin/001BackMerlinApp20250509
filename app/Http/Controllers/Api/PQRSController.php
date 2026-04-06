<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PqrsCausal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StorePqrsRequest;
use App\Models\FleteCiudad;
use App\Models\Pqrs;
use App\Models\Orm;
use App\Models\PqrsProducto;
use App\Models\PqrsProductoAdjunto;
use App\Models\PqrsAdjunto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PQRSController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // AJUSTA ESTE CAMPO SEGÚN TU TABLA users
        $codAsesor = trim((string) ($user->codigo_asesor ?? ''));

        if ($codAsesor === '') {
            return response()->json([
                'ok' => false,
                'message' => 'El usuario autenticado no tiene código de asesor configurado.',
            ], 422);
        }

        $q = trim((string) $request->query('q', ''));
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
        $limit = (int) $request->query('limit', 50);

        if ($limit <= 0 || $limit > 100) {
            $limit = 50;
        }

        $rows = Pqrs::query()
            ->with('orm:id,pqrs_id,estado')
            ->select([
                'id',
                'tipo_pqrs',
                'nit',
                'razon_social',
                'telefono',
                'correo_cliente',
                'direccion',
                'ciudad',
                'departamento',
                'cod_asesor',
                'nombre_asesor',
                'correo_asesor',
                'estado',
                'fecha_creacion',
                'fecha_revisado',
                'fecha_cierre',
                'created_at',
            ])
            ->where('cod_asesor', $codAsesor)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('nit', 'like', "%{$q}%")
                        ->orWhere('razon_social', 'like', "%{$q}%");
                });
            })
            ->when($fechaInicio, function ($query) use ($fechaInicio) {
                $query->whereDate('fecha_creacion', '>=', $fechaInicio);
            })
            ->when($fechaFin, function ($query) use ($fechaFin) {
                $query->whereDate('fecha_creacion', '<=', $fechaFin);
            })
            ->orderByDesc('fecha_creacion')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function ($pqrs) {
                return [
                    'id' => $pqrs->id,
                    'tipo_pqrs' => $pqrs->tipo_pqrs,
                    'nit' => $pqrs->nit,
                    'razon_social' => $pqrs->razon_social,
                    'telefono' => $pqrs->telefono,
                    'correo_cliente' => $pqrs->correo_cliente,
                    'direccion' => $pqrs->direccion,
                    'ciudad' => $pqrs->ciudad,
                    'departamento' => $pqrs->departamento,
                    'cod_asesor' => $pqrs->cod_asesor,
                    'nombre_asesor' => $pqrs->nombre_asesor,
                    'correo_asesor' => $pqrs->correo_asesor,
                    'estado' => $pqrs->estado,
                    'fecha_creacion' => optional($pqrs->fecha_creacion)->format('Y-m-d H:i'),
                    'fecha_revisado' => optional($pqrs->fecha_revisado)->format('Y-m-d H:i'),
                    'fecha_cierre' => optional($pqrs->fecha_cierre)->format('Y-m-d H:i'),
                    'created_at' => optional($pqrs->created_at)->format('Y-m-d H:i'),
                    'tiene_orm' => (bool) $pqrs->orm,
                    'orm_id' => $pqrs->orm?->id,
                    'orm_estado' => $pqrs->orm?->estado,
                ];
            });

        return response()->json([
            'ok' => true,
            'cod_asesor' => $codAsesor,
            'data' => $rows,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        // AJUSTA ESTE CAMPO SEGÚN TU TABLA users
        $codAsesor = trim((string) ($user->codigo_asesor ?? ''));

        if ($codAsesor === '') {
            return response()->json([
                'ok' => false,
                'message' => 'El usuario autenticado no tiene código de asesor configurado.',
            ], 422);
        }

        $pqrs = Pqrs::with([
            'orm.transportadora',
            'orm.usuarioRecibe',
            'orm.usuarioMarcaRecogidaTransportadora',
            'productos.causal',
            'productos.adjuntos',
            'adjuntos',
        ])
        ->where('id', $id)
        ->where('cod_asesor', $codAsesor)
        ->first();

        if (!$pqrs) {
            return response()->json([
                'ok' => false,
                'message' => 'PQRS no encontrada o no pertenece al asesor autenticado.',
            ], 404);
        }

        $facturas = $pqrs->productos
            ->map(function ($p) {
                $tipo = trim((string) ($p->tipo_docto ?? ''));
                $numero = trim((string) ($p->nro_docto ?? ''));
                $fecha = $p->fecha;

                return [
                    'key' => $tipo . '|' . $numero,
                    'tipo_docto' => $tipo,
                    'nro_docto' => $numero,
                    'fecha' => $fecha ? \Carbon\Carbon::parse($fecha)->format('Y-m-d') : null,
                ];
            })
            ->filter(fn($f) => $f['tipo_docto'] !== '' || $f['nro_docto'] !== '')
            ->unique('key')
            ->values()
            ->toArray();

        $productos = $pqrs->productos->map(function ($p) {
            return [
                'id' => $p->id,
                'referencia' => $p->referencia,
                'descripcion_ref' => $p->descripcion_ref,
                'unidades_solicitadas' => (float) $p->unidades_solicitadas,
                'precio_unitario' => (float) $p->precio_unitario,
                'valor_bruto' => (float) $p->valor_bruto,
                'valor_imp' => (float) $p->valor_imp,
                'valor_neto' => (float) $p->valor_neto,
                'tipo_docto' => $p->tipo_docto,
                'nro_docto' => $p->nro_docto,
                'estado' => $p->estado,
                'estado_orm' => $p->estado_orm,
                'requiere_recogida' => (int) $p->requiere_recogida,
                'notas' => $p->notas,
                'causal' => [
                    'id' => $p->causal?->id,
                    'nombre' => $p->causal?->nombre,
                ],
                'adjuntos' => $p->adjuntos->map(function ($adj) {
                    return [
                        'id' => $adj->id,
                        'original_name' => $adj->original_name,
                        'mime' => $adj->mime,
                        'path' => $adj->path,
                        'url' => asset('storage/' . $adj->path),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'cod_asesor' => $codAsesor,
            'data' => [
                'id' => $pqrs->id,
                'tipo_pqrs' => $pqrs->tipo_pqrs,
                'estado' => $pqrs->estado,
                'nit' => $pqrs->nit,
                'razon_social' => $pqrs->razon_social,
                'telefono' => $pqrs->telefono,
                'correo_cliente' => $pqrs->correo_cliente,
                'direccion' => $pqrs->direccion,
                'ciudad' => $pqrs->ciudad,
                'departamento' => $pqrs->departamento,
                'nombre_asesor' => $pqrs->nombre_asesor,
                'cod_asesor' => $pqrs->cod_asesor,
                'correo_asesor' => $pqrs->correo_asesor,
                'fecha_creacion' => optional($pqrs->created_at)->format('Y-m-d H:i'),
                'fecha_revisado' => optional($pqrs->fecha_revisado)->format('Y-m-d H:i'),
                'fecha_cierre' => optional($pqrs->fecha_cierre)->format('Y-m-d H:i'),
                'tipo_acuerdo' => $pqrs->tipo_acuerdo,
                'nota_acuerdo' => $pqrs->nota_acuerdo,
                'valor_acuerdo' => $pqrs->valor_acuerdo,
                'comentario_cierre' => $pqrs->comentario_cierre,
                'facturas' => $facturas,
                'productos' => $productos,
                'orm' => $pqrs->orm ? [
                    'id' => $pqrs->orm->id,
                    'estado' => $pqrs->orm->estado,
                    'nit' => $pqrs->orm->nit,
                    'razon_social' => $pqrs->orm->razon_social,
                    'direccion' => $pqrs->orm->direccion,
                    'departamento' => $pqrs->orm->departamento,
                    'ciudad' => $pqrs->orm->ciudad,
                    'telefono' => $pqrs->orm->telefono,
                    'transportadora' => [
                        'id' => $pqrs->orm->transportadora?->id,
                        'razon_social' => $pqrs->orm->transportadora?->razon_social,
                    ],
                    'lios' => $pqrs->orm->lios,
                    'cajas' => $pqrs->orm->cajas,
                    'peso' => $pqrs->orm->peso,
                    'valor_declarado' => $pqrs->orm->valor_declarado,
                    'numero_guia' => $pqrs->orm->numero_guia,
                    'comentarios' => $pqrs->orm->comentarios,
                    'fecha_recogida_programada' => optional($pqrs->orm->fecha_recogida_programada)->format('Y-m-d H:i'),
                    'fecha_recogida_transportadora' => optional($pqrs->orm->fecha_recogida_transportadora)->format('Y-m-d H:i'),
                    'fecha_llegada_bodega' => optional($pqrs->orm->fecha_llegada_bodega)->format('Y-m-d H:i'),
                    'usuario_marca_recogida_transportadora' => $pqrs->orm->usuarioMarcaRecogidaTransportadora?->name,
                    'usuario_recibe' => $pqrs->orm->usuarioRecibe?->name,
                ] : null,
            ],
        ]);
    }
    public function consultaProductos(Request $request, $query)
    {
        $query = trim((string) $query);

        $nit = $request->get('nit');
        $suc = $request->get('sucursal');

        if (!$nit) $nit = '900447351';
        if (!$suc) $suc = '020';

        $result = DB::connection('sqlsrv')->select("
            SELECT
                f_cliente_fact,
                f_cliente_fact_suc,
                f_id_tipo_docto,
                f_nrodocto,
                f_fecha,
                f_ref_item,
                t120.f120_descripcion as f_descripcion,
                f_cant_inv,
                f_precio_venta,
                f_valor_bruto_local,
                f_valor_imp_local,
                f_valor_neto_local
            FROM BI_T461_1
            LEFT JOIN t120_mc_items AS t120
                ON t120.f120_referencia = f_ref_item
                AND t120.f120_id_cia = 3
            WHERE
                f_cliente_fact = ?
                AND f_cliente_fact_suc = ?
                AND f_parametro_biable = 3
                AND f_id_cia = 3
                AND (
                    f_ref_item LIKE ?
                    OR t120.f120_descripcion LIKE ?
                )
            ORDER BY f_fecha DESC, f_id_tipo_docto
        ", [
            $nit,
            $suc,
            "%{$query}%",
            "%{$query}%",
        ]);

        return response()->json([
            'success' => true,
            'productos' => $result,
        ]);
    }

    public function consultaFactura(Request $request)
    {
        $nit  = trim((string) $request->get('nit'));
        $suc  = trim((string) $request->get('sucursal'));
        $fact = trim((string) $request->get('factura'));

        if ($nit === '') $nit = '900447351';
        if ($suc === '') $suc = '020';

        if ($fact === '') {
            return response()->json([
                'success' => false,
                'message' => 'Falta el parámetro factura.',
                'facturas' => [],
            ], 422);
        }

        $result = DB::connection('sqlsrv')->select("
            SELECT
                f_cliente_fact,
                f_cliente_fact_suc,
                f_id_tipo_docto,
                f_nrodocto,
                f_fecha,
                f_ref_item,
                t120.f120_descripcion as f_descripcion,
                f_cant_inv,
                f_precio_venta,
                f_valor_bruto_local,
                f_valor_imp_local,
                f_valor_neto_local
            FROM BI_T461_1
            LEFT JOIN t120_mc_items AS t120
                ON t120.f120_referencia = f_ref_item
                AND t120.f120_id_cia = 3
            WHERE
                f_cliente_fact = ?
                AND f_cliente_fact_suc = ?
                AND f_parametro_biable = 3
                AND f_id_cia = 3
                AND f_nrodocto = ?
            ORDER BY f_fecha DESC, f_id_tipo_docto
        ", [$nit, $suc, $fact]);

        return response()->json([
            'success' => true,
            'facturas' => $result,
        ]);
    }

    public function causalesAsesores()
    {
        $causales = PqrsCausal::query()
            ->with(['submotivo.motivo', 'responsable'])
            ->where('visible_asesor', 1)
            ->orderBy('submotivo_id')
            ->orderBy('orden')
            ->get();

        return response()->json([
            'success' => true,
            'causales' => $causales,
        ]);
    }

    public function departamentos()
    {
        $rows = FleteCiudad::query()
            ->select('cod_depto', 'depto')
            ->where('estado', 1)
            ->groupBy('cod_depto', 'depto')
            ->orderBy('cod_depto')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function ciudadesPorDepartamento(Request $request)
    {
        $codDepto = trim((string)$request->get('cod_depto'));

        if ($codDepto === '') {
            return response()->json([
                'success' => false,
                'message' => 'Falta cod_depto',
                'data' => [],
            ], 422);
        }

        $rows = FleteCiudad::query()
            ->select('cod_ciudad', 'ciudad', 'cod_depto')
            ->where('estado', 1)
            ->where('cod_depto', $codDepto)
            ->orderBy('cod_ciudad')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function store(StorePqrsRequest $request): JsonResponse
    {
        $data = $request->validated();

\Log::info('validated keys', ['keys' => array_keys($data ?? [])]);
\Log::info('producto sample', ['producto' => data_get($data, 'productos.0', [])]);
\Log::info('factura sample', ['factura' => data_get($data, 'factura', [])]);

        $cliente        = $data['cliente'] ?? [];
        $sucursal       = $data['sucursal'] ?? [];
        $pqrsIn         = $data['pqrs'] ?? [];
        $asesorIn       = $data['asesor'] ?? [];
        $modo           = (string)($data['modoAplicacion'] ?? '');
        $direccionEnvio = $data['direccion_envio'] ?? null;

        $puntos = $sucursal['puntos_envio'] ?? $sucursal['puntosEnvio'] ?? $sucursal['puntos'] ?? [];
        $punto000 = collect(is_array($puntos) ? $puntos : [])
            ->first(fn($p) => (string)($p['punto_envio_id'] ?? $p['puntoEnvioId'] ?? '') === '000');

        $correoCliente   = trim((string)($data['correo_cliente'] ?? ''));
        $telefonoCliente = trim((string)($data['telefono_cliente'] ?? ''));

        $codAsesor    = trim((string)($asesorIn['codigo_asesor'] ?? $sucursal['id_vendedor'] ?? ''));
        $nombreAsesor = trim((string)($asesorIn['nombre'] ?? ''));
        $correoAsesor = trim((string)($asesorIn['correo'] ?? ''));

        $requiereRecogida = false;

        if ($modo === 'productos') {
            $productos = $data['productos'] ?? [];
            $requiereRecogida = collect(is_array($productos) ? $productos : [])
                ->contains(fn($p) => (bool)($p['requiereRecogida'] ?? false));
        } elseif ($modo === 'factura') {
            $factura = $data['factura'] ?? [];
            $requiereRecogida = (bool)($factura['requiereRecogida'] ?? false);
        }

        if ($requiereRecogida && empty($direccionEnvio)) {
            return response()->json([
                'success' => false,
                'message' => 'Requiere recogida, pero no llegó direccion_envio.',
            ], 422);
        }

        $encabezado = [
            'nit'          => (string)($cliente['nit'] ?? $cliente['f200_nit'] ?? ''),
            'razon_social' => (string)($cliente['razon_social'] ?? $cliente['f200_razon_social'] ?? $cliente['nombre_establecimiento'] ?? ''),

            'departamento' => (string)($punto000['departamento'] ?? ''),
            'ciudad'       => (string)($punto000['ciudad'] ?? ''),
            'direccion'    => (string)($punto000['direccion'] ?? ''),
            'telefono'     => $telefonoCliente !== ''
                ? $telefonoCliente
                : (string)($punto000['telefono'] ?? ''),

            'correo_cliente' => $correoCliente !== ''
                ? $correoCliente
                : (string)($punto000['email'] ?? $punto000['correo'] ?? ''),
            'tipo_pqrs'     => $modo,
            'cod_asesor'    => $codAsesor,
            'nombre_asesor' => $nombreAsesor,
            'correo_asesor' => $correoAsesor,

            'tipo'        => (string)($pqrsIn['tipo'] ?? ''),
            'prioridad'   => (string)($pqrsIn['prioridad'] ?? 'media'),
            'asunto'      => (string)($pqrsIn['asunto'] ?? ''),
            'descripcion' => (string)($pqrsIn['descripcion'] ?? ''),

            'estado'         => $data['estado'] ?? 'creada',
            'fecha_creacion' => now(),
            'creado_por'     => optional(Auth::user())->id,
        ];

        foreach ($encabezado as $k => $v) {
            if (is_string($v)) {
                $encabezado[$k] = trim($v);
            }
        }

        $ormId = null;
        $pqrs  = null;

        \Log::info('PQRS store - modo', ['modo' => $modo]);
        \Log::info('PQRS store - requiereRecogida', ['requiereRecogida' => $requiereRecogida]);
        \Log::info('PQRS store - direccion_envio', ['direccion_envio' => $direccionEnvio]);

        try {
            $productos = ($modo === 'productos') ? ($data['productos'] ?? []) : [];
            $factura   = ($modo === 'factura') ? ($data['factura'] ?? []) : [];

            DB::transaction(function () use (
                &$pqrs,
                &$ormId,
                $encabezado,
                $requiereRecogida,
                $direccionEnvio,
                $modo,
                $productos,
                $factura
            ) {
                $pqrs = Pqrs::create($encabezado);

                if ($requiereRecogida) {
                    $norm = $this->normalizarDireccionEnvioDesdeApp($direccionEnvio);

                    $orm = Orm::create([
                        'pqrs_id'      => $pqrs->id,
                        'estado'       => 'creada',
                        'nit'          => $encabezado['nit'] ?? null,
                        'razon_social' => $encabezado['razon_social'] ?? null,
                        'direccion'    => $norm['direccion'] ?? null,
                        'departamento' => $norm['departamento'] ?? null,
                        'ciudad'       => $norm['ciudad'] ?? null,
                        'telefono'     => $norm['telefono'] ?? null,
                    ]);

                    $ormId = $orm->id;
                }

                if ($modo === 'productos') {
                    foreach ($productos as $p) {
                        $causalId = (int)($p['causal_id'] ?? 0);
                        if ($causalId <= 0) {
                            throw new \RuntimeException("Falta causal_id en producto.");
                        }

                        $causal = PqrsCausal::query()->findOrFail($causalId);

                        if (!(bool)$causal->visible_asesor) {
                            throw new \RuntimeException("Causal {$causalId} no disponible para asesores.");
                        }

                        $requiereAdjunto = (bool)$causal->requiere_adjunto;
                        $permiteRecogida = (bool)$causal->permite_recogida;

                        $adjuntos = $p['adjuntos'] ?? [];

                        if ($requiereAdjunto && empty($adjuntos)) {
                            throw new \RuntimeException("La causal '{$causal->nombre}' requiere adjunto.");
                        }

                        $requiereRecogidaProducto = (bool)($p['requiereRecogida'] ?? false);

                        if ($requiereRecogidaProducto && !$permiteRecogida) {
                            throw new \RuntimeException("La causal '{$causal->nombre}' no permite recogida.");
                        }

                        $fecha = $this->parseFechaFlexible($p['fecha'] ?? null);

                        $precio   = (float)($p['precio'] ?? 0);
                        $unidades = (float)($p['unidadesSolicitadas'] ?? 0);
                        $bruto    = $precio * $unidades;

                        $ivaOriginal = (float)($p['iva'] ?? 0);
                        $imp         = $ivaOriginal > 0 ? (($bruto * 1.19) - $bruto) : 0;
                        $neto        = $bruto + $imp;

                        $prod = PqrsProducto::create([
                            'pqrs_id'        => $pqrs->id,

                            'causal_id'      => $causal->id,
                            'responsable_id' => $causal->responsable_id,
                            'submotivo_id'   => $causal->submotivo_id,

                            'tipo_docto'     => (string)($p['tipo_docto'] ?? null),
                            'nro_docto'      => (string)($p['nro_docto'] ?? null),
                            'fecha'          => $fecha,

                            'referencia'      => trim((string)($p['referencia'] ?? '')),
                            'descripcion_ref' => (string)($p['descripcion'] ?? null),

                            'unidades_solicitadas' => $unidades,

                            'precio_unitario' => $precio,
                            'valor_bruto'     => $bruto,
                            'valor_imp'       => $imp,
                            'valor_neto'      => $neto,

                            'notas' => filled($p['notas'] ?? null) ? trim((string)$p['notas']) : null,

                            'requiere_recogida'  => $requiereRecogidaProducto,
                            'solicitud_recogida' => $requiereRecogidaProducto ? 1 : 0,
                        ]);

                        if (!empty($adjuntos)) {
                            $this->guardarAdjuntosProducto($pqrs->id, $prod, $adjuntos, 'productos');
                        }
                    }
                }

                if ($modo === 'factura') {
                    $items = $factura['items'] ?? [];
                    $causalId = (int)($factura['causal_id'] ?? 0);

                    if ($causalId <= 0) {
                        throw new \RuntimeException("Falta causal_id en factura.");
                    }

                    if (empty($items) || !is_array($items)) {
                        throw new \RuntimeException("No llegaron items de factura para guardar.");
                    }

                    $causal = PqrsCausal::query()->findOrFail($causalId);

                    if (!(bool)$causal->visible_asesor) {
                        throw new \RuntimeException("Causal {$causalId} no disponible para asesores.");
                    }

                    $requiereAdjunto = (bool)$causal->requiere_adjunto;
                    $permiteRecogida = (bool)$causal->permite_recogida;
                    $adjuntos = $factura['adjuntos'] ?? [];
                    $requiereRecogidaFactura = (bool)($factura['requiereRecogida'] ?? false);

                    if ($requiereAdjunto && empty($adjuntos)) {
                        throw new \RuntimeException("La causal '{$causal->nombre}' requiere adjunto.");
                    }

                    if ($requiereRecogidaFactura && !$permiteRecogida) {
                        throw new \RuntimeException("La causal '{$causal->nombre}' no permite recogida.");
                    }

                    foreach ($items as $p) {
                        $fecha = $this->parseFechaFlexible($p['fecha'] ?? null);

                        $precio   = (float)($p['precio'] ?? 0);
                        $unidades = (float)($p['unidadesSolicitadas'] ?? 0);
                        $bruto    = $precio * $unidades;

                        $ivaOriginal = (float)($p['iva'] ?? 0);
                        $imp         = $ivaOriginal > 0 ? (($bruto * 1.19) - $bruto) : 0;
                        $neto        = $bruto + $imp;

                        $prod = PqrsProducto::create([
                            'pqrs_id'        => $pqrs->id,

                            'causal_id'      => $causal->id,
                            'responsable_id' => $causal->responsable_id,
                            'submotivo_id'   => $causal->submotivo_id,

                            'tipo_docto'     => (string)($p['tipo_docto'] ?? null),
                            'nro_docto'      => (string)($p['nro_docto'] ?? null),
                            'fecha'          => $fecha,

                            'referencia'      => trim((string)($p['referencia'] ?? '')),
                            'descripcion_ref' => (string)($p['descripcion'] ?? null),

                            'unidades_solicitadas' => $unidades,

                            'precio_unitario' => $precio,
                            'valor_bruto'     => $bruto,
                            'valor_imp'       => $imp,
                            'valor_neto'      => $neto,

                            'notas' => filled($factura['notas'] ?? null) ? trim((string)$factura['notas']) : null,

                            'requiere_recogida'  => $requiereRecogidaFactura,
                            'solicitud_recogida' => $requiereRecogidaFactura ? 1 : 0,
                        ]);

                    }


                    if (!empty($adjuntos)) {
                        $this->guardarAdjuntosGeneralesPqrs($pqrs->id, $adjuntos, 'factura');
                    }
                }
            });
        } catch (\Throwable $e) {
            \Log::error('PQRS store ERROR', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creando PQRS/ORM',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $ormId
                ? 'PQRS creada y ORM generada correctamente.'
                : 'PQRS creada correctamente.',
            'data' => [
                'id'         => $pqrs->id,
                'estado'     => $pqrs->estado,
                'orm_id'     => $ormId,
                'created_at' => $pqrs->created_at,
            ],
        ], 201);
    }

    private function normalizarDireccionEnvioDesdeApp(?array $direccionEnvio): array
    {
        $tipo = (string)($direccionEnvio['tipo'] ?? '');
        $d    = $direccionEnvio['data'] ?? [];

        if ($tipo === 'punto') {
            return [
                'tipo_direccion' => 'punto',
                'punto_envio_id' => trim((string)($d['punto_envio_id'] ?? $d['puntoEnvioId'] ?? '')),
                'direccion'      => trim((string)($d['direccion'] ?? '')),
                'departamento'   => trim((string)($d['departamento'] ?? $d['depto'] ?? '')),
                'ciudad'         => trim((string)($d['ciudad'] ?? '')),
                'cod_depto'      => trim((string)($d['cod_depto'] ?? '')),
                'cod_ciudad'     => trim((string)($d['cod_ciudad'] ?? '')),
                'telefono'       => trim((string)($d['telefono'] ?? '')),
                'contacto'       => trim((string)($d['contacto'] ?? '')),
            ];
        }

        return [
            'tipo_direccion' => 'manual',
            'punto_envio_id' => null,
            'direccion'      => trim((string)($d['direccion'] ?? '')),
            'departamento'   => trim((string)($d['depto'] ?? $d['departamento'] ?? '')),
            'ciudad'         => trim((string)($d['ciudad'] ?? '')),
            'cod_depto'      => trim((string)($d['cod_depto'] ?? '')),
            'cod_ciudad'     => trim((string)($d['cod_ciudad'] ?? '')),
            'telefono'       => trim((string)($d['telefono'] ?? '')),
            'contacto'       => trim((string)($d['contacto'] ?? '')),
        ];
    }

    private function parseFechaFlexible($valor): ?string
    {
        if (empty($valor)) {
            return null;
        }

        $s = trim((string)$valor);

        try {
            if (preg_match('/^\d{8}$/', $s)) {
                return Carbon::createFromFormat('Ymd', $s)->toDateString();
            }

            return Carbon::parse($s)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function guardarAdjuntosProducto(int $pqrsId, PqrsProducto $prod, array $adjuntos, string $origen = 'productos'): void
    {
        $ref = $prod->referencia ?: 'sin_ref';
        $dir = "pqrs/{$pqrsId}/{$origen}/{$prod->id}_{$ref}";

        foreach ($adjuntos as $a) {
            if (!is_array($a)) {
                continue;
            }

            $name = (string)($a['name'] ?? 'archivo');
            $mime = (string)($a['mime'] ?? 'application/octet-stream');
            $b64  = (string)($a['base64'] ?? '');

            if ($b64 === '') {
                continue;
            }

            $saved = $this->guardarAdjuntoBase64Public($dir, $name, $b64);

            PqrsProductoAdjunto::create([
                'pqrs_producto_id' => $prod->id,
                'original_name'    => $name,
                'mime'             => $mime,
                'size'             => $saved['size'] ?? null,
                'path'             => $saved['path'],
            ]);
        }
    }

    private function guardarAdjuntoBase64Public(string $dir, string $originalName, string $base64): array
    {
        if (str_contains($base64, ',')) {
            $base64 = explode(',', $base64, 2)[1];
        }

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            throw new \RuntimeException("Base64 inválido para adjunto: {$originalName}");
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalName) ?: 'archivo';
        $filename = now()->format('Ymd_His') . '_' . uniqid() . '_' . $safeName;

        $path = trim($dir, '/') . '/' . $filename;

        Storage::disk('public')->put($path, $binary);

        return [
            'path' => $path,
            'size' => strlen($binary),
        ];
    }

    private function guardarAdjuntosGeneralesPqrs(int $pqrsId, array $adjuntos, string $origen = 'factura'): void
{
    $dir = "pqrs/{$pqrsId}/{$origen}";

    foreach ($adjuntos as $a) {
        if (!is_array($a)) {
            continue;
        }

        $name = (string)($a['name'] ?? 'archivo');
        $mime = (string)($a['mime'] ?? 'application/octet-stream');
        $b64  = (string)($a['base64'] ?? '');

        if ($b64 === '') {
            continue;
        }

        $saved = $this->guardarAdjuntoBase64Public($dir, $name, $b64);

        PqrsAdjunto::create([
            'pqrs_id'        => $pqrsId,
            'origen'         => $origen,
            'original_name'  => $name,
            'mime'           => $mime,
            'size'           => $saved['size'] ?? null,
            'path'           => $saved['path'],
        ]);
    }
}
}