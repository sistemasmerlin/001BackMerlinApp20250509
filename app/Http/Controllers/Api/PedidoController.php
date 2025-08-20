<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\DireccionEnvio;
use App\Models\Backorder;
use App\Xml\Pedido as PedidoXml;
use App\Mail\PedidoConfirmadoMail;
use App\Mail\PedidoEspecialMail;
use App\Models\DetalleBackorder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{

    public function pedidosConError($id){
        $pedidos = Pedido::where('nota','=','No creado en Siesa')->where('codigo_asesor')->get();

        if($pedidos){
            return response()->json([
                'estado' => true,
                'mensaje' => 'Se retornan los pedidos que no llegaron a Siesa pero se crearon en la app',
            ], 200);

        }else{
            return response()->json([
                'estado' => true,
                'mensaje' => 'No se encontraron pedidos',
            ], 200);

        }

    }

    public function pedidosErp($codigo_asesor){

        // return $codigo_asesor;

        $pedidos = DB::connection('sqlsrv')
        ->select("SELECT TOP (50)
            [f430_fecha_ts_creacion] fecha_creacion
            ,t210.[f210_id] codigo_asesor
            ,[f430_id_tipo_docto] prefijo
            ,[f430_consec_docto] consecutivo
            ,t200.[f200_nit] nit
            ,t200.[f200_dv_nit] digito_verificacion
            ,t200.[f200_razon_social] razon_social
            ,[f430_ind_estado] estado
            ,CASE 
                WHEN [f430_ind_facturado] = 0 THEN 'No'
                WHEN [f430_ind_facturado] = 0 THEN 'Si'
            ELSE 'No' END as facturado
            ,[f430_rowid_tercero_fact]
        FROM [t430_cm_pv_docto]
        LEFT JOIN [t210_mm_vendedores] as t210
        ON t210.f210_rowid_tercero = f430_rowid_tercero_vendedor
        AND f430_id_cia = 3
        LEFT JOIN [t200_mm_terceros] as t200
        ON f430_rowid_tercero_fact = t200.f200_rowid
        AND t200.f200_id_cia = 3
        WHERE [f430_id_cia] = 3
        AND [f430_id_concepto] = 501
        AND [f430_id_grupo_clase_docto] = 502
        AND t210.[f210_id] = $codigo_asesor
        ORDER BY f430_fecha_ts_creacion desc");

            return response()->json([
                'pedidos' => $pedidos,
                'estado' => true,
                'mensaje' => 'Se retornan los pedidos que no llegaron a Siesa pero se crearon en la app',
            ], 200);

    }

    public function detallePedidoErp($prefijo, $consecutivo){

        $encabezado = DB::connection('sqlsrv')->select("SELECT CONCAT(bi_t430.[f_id_tipo_docto],' ',bi_t430.[f_nrodocto]) as documento
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
            and bi_t430.[f_id_tipo_docto] = '$prefijo'
            and bi_t430.[f_nrodocto] = '$consecutivo'
            and t012.[f012_id_pais] = 169
            and t013.[f013_id_pais] = 169
            AND t200.f200_ind_estado = 1
            ORDER BY bi_t430.f_nrodocto desc;");


        $detalle = DB::connection('sqlsrv')->select("SELECT  
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
            AND t431.f_id_tipo_docto = '$prefijo'
            AND t431.f_nrodocto = '$consecutivo'
        ORDER BY 
            t120.f120_referencia;");

        $subtotal_pedido = 0;
        $subtotal_descuento = 0;

        foreach($detalle as  $item){
        $subtotal_pedido += $item->valor_unitario * $item->cantidad;
        $subtotal_descuento += $item->total_descuento;
        } 

        return response()->json([
            'encabezado' => $encabezado,
            'detalle' => $detalle,
            'subtotal_pedido' => $subtotal_pedido,
            'subtotal_descuento' => $subtotal_descuento,
            'estado' => true,
            'mensaje' => 'Se retornan los pedidos que no llegaron a Siesa pero se crearon en la app',
        ], 200);
    }

    public function guardar(Request $request)
    {
        $data = $request->validate([
            'cliente.nit' => 'required|string',
            'cliente.razon_social' => 'required|string',
            'cliente.email' => 'nullable|email',
            'sucursal.id_sucursal' => 'required|string',
            'sucursal.descripcion_sucursal' => 'required|string',
            'productos' => 'required|array',
            'productos.*.referencia' => 'required|string',
            'productos.*.cantidad' => 'required|numeric|min:1',
            'productos.*.precio_1' => 'required|numeric|min:0',
            'totales' => 'required|array',
            'creadoPor.nombre' => 'required|string',
            'creadoPor.codigo' => 'required|string',
        ]);

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

        $orden_compra = date('ymdHis');
        $notasOriginal = $request['notas'] ?? '';
        $notasLimpias = strtr($notasOriginal, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N'
        ]);

        DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'codigo_asesor' => $request->creadoPor['codigo'],
                'nombre_asesor' => $request->creadoPor['nombre'],
                'nit' => $request->cliente['nit'],
                'razon_social' => $request->cliente['razon_social'],
                'lista_precio' => $request->sucursal['lista_precio'] ?? '',
                'estado_siesa' => $request->estado_general['nombre'] ?? '',
                'id_estado_pedido' => $request->estado_general['codigo'] ?? '',
                'prefijo' => 'PAM',
                'nota' => 'Nota',
                'orden_compra' => $orden_compra,
                'correo_cliente' =>  $request->creadoPor['correo'].','.$request->punto_envio['email'] ?? null,
                'estado' => '1',
                'id_sucursal' => $request->sucursal['id_sucursal'],
                'flete' => $request->totales['flete'],
                'observaciones' => $notasLimpias,
                'condicion_pago' => $request->sucursal['cond_pago'] ?? '',
                'fecha_pedido' => now(),
            ]);

            foreach ($request->productos as $producto) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'referencia' => $producto['referencia'],
                    'descripcion' => $producto['descripcion'] ?? '',
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_1'],
                    'descuento' => $producto['descuento'] ?? 0,
                    'subtotal' => $producto['subtotal'] ?? 0,
                ]);
            }

            if (!empty($request->punto_envio)) {
                DireccionEnvio::create([
                    'pedido_id' => $pedido->id,
                    'id_punto_envio' => $request->punto_envio['punto_envio_id'],
                    'direccion' => $request->punto_envio['direccion'],
                    'ciudad' => $request->punto_envio['ciudad'],
                    'departamento' => $request->punto_envio['departamento'],
                    'codigo_ciudad' => $request->punto_envio['cod_ciudad'] ?? '',
                    'codigo_departamento' => $request->punto_envio['cod_departamento'] ?? '',
                ]);
            }

            if (!empty($request->backorder)) {
                $backorder = \App\Models\Backorder::create([
                    'pedido_id' => $pedido->id,
                    'fecha_backorder' => now(),
                    'estado' => '1',
                    'estado_backorder' => 'pendiente',
                ]);

                foreach ($request->backorder as $producto) {
                    \App\Models\DetalleBackorder::create([
                        'backorder_id' => $backorder->id,
                        'referencia' => $producto['referencia'],
                        'descripcion' => $producto['descripcion'] ?? '',
                        'cantidad' => $producto['cantidad'],
                        'precio_unitario' => $producto['precio'] ?? 0,
                        'descuento' => $producto['descuento'] ?? 0,
                        'subtotal' => $producto['subtotal'] ?? 0,
                    ]);
                }
            }

            $pedidoXml = new PedidoXml();
            $resultadoXml = $pedidoXml->generarXml($pedido);


            if ($resultadoXml['status'] !== 'success') {

            $xmlResponse = $resultadoXml['xmlResult']->ImportarXMLResult->any;
            $xmlObject = simplexml_load_string($xmlResponse, "SimpleXMLElement", LIBXML_NOCDATA);
            $f_detalle = (string) $xmlObject->NewDataSet->Table->f_detalle;

                DB::rollBack();
                return response()->json([
                    'error' => 'Error al generar XML',
                    'mensaje' => 'Error al procesar el pedido en el ERP: '.$f_detalle,
                ], 500);
            }

            $info_pedido = Pedido::with('direccionEnvio')->find($pedido->id);
            $validacion_siesa = DB::connection('sqlsrv')->select("SELECT [f_id_tipo_docto] as prefijo, [f_nrodocto] as consecutivo FROM [BI_T430] WHERE [f_parametro_biable] = 3 AND [f_id_cia] = 3 AND [f_cliente_desp] = ? AND [f_cliente_fact_suc] = ? AND [f_punto_envio] = ? AND [f_orden_compra] = ?", [
                $info_pedido->nit,
                $info_pedido->id_sucursal,
                $info_pedido->direccionEnvio->id_punto_envio ?? null,
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

            $pedido_siesa = $prefijo_siesa.'-'.$consecutivo_siesa;
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

                foreach($detalles as  $detalle){
                    $subtotal_pedido += $detalle->valor_unitario * $detalle->cantidad;
                    $subtotal_descuento += $detalle->total_descuento;
                }


            DB::commit();

            try {
// correos
                $correos = array_map('trim', explode(',', $pedido->correo_cliente));

                Mail::to($correos)
                    ->send(new PedidoConfirmadoMail($encabezados, $detalles, $subtotal_pedido, $subtotal_descuento));

                return response()->json([
                    'success' => 'ok full',
                    'mensaje' => 'Se ha enviado el pedido, se ha creado en SIESA ' . $prefijo_siesa . '-' . $consecutivo_siesa. ' - Correos enviados.'
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'warning' => 'Pedido creado y guardado, pero falló el envío del correo',
                    'error' => $e->getMessage(),
                    'mensaje' => 'Se ha enviado el pedido, se ha creado en SIESA ' . $prefijo_siesa . '-' . $consecutivo_siesa. ' - No se ha enviado los correos.'
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

    public function guardarPedidoEspecial(Request $request)
    {
        $data = $request->validate([
            'cliente.nit' => 'required|string',
            'cliente.razon_social' => 'required|string',
            'cliente.email' => 'nullable|email',
            'sucursal.id_sucursal' => 'required|string',
            'sucursal.descripcion_sucursal' => 'required|string',
            'productos' => 'required|array',
            'productos.*.referencia' => 'required|string',
            'productos.*.cantidad' => 'required|numeric|min:1',
            'productos.*.precio_1' => 'required|numeric|min:0',
            'totales' => 'required|array',
            'creadoPor.nombre' => 'required|string',
            'creadoPor.codigo' => 'required|string',
        ]);

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

        $orden_compra = date('ymdHis');
        $notasOriginal = $request['notas'] ?? '';
        $notasLimpias = strtr($notasOriginal, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N'
        ]);

        DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'codigo_asesor' => $request->creadoPor['codigo'],
                'nombre_asesor' => $request->creadoPor['nombre'],
                'nit' => $request->cliente['nit'],
                'razon_social' => $request->cliente['razon_social'],
                'lista_precio' => $request->sucursal['lista_precio'] ?? '',
                'estado_siesa' => $request->estado_general['nombre'] ?? '',
                'id_estado_pedido' => $request->estado_general['codigo'] ?? '',
                'prefijo' => 'PES',
                'nota' => 'Negociación especial',
                'orden_compra' => $orden_compra,
                'correo_cliente' =>  $request->punto_envio['email'] ?? null,
                'estado' => '1',
                'id_sucursal' => $request->sucursal['id_sucursal'],
                'flete' => $request->totales['flete'],
                'observaciones' => $notasLimpias,
                'condicion_pago' => $request->sucursal['cond_pago'] ?? '',
                'fecha_pedido' => now(),
            ]);

            foreach ($request->productos as $producto) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'referencia' => $producto['referencia'],
                    'descripcion' => $producto['descripcion'] ?? '',
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_1'],
                    'descuento' => $producto['descuento'] ?? 0,
                    'subtotal' => $producto['subtotal'] ?? 0,
                ]);
            }

            if (!empty($request->punto_envio)) {
                DireccionEnvio::create([
                    'pedido_id' => $pedido->id,
                    'id_punto_envio' => $request->punto_envio['punto_envio_id'],
                    'direccion' => $request->punto_envio['direccion'],
                    'ciudad' => $request->punto_envio['ciudad'],
                    'departamento' => $request->punto_envio['departamento'],
                    'codigo_ciudad' => $request->punto_envio['cod_ciudad'] ?? '',
                    'codigo_departamento' => $request->punto_envio['cod_departamento'] ?? '',
                ]);
            }

            if (!empty($request->backorder)) {
                $backorder = \App\Models\Backorder::create([
                    'pedido_id' => $pedido->id,
                    'fecha_backorder' => now(),
                    'estado' => '1',
                    'estado_backorder' => 'pendiente',
                ]);

                foreach ($request->backorder as $producto) {
                    \App\Models\DetalleBackorder::create([
                        'backorder_id' => $backorder->id,
                        'referencia' => $producto['referencia'],
                        'descripcion' => $producto['descripcion'] ?? '',
                        'cantidad' => $producto['cantidad'],
                        'precio_unitario' => $producto['precio'] ?? 0,
                        'descuento' => $producto['descuento'] ?? 0,
                        'subtotal' => $producto['subtotal'] ?? 0,
                    ]);
                }
            }

            $pedido->save();

            DB::commit();

            //Correo pedidos especiales
            try{

                $correos = array_map('trim', explode(',', $pedido->correo_cliente));

                // el primer correo es el del asesor
                $correoAsesor = $correos[0];

                Mail::to(['auxcomercial@merlinrod.com','auxsistemas@merlinrod.com',$correoAsesor])
                    ->send(new PedidoEspecialMail($pedido));

                return response()->json([
                    'success' => 'ok full',
                    'mensaje' => 'Se ha enviado el pedido de negociacion especial para ser revisado'
                ], 200);
    
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Ocurrió un error al guardar el pedido',
                    'mensaje' => $e->getMessage()
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

}
