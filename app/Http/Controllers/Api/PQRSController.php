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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PQRSController extends Controller
{
    public function consultaProductos(Request $request, $query)
    {
        $query = trim((string) $query);

        // cliente y sucursal deberían venir del request (para que funcione para cualquier cliente)
        $nit = $request->get('nit');            // ej 900447351
        $suc = $request->get('sucursal');       // ej 020

        // Si aún no quieres enviarlos, puedes dejarlos fijos temporalmente:
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
        $nit  = trim((string) $request->get('nit'));        // ej: 900447351
        $suc  = trim((string) $request->get('sucursal'));   // ej: 020
        $fact = trim((string) $request->get('factura'));    // ej: 00000500

        // Fallback temporal (si aún no lo mandas desde Ionic)
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

        \Log::info('validated keys', array_keys($data));
        \Log::info('producto sample', $data['productos'][0] ?? null);

        $cliente       = $data['cliente']  ?? [];
        $sucursal      = $data['sucursal'] ?? [];
        $pqrsIn        = $data['pqrs']     ?? [];
        $asesorIn      = $data['asesor']   ?? [];
        $modo          = (string)($data['modoAplicacion'] ?? '');
        $direccionEnvio = $data['direccion_envio'] ?? null;

        // ✅ Punto 000 (para datos base)
        $puntos = $sucursal['puntos_envio'] ?? $sucursal['puntosEnvio'] ?? $sucursal['puntos'] ?? [];
        $punto000 = collect(is_array($puntos) ? $puntos : [])
            ->first(fn($p) => (string)($p['punto_envio_id'] ?? $p['puntoEnvioId'] ?? '') === '000');

        // ✅ correo editable viene en ROOT
        $correoCliente = trim((string)($data['correo_cliente'] ?? ''));
        $telefonoCliente = trim((string)($data['telefono_cliente'] ?? ''));

        // ✅ Asesor (ojo: en payload viene "correo", NO "correo_asesor")
        $codAsesor    = trim((string)($asesorIn['codigo_asesor'] ?? $sucursal['id_vendedor'] ?? ''));
        $nombreAsesor = trim((string)($asesorIn['nombre'] ?? ''));
        $correoAsesor = trim((string)($asesorIn['correo'] ?? ''));

        // ✅ Determinar si requiere recogida
        $requiereRecogida = false;

        if ($modo === 'productos') {
            $productos = $data['productos'] ?? [];
            $requiereRecogida = collect(is_array($productos) ? $productos : [])
                ->contains(fn($p) => (bool)($p['requiereRecogida'] ?? false));
        } elseif ($modo === 'factura') {
            $factura = $data['factura'] ?? [];
            $requiereRecogida = (bool)($factura['requiereRecogida'] ?? false);
        }

        // ✅ Si requiere recogida => debe venir direccion_envio
        if ($requiereRecogida && empty($direccionEnvio)) {
            return response()->json([
                'success' => false,
                'message' => 'Requiere recogida, pero no llegó direccion_envio.',
            ], 422);
        }

        // ✅ Encabezado PQRS
        $encabezado = [
            'nit'          => (string)($cliente['nit'] ?? $cliente['f200_nit'] ?? ''),
            'razon_social' => (string)($cliente['razon_social'] ?? $cliente['f200_razon_social'] ?? $cliente['nombre_establecimiento'] ?? ''),

            'departamento' => (string)($punto000['departamento'] ?? ''),
            'ciudad'       => (string)($punto000['ciudad'] ?? ''),
            'direccion'    => (string)($punto000['direccion'] ?? ''),
            'telefono' => $telefonoCliente !== '' 
                ? $telefonoCliente 
                : (string)($punto000['telefono'] ?? ''),

            'correo_cliente' => $correoCliente !== '' ? $correoCliente : (string)($punto000['email'] ?? $punto000['correo'] ?? ''),

            'cod_asesor'    => $codAsesor,
            'nombre_asesor' => $nombreAsesor,
            'correo_asesor' => $correoAsesor,

            // campos de tu form PQRS si los guardas
            'tipo'       => (string)($pqrsIn['tipo'] ?? ''),
            'prioridad'  => (string)($pqrsIn['prioridad'] ?? 'media'),
            'asunto'     => (string)($pqrsIn['asunto'] ?? ''),
            'descripcion' => (string)($pqrsIn['descripcion'] ?? ''),

            'estado'         => $data['estado'] ?? 'creada',
            'fecha_creacion' => now(),
            'creado_por'     => optional(Auth::user())->id,
        ];

        foreach ($encabezado as $k => $v) {
            if (is_string($v)) $encabezado[$k] = trim($v);
        }

        $ormId = null;
        $pqrs = null;

        \Log::info('PQRS store - modo', ['modo' => $modo]);
        \Log::info('PQRS store - requiereRecogida', ['requiereRecogida' => $requiereRecogida]);
        \Log::info('PQRS store - direccion_envio', ['direccion_envio' => $direccionEnvio]);


try {

    // ✅ define los arrays antes del transaction
    $productos = ($modo === 'productos') ? ($data['productos'] ?? []) : [];
    $factura   = ($modo === 'factura') ? ($data['factura'] ?? []) : [];

    DB::transaction(function () use (
        &$pqrs,
        &$ormId,
        $encabezado,
        $requiereRecogida,
        $direccionEnvio,
        $modo,
        $productos,   // ✅ ahora sí existe adentro
        $factura
    ) {

        $pqrs = Pqrs::create($encabezado);

        // ✅ ORM
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

        // ✅ Guardar productos
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

                // ✅ adjuntos vienen como [{name,mime,base64}]
                $adjuntos = $p['adjuntos'] ?? [];

                if ($requiereAdjunto && empty($adjuntos)) {
                    throw new \RuntimeException("La causal '{$causal->nombre}' requiere adjunto.");
                }

                $requiereRecogidaProducto = (bool)($p['requiereRecogida'] ?? false);

                if ($requiereRecogidaProducto && !$permiteRecogida) {
                    throw new \RuntimeException("La causal '{$causal->nombre}' no permite recogida.");
                }

                // ✅ parse fecha YYYYMMDD (ej 20260210)
                $fecha = null;
                if (!empty($p['fecha'])) {
                    $s = trim((string)$p['fecha']);
                    if (preg_match('/^\d{8}$/', $s)) {
                        $fecha = Carbon::createFromFormat('Ymd', $s)->toDateString();
                    } else {
                        // fallback si llega ISO
                        $fecha = Carbon::parse($s)->toDateString();
                    }
                }

                // ✅ tus llaves reales del payload
                $precio = (float)($p['precio'] ?? 0);
                $bruto  = (float)($p['precio']*$p['unidadesSolicitadas'] ?? 0);
                if($p['iva'] > 0){
                    $imp = ((float)($bruto*1.19) - $bruto);
                }else{
                    $imp = 0;
                }
                $neto   = (float)($bruto+$imp  ?? 0);

                $prod = PqrsProducto::create([
                    'pqrs_id'        => $pqrs->id,

                    'causal_id'      => $causal->id,
                    'responsable_id' => $causal->responsable_id,
                    'submotivo_id'   => $causal->submotivo_id,

                    'tipo_docto'     => (string)($p['tipo_docto'] ?? null),
                    'nro_docto'      => (string)($p['nro_docto'] ?? null),
                    'fecha'          => $fecha,

                    'referencia'      => trim((string)($p['referencia'] ?? null)),
                    'descripcion_ref' => (string)($p['descripcion'] ?? null),

                    'unidades_solicitadas' => (float)($p['unidadesSolicitadas'] ?? 0),

                    'precio_unitario' => $precio,
                    'valor_bruto'     => $bruto,
                    'valor_imp'       => $imp,
                    'valor_neto'      => $neto,

                    'requiere_recogida'  => $requiereRecogidaProducto,
                    'solicitud_recogida' => $requiereRecogidaProducto ? 1 : 0,
                ]);

                // ✅ adjuntos: guardar en disk public + DB
                if (!empty($adjuntos)) {

                    $ref = $prod->referencia ?: 'sin_ref';
                    $dir = "pqrs/{$pqrs->id}/productos/{$prod->id}_{$ref}";

                    foreach ($adjuntos as $a) {

                        // si accidentalmente llega un File/obj raro, lo saltamos
                        if (!is_array($a)) continue;

                        $name = (string)($a['name'] ?? 'archivo');
                        $mime = (string)($a['mime'] ?? 'application/octet-stream');
                        $b64  = (string)($a['base64'] ?? '');

                        if ($b64 === '') continue;

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
            }
        }

    });

} catch (\Throwable $e) {
    \Log::error('PQRS store ERROR', [
        'msg' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
    ]);

    return response()->json([
        'success' => false,
        'message' => 'Error creando PQRS/ORM',
        'error' => $e->getMessage(),
    ], 500);
}

        return response()->json([
            'success' => true,
            'message' => $ormId ? 'PQRS creada y ORM generada correctamente.' : 'PQRS creada correctamente.',
            'data' => [
                'id'         => $pqrs->id,
                'estado'     => $pqrs->estado,
                'orm_id'     => $ormId,
                'created_at' => $pqrs->created_at,
            ],
        ], 201);
    }

    /**
     * ✅ Normaliza direccion_envio que llega tal cual desde Ionic:
     * direccion_envio: { tipo: "punto"|"manual", data: { ... } }
     */
    private function normalizarDireccionEnvioDesdeApp(?array $direccionEnvio): array
    {
        $tipo = (string)($direccionEnvio['tipo'] ?? '');
        $d    = $direccionEnvio['data'] ?? [];

        // En tu payload punto_envio_id viene dentro de data
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

        // manual
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

    private function guardarAdjuntoBase64Public(string $dir, string $originalName, string $base64): array
    {
        // soporta "data:...;base64,XXXX"
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
}
