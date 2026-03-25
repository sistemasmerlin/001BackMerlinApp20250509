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