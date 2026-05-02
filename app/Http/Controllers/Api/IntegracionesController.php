<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Xml\PedidoIntegracion as PedidoXml;
use App\Mail\PedidoConfirmadoMail;
use App\Models\DireccionEnvio;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\FleteCiudad;

class IntegracionesController extends Controller
{
    public function index(Request $request)
    {
        $token = $this->obtenerTokenBagisto();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'No fue posible autenticar con Bagisto',
            ], 401);
        }

        $response = $this->consultarProductosBagisto($token, $request);

        if ($response->status() === 401) {
            Cache::forget('bagisto_api_token');

            $token = $this->obtenerTokenBagisto();

            if (! $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No fue posible renovar el token de Bagisto',
                ], 401);
            }

            $response = $this->consultarProductosBagisto($token, $request);
        }

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Error consultando productos en Bagisto',
                'status'  => $response->status(),
                'error'   => $response->json(),
            ], $response->status());
        }

        $bagistoData = $response->json();

        $referencias = collect($bagistoData['referencias'] ?? $bagistoData['       referencias'] ?? [])
            ->map(fn ($ref) => trim((string) $ref))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $productosErp = collect($this->productos($referencias))
            ->keyBy('referencia');

        $data = collect($bagistoData['data'] ?? [])->map(function ($producto) use ($productosErp) {
            $referencia = $producto['referencia'] ?? $producto['sku'] ?? null;

            $erp = $referencia ? $productosErp->get($referencia) : null;

            $producto['marca'] = $erp->marca ?? null;
            $producto['notas'] = $erp->notas ?? null;
            $producto['disponible'] = $erp->disponible ?? 0;
            $producto['precio'] = $erp->precio_1 ?? 0;
            $producto['precio_iva'] = $erp->precio_1_iva ?? 0;
            $producto['precio_mas_iva'] = $erp->precio_1_mas_iva ?? 0;

            return $producto;
        })->values();

        return response()->json([
            'success'     => true,
            //'referencias' => $referencias,
            'data'        => $data,
            'pagination'  => $bagistoData['pagination'] ?? null,
        ]);
    }

    private function obtenerTokenBagisto(): ?string
    {
        return Cache::remember('bagisto_api_token', now()->addHours(8), function () {
            $response = Http::acceptJson()
                ->asJson()
                ->post(config('services.bagisto.base_url') . '/integraciones/login', [
                    'email'    => config('services.bagisto.email'),
                    'password' => config('services.bagisto.password'),
                ]);

            if ($response->failed()) {
                return null;
            }

            return $response->json('token');
        });
    }

    private function consultarProductosBagisto(string $token, Request $request)
    {
        return Http::withToken($token)
            ->acceptJson()
            ->get(config('services.bagisto.base_url') . '/integraciones/productos', [
                'search'   => $request->get('search'),
                'per_page' => $request->get('per_page', 1000),
                'page'     => $request->get('page', 1),
            ]);
    }

    private function productos(array $referencias): array
    {
        if (empty($referencias)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($referencias), '?'));

        $sql = "
            SELECT 
                t120.f120_id item,
                RTRIM(t120.f120_referencia) referencia,
                RTRIM(t106.f106_descripcion) AS marca,
                RTRIM(t120.f120_notas) notas,
                CONVERT(decimal(10), SUM(t400.f400_cant_existencia_1 - t400.f400_cant_comprometida_1)) AS disponible,
                CONVERT(decimal(10), PRECIOS1.PrecioBase) AS precio_1,
                CONVERT(decimal(10), PRECIOS1.Impuesto) AS precio_1_iva,
                CONVERT(decimal(10), PRECIOS1.PrecioImp) AS precio_1_mas_iva
            FROM t400_cm_existencia t400
            INNER JOIN t121_mc_items_extensiones t121
                ON t400.f400_rowid_item_ext = t121.f121_rowid
                AND t121.f121_ind_estado = 1
            INNER JOIN t120_mc_items t120
                ON t120.f120_rowid = t121.f121_rowid_item
            LEFT JOIN t125_mc_items_criterios t125
                ON t125.f125_rowid_item = t120.f120_rowid
                AND PATINDEX('%[a-zA-Z]%', t120.f120_referencia) <= 0
                AND t125.f125_id_cia = t120.f120_id_cia
                AND t125.f125_id_plan = '003'
            INNER JOIN t106_mc_criterios_item_mayores t106
                ON t106.f106_id = t125.f125_id_criterio_mayor
                AND t106.f106_id_plan = t125.f125_id_plan
                AND t106.f106_id_plan = '003'
                AND t106.f106_id_cia = t125.f125_id_cia
            LEFT JOIN (
                SELECT
                    t120.f120_id Items,
                    COALESCE(t126.f126_precio, 0) PrecioBase,
                    CASE 
                        WHEN COALESCE(t037.f037_tasa, 0) = 0 
                        THEN t126.f126_precio 
                        ELSE t126.f126_precio * (1 + (COALESCE(t037.f037_tasa, 0) / 100)) 
                    END PrecioImp,
                    CASE 
                        WHEN COALESCE(t037.f037_tasa, 0) = 0 
                        THEN t126.f126_precio 
                        ELSE (t126.f126_precio * (1 + (COALESCE(t037.f037_tasa, 0) / 100))) - t126.f126_precio 
                    END Impuesto
                FROM t126_mc_items_precios t126
                INNER JOIN t120_mc_items t120 
                    ON t120.f120_rowid = t126.f126_rowid_item
                LEFT JOIN t114_mc_grupos_impo_impuestos t114 
                    ON t120.f120_id_cia = t114.f114_id_cia
                    AND t114.f114_grupo_impositivo = t120.f120_id_grupo_impositivo
                    AND t114.f114_id_clase_impuesto = 1
                    AND t114.f114_ind_tipo_indicador = 3
                LEFT JOIN t037_mm_llaves_impuesto t037 
                    ON t037.f037_id = t114.f114_id_llave_impuesto
                    AND t037.f037_id_cia = t114.f114_id_cia
                INNER JOIN (
                    SELECT
                        t126.f126_rowid_item RowidItem,
                        MAX(t126.f126_fecha_activacion) Fecha
                    FROM t126_mc_items_precios t126
                    WHERE t126.f126_id_cia = 3
                    AND t126.f126_id_lista_precio = 001
                    GROUP BY t126.f126_rowid_item
                ) Act_Precio 
                    ON Act_Precio.RowidItem = t126.f126_rowid_item 
                    AND Act_Precio.Fecha = t126.f126_fecha_activacion
                WHERE t126.f126_id_cia = 3
                AND t126.f126_id_lista_precio = 001
            ) AS PRECIOS1 
                ON PRECIOS1.Items = t120.f120_id
            WHERE t400.f400_id_cia = 3
            AND t400.f400_rowid_bodega IN ('1062')
            AND RTRIM(t120.f120_referencia) IN ($placeholders)
            GROUP BY 
                t120.f120_id,
                t120.f120_referencia,
                t120.f120_notas,
                PRECIOS1.PrecioBase,
                PRECIOS1.PrecioImp,
                PRECIOS1.Impuesto,
                t106.f106_descripcion
            ORDER BY 1
        ";

        return DB::connection('sqlsrv')->select($sql, $referencias);
    }
    public function guardarPedido(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nit_integrador' => [
            'required',
            'string',
            Rule::in(['900447351']),
        ],

        'oc' => 'required|string|max:30',
        'notas' => 'nullable|string|max:500',

        'nombre_ciudad' => 'required|string|max:100',
        'codigo_ciudad' => 'required|string|max:10',
        'nombre_departamento' => 'required|string|max:100',
        'codigo_departamento' => 'required|string|max:10',
        'direccion_envio' => 'required|string|max:200',

        'nombre_cliente' => 'required|string|max:150',
        'documento_cliente' => 'required|string|max:30',
        'telefono_cliente' => 'required|string|max:30',

        'totales' => 'required|array',
        'totales.flete' => 'required|numeric|min:0',
        'totales.subtotal' => 'required|numeric|min:0',
        'totales.iva' => 'required|numeric|min:0',
        'totales.total' => 'required|numeric|min:0',

        'productos' => 'required|array|min:1',
        'productos.*.referencia' => 'required|string|max:50',
        'productos.*.descripcion' => 'required|string|max:200',
        'productos.*.cantidad' => 'required|numeric|min:1',
        'productos.*.precio' => 'required|numeric|min:0',
    ], [
        'nit_integrador.required' => 'El nit_integrador es obligatorio.',
        'nit_integrador.in' => 'El nit_integrador no está autorizado.',

        'oc.required' => 'La orden de compra es obligatoria.',

        'nombre_ciudad.required' => 'La ciudad es obligatoria.',
        'codigo_ciudad.required' => 'El código de ciudad es obligatorio.',
        'nombre_departamento.required' => 'El departamento es obligatorio.',
        'codigo_departamento.required' => 'El código de departamento es obligatorio.',
        'direccion_envio.required' => 'La dirección de envío es obligatoria.',

        'nombre_cliente.required' => 'El nombre del cliente es obligatorio.',
        'documento_cliente.required' => 'El documento del cliente es obligatorio.',
        'telefono_cliente.required' => 'El teléfono del cliente es obligatorio.',

        'totales.required' => 'Los totales son obligatorios.',
        'totales.flete.required' => 'El flete es obligatorio.',
        'totales.subtotal.required' => 'El subtotal es obligatorio.',
        'totales.iva.required' => 'El IVA es obligatorio.',
        'totales.total.required' => 'El total es obligatorio.',

        'productos.required' => 'Debe enviar mínimo un producto.',
        'productos.min' => 'Debe enviar mínimo un producto.',
        'productos.*.referencia.required' => 'La referencia del producto es obligatoria.',
        'productos.*.cantidad.required' => 'La cantidad del producto es obligatoria.',
        'productos.*.cantidad.min' => 'La cantidad debe ser mayor a cero.',
        'productos.*.precio.required' => 'El precio del producto es obligatorio.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'ok' => false,
            'mensaje' => 'Error de validación',
            'errores' => $validator->errors(),
        ], 422);
    }

    $data = $validator->validated();

    $oc = $data['oc'];
    $nit_integrador = $data['nit_integrador'];

    $prefijos = [
        '900447351' => 'PVL',
    ];

    $totalPedido = (float) $data['totales']['total'];

    $fleteCalculado = $this->obtenerFleteCalculado(
        $request->codigo_ciudad,
        $request->codigo_departamento,
        $totalPedido
    );

    $flete = $fleteCalculado['flete'];

    $prefijo = $prefijos[$nit_integrador];

        $referenciasValidar = collect($request->productos)->pluck('referencia')->unique()->toArray();
        $referenciasSql = "'" . implode("','", array_map('addslashes', $referenciasValidar)) . "'";

        $validacion = DB::connection('sqlsrv')->select("SELECT RTRIM(t120.f120_referencia) AS referencia,
            SUM(t400.f400_cant_existencia_1) AS existencia
            FROM t400_cm_existencia t400
            INNER JOIN t121_mc_items_extensiones t121
                ON t400.f400_rowid_item_ext = t121.f121_rowid
                AND t121.f121_ind_estado = 1
            INNER JOIN t120_mc_items t120
                ON t120.f120_rowid = t121.f121_rowid_item
            LEFT JOIN t125_mc_items_criterios t125
                ON t125.f125_rowid_item = t120.f120_rowid
                AND PATINDEX('%[a-zA-Z]%', t120.f120_referencia) <= 0
                AND t125.f125_id_cia = t120.f120_id_cia
                AND t125.f125_id_plan = '003'
            WHERE t120.f120_referencia IN ($referenciasSql)
            GROUP BY t120.f120_referencia");

        $existencias = collect($validacion)->keyBy('referencia')->map(fn($i) => (float) $i->existencia);
        $errores = [];

        foreach ($request->productos as $producto) {
            $referencia = $producto['referencia'];
            $cantidadSolicitada = (float) $producto['cantidad'];
            $existenciaDisponible = $existencias[$referencia] ?? 0;

            if ($cantidadSolicitada > $existenciaDisponible) {
                $errores[] = [
                    'referencia' => $referencia,
                    'cantidad_solicitada' => $cantidadSolicitada,
                    'existencia_disponible' => $existenciaDisponible,
                ];
            }
        }

        if (!empty($errores)) {
            return response()->json([
                'error' => 'No hay unidades disponibles para algunos productos',
                'detalles' => $errores
            ], 422);
        }

        $orden_compra = $prefijo.$oc;

        $notasOriginal = 'NO ENVIAR FACTURA - Cliente: '.$request->nombre_cliente.' - CC/Nit: '.$request->documento_cliente.' - Telefono: '.$request->telefono_cliente.' - Direccion: '.$request->direccion_envio.' ('.$request->nombre_ciudad.' - '.$request->nombre_departamento.')';

        $notasLimpias = strtr($notasOriginal, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'ñ' => 'n',
            'Ñ' => 'N'
        ]);

        DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'codigo_asesor' => 'Virtual Llantas',
                'nombre_asesor' => 'App Merlin',
                'nit' => '900447351',
                'razon_social' => 'VIRTUAL LLANTAS S.A.S.',
                'lista_precio' => '001',
                'estado_siesa' => 'Comprometido',
                'id_estado_pedido' => 1,
                'prefijo' => $prefijo,
                'nota' => $notasLimpias,
                'orden_compra' => $orden_compra,
                'correo_cliente' =>  'sistemas@merlinrod.com',
                'estado' => '1',
                'id_sucursal' => '020',
                'flete' => $fleteCalculado['flete'],
                'observaciones' => $notasLimpias,
                'condicion_pago' => '30D',
                'fecha_pedido' => now(),
            ]);

           // return $pedido;
            
            foreach ($request->productos as $producto) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'referencia' => $producto['referencia'],
                    'descripcion' => $producto['descripcion'] ?? '',
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio'] ?? 0,
                    'descuento' => $producto['descuento'] ?? 0,
                    'subtotal' => $producto['subtotal'] ?? 0,
                ]);
            }

                DireccionEnvio::create([
                    'pedido_id' => $pedido->id,
                    'id_punto_envio' => '000',
                    'direccion' => $request->direccion_envio,
                    'ciudad' => $request->nombre_ciudad,
                    'departamento' => $request->nombre_departamento,
                    'codigo_ciudad' => $request->codigo_ciudad,
                    'codigo_departamento' => $request->codigo_departamento,
                ]);

            $pedidoXml = new PedidoXml();
            
            $resultadoXml = $pedidoXml->generarXml($pedido);
            //$resultadoXml = $pedidoXml->generarXml($pedido);
 
            if ($resultadoXml['status'] !== 'success') {

                $xmlResponse = $resultadoXml['xmlResult']->ImportarXMLResult->any;
                $xmlObject = simplexml_load_string($xmlResponse, "SimpleXMLElement", LIBXML_NOCDATA);
                $f_detalle = (string) $xmlObject->NewDataSet->Table->f_detalle;

                DB::rollBack();
                return response()->json([
                    'error' => 'Error al generar XML',
                    'mensaje' => 'Error al procesar el pedido en el ERP: ' . $f_detalle,
                ], 500);
            }

            $info_pedido = Pedido::with('direccionEnvio')->find($pedido->id);

            $validacion_siesa = DB::connection('sqlsrv')->select("SELECT [f_id_tipo_docto] as prefijo, 
            [f_nrodocto] as consecutivo 
            FROM [BI_T430] 
            WHERE [f_parametro_biable] = 3 
            AND [f_id_cia] = 3 AND [f_cliente_desp] = ? AND [f_cliente_fact_suc] = ? AND [f_punto_envio] = ? AND [f_orden_compra] = ?", [
                $info_pedido->nit,
                '020',
                '000',
                $info_pedido->orden_compra
            ]);

            if (empty($validacion_siesa)) {
                DB::rollBack();
                return response()->json(['error' => 'No se ha creado el pedido en Siesa'], 500);
            }

            foreach ($validacion_siesa as $validar) {
                $prefijo_siesa = $validar->prefijo;
                $consecutivo_siesa = $validar->consecutivo;
            }

            $pedido_siesa = $prefijo_siesa . '-' . $consecutivo_siesa;
            $pedido->nota = $resultadoXml['status'] === 'success' ? $pedido_siesa : 'No creado en Siesa';
            $pedido->save();

            $encabezados = DB::connection('sqlsrv')->select("SELECT CONCAT(bi_t430.[f_id_tipo_docto],' ',bi_t430.[f_nrodocto]) as documento
                        ,bi_t430.[f_fecha] as fecha
                        ,bi_t430.[f_estado] as estado
                        ,bi_t430.f_subtotal_local as f_subtotal
                        ,bi_t430.[f_cliente_desp] as nit_cliente
                        ,t200.f200_razon_social as razon_social
                        ,t200.f200_rowid as row_tercero
                        ,bi_t430.[f_vendedor] as nit_vendedor
                        ,(SELECT TOP 1 CONCAT([f200_nombres],' ',[f200_apellido1],' ',[f200_apellido2]) as nombre
                            FROM [t200_mm_terceros]
                            WHERE [f200_id_cia] = 3
                            AND [f200_nit] = f_vendedor) as nombre_asesor
                            ,bi_t430.[f_notas] as notas
                            ,CONVERT(decimal(10), bi_t430.[f_subtotal_docto] * 1, 0) as subtotal
                            ,bi_t430.[f_facturado] as facturado
                            ,bi_t430.[f_cliente_desp_suc] as sucursal
                            ,bi_t430.[f_punto_envio] as punto_envio
                            ,t215.f215_rowid_contacto row_contacto_pe
                            ,t215.f215_descripcion descripcion_punto_envio
                            ,t215.f215_rowid_tercero row_tereco_pe
                            ,t015.[f015_contacto] contacto
                            ,t015.[f015_direccion1] direccion
                            ,t015.[f015_direccion2] direccion2
                            ,t015.[f015_direccion3] direccion3
                            ,t015.[f015_id_depto] cod_departamento
                            ,[f012_descripcion] depto
                            ,t015.[f015_id_ciudad] cod_ciudad
                            ,[f013_descripcion] ciudad
                            ,t015.[f015_telefono] telefono
                            ,t015.[f015_email] email
                            ,t015.[f015_celular] celular
                            ,t201.[f201_id_cond_pago] as cond_pago
                        FROM [BI_T430] as bi_t430
                        LEFT JOIN [t200_mm_terceros] as t200
                        ON bi_t430.f_cliente_fact = t200.f200_nit
                        LEFT JOIN [t215_mm_puntos_envio_cliente] as t215
                        ON t200.f200_rowid = t215.f215_rowid_tercero
                        AND bi_t430.[f_punto_envio] = t215.f215_id
                        AND bi_t430.[f_cliente_desp_suc] = t215.f215_id_sucursal
                        LEFT JOIN [t201_mm_clientes] as t201
                        ON t201.f201_rowid_tercero = t200.f200_rowid
                        AND t201.f201_id_sucursal = bi_t430.[f_cliente_desp_suc]
                        LEFT JOIN [t015_mm_contactos] as t015
                        ON t215.f215_rowid_contacto = t015.f015_rowid
                        LEFT JOIN [t012_mm_deptos] as t012
                        ON t012.[f012_id] = t015.[f015_id_depto]
                        LEFT JOIN [t013_mm_ciudades] as t013
                        ON t013.[f013_id] = t015.[f015_id_ciudad]
                        AND t013.[f013_id_depto] = t015.[f015_id_depto]
                        WHERE bi_t430.f_id_cia = 3
                        AND t200.f200_id_cia = 3
                        AND [f_id_cia]  = '3'
                        AND bi_t430.f_parametro_biable = '3'
                        AND bi_t430.[f_co] = '003'
                        and bi_t430.[f_id_tipo_docto] = '$prefijo_siesa'
                        and bi_t430.[f_nrodocto] = '$consecutivo_siesa '
                        and t012.[f012_id_pais] = 169
                        and t013.[f013_id_pais] = 169
                        AND t200.f200_ind_estado = 1
                        ORDER BY bi_t430.f_nrodocto desc;");

            $detalles = DB::connection('sqlsrv')->select("SELECT  
                        t120.f120_referencia AS referencia,
                        t106.f106_descripcion AS marca,
                        t120.f120_descripcion AS descripcion,
                        CONVERT(decimal(10, 0), t431.f_cant_pedida_base) AS cantidad,
                        CONVERT(decimal(10, 0), t431.f_cant_comprom_base) AS cantidad_comprometida,
                        CONVERT(decimal(10, 0), t431.f_precio_unit_docto) AS valor_unitario,
                        CONVERT(decimal(10, 0), t431.f_valor_neto_local) AS valor_neto,
                        CONVERT(decimal(10, 0), t431.f_dscto_promedio) AS descuento,
                        CONVERT(decimal(10, 0), t431.f_valor_subtotal_local) AS subtotal,
                        CONVERT(decimal(10, 0), t431.f_valor_imp_local) AS impuestos,
                        CONVERT(decimal(10, 0), t431.f_valor_dscto_local) AS total_descuento
                    FROM BI_T431 t431
                    JOIN t120_mc_items t120 
                        ON t120.f120_rowid = t431.f_rowid_item AND t120.f120_id_cia = '3'
                    JOIN t125_mc_items_criterios t125 
                        ON t125.f125_rowid_item = t431.f_rowid_item 
                        AND t125.f125_id_cia = '3' 
                        AND t125.f125_id_plan = '003'
                    JOIN t105_mc_criterios_item_planes t105 
                        ON t105.f105_id = t125.f125_id_plan 
                        AND t105.f105_id_cia = '3'
                    JOIN t106_mc_criterios_item_mayores t106 
                        ON t106.f106_id = t125.f125_id_criterio_mayor 
                        AND t106.f106_id_plan = '003' 
                        AND t106.f106_id_cia = '3'
                    WHERE 
                        t431.f_id_cia = '3'
                        AND t431.f_co = '003'
                        AND t431.f_parametro_biable = '3'
                        AND t431.f_id_tipo_docto = '$prefijo_siesa'
                        AND t431.f_nrodocto = '$consecutivo_siesa'
                    ORDER BY 
                        t120.f120_referencia;");

            $subtotal_pedido = 0;
            $subtotal_descuento = 0;

            foreach ($detalles as  $detalle) {
                $subtotal_pedido += $detalle->valor_unitario * $detalle->cantidad;
                $subtotal_descuento += $detalle->total_descuento;
            }


            DB::commit();

            try {
                // correos
                $correos = ['rdalzate@utp.edu.co','sistemas@merlinrod.com'];

                Mail::to($correos)
                    ->send(new PedidoConfirmadoMail($encabezados, $detalles, $subtotal_pedido, $subtotal_descuento));

                return response()->json([
                    'success' => 'ok full',
                    'mensaje' => 'Se ha enviado el pedido, se ha creado en SIESA ' . $prefijo_siesa . '-' . $consecutivo_siesa . ' - Correos enviados.'
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'warning' => 'Pedido creado y guardado, pero falló el envío del correo',
                    'error' => $e->getMessage(),
                    'mensaje' => 'Se ha enviado el pedido, se ha creado en SIESA ' . $prefijo_siesa . '-' . $consecutivo_siesa . ' - No se ha enviado los correos.'
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Ocurrió un error al guardar el pedido',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function calcularFlete(Request $request)
    {
        $data = $request->validate([
            'codigo_ciudad' => 'required|string',
            'codigo_departamento' => 'required|string',
            'total' => 'required|numeric|min:0',
        ]);

        $fleteCiudad = FleteCiudad::where('cod_ciudad', $data['codigo_ciudad'])
            ->where('cod_depto', $data['codigo_departamento'])
            ->first();

        if (!$fleteCiudad) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No se encontró configuración de flete para la ciudad enviada.',
            ], 404);
        }

        $total = (float) $data['total'];
        $monto = (float) $fleteCiudad->monto;
        $minimo = (float) $fleteCiudad->minimo;

        $porcentaje = $total > $monto
            ? (float) $fleteCiudad->mayor
            : (float) $fleteCiudad->menor;

        $valorCalculado = ($total * $porcentaje) / 100;

        $valorFlete = $valorCalculado < $minimo
            ? $minimo
            : $valorCalculado;

        return response()->json([
            'ok' => true,
            'codigo_departamento' => $data['codigo_departamento'],
            'codigo_ciudad' => $data['codigo_ciudad'],
            'flete' => round($valorFlete, 2)
        ]);
    }

    private function obtenerFleteCalculado(string $codigoCiudad,string $codigoDepartamento,float $total
    ): array {
        $fleteCiudad = FleteCiudad::where('cod_ciudad', $codigoCiudad)
            ->where('cod_depto', $codigoDepartamento)
            ->first();

        if (!$fleteCiudad) {
            throw new \Exception('No se encontró configuración de flete para la ciudad enviada.');
        }

        $monto = (float) $fleteCiudad->monto;
        $minimo = (float) $fleteCiudad->minimo;

        $porcentaje = $total > $monto
            ? (float) $fleteCiudad->mayor
            : (float) $fleteCiudad->menor;

        $valorCalculado = ($total * $porcentaje) / 100;

        $valorFlete = $valorCalculado < $minimo
            ? $minimo
            : $valorCalculado;

        return [
            'flete' => round($valorFlete, 2),
            'valor_calculado' => round($valorCalculado, 2),
            'porcentaje_aplicado' => $porcentaje,
            'minimo' => $minimo,
            'monto_base' => $monto,
            'dias_entrega' => $fleteCiudad->entrega,
        ];
    }
}