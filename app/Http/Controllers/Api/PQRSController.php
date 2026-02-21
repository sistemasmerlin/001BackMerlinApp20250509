<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PqrsCausal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StorePqrsRequest;
use App\Models\FleteCiudad;
use App\Models\Pqrs;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        $cliente  = $data['cliente']  ?? [];
        $sucursal = $data['sucursal'] ?? [];
        $pqrsIn   = $data['pqrs']     ?? [];
        $asesorIn = $data['asesor']   ?? [];

        // ✅ Punto de envío 000 (siempre)
        $puntos = $sucursal['puntos_envio'] ?? $sucursal['puntosEnvio'] ?? $sucursal['puntos'] ?? [];
        $punto000 = collect(is_array($puntos) ? $puntos : [])
            ->first(fn($p) => (string)($p['punto_envio_id'] ?? $p['puntoEnvioId'] ?? '') === '000');

        // ✅ Correo editable viene en el ROOT del payload
        $correoCliente = trim((string)($data['correo_cliente'] ?? ''));

        // ✅ Asesor: viene desde Ionic (storage.usuario) o fallback a sucursal
        $codAsesor    = trim((string)($asesorIn['codigo_asesor'] ?? $sucursal['id_vendedor'] ?? ''));
        $nombreAsesor = trim((string)($asesorIn['nombre'] ?? ''));
        $correoAsesor = trim((string)($asesorIn['correo'] ?? ''));

        $encabezado = [
            'nit'          => (string)($cliente['nit'] ?? $cliente['f200_nit'] ?? ''),
            'razon_social' => (string)($cliente['razon_social'] ?? $cliente['f200_razon_social'] ?? $cliente['nombre_establecimiento'] ?? ''),

            'departamento' => (string)($punto000['departamento'] ?? ''),
            'ciudad'       => (string)($punto000['ciudad'] ?? ''),
            'direccion'    => (string)($punto000['direccion'] ?? ''),
            'telefono'     => (string)($punto000['telefono'] ?? ''),

            // ✅ correo editable (si no viene, usa el del punto 000)
            'correo_cliente' => $correoCliente !== '' ? $correoCliente : (string)($punto000['email'] ?? ''),

            // ✅ asesor
            'cod_asesor'    => $codAsesor,
            'nombre_asesor' => $nombreAsesor,
            'correo_asesor' => $correoAsesor,

            // ✅ estado/fechas
            'estado'         => $data['estado'] ?? 'creada',
            'fecha_creacion' => now(),

            // ✅ quién crea (si tienes auth por Sanctum)
            'creado_por'     => optional(Auth::user())->id,
        ];

        // (opcional) trim a strings
        foreach ($encabezado as $k => $v) {
            if (is_string($v)) $encabezado[$k] = trim($v);
        }

        $pqrs = Pqrs::create($encabezado);

        return response()->json([
            'success' => true,
            'message' => 'PQRS creada correctamente.',
            'data' => [
                'id' => $pqrs->id,
                'estado' => $pqrs->estado,
                'created_at' => $pqrs->created_at,
            ],
        ], 201);
    }
}
