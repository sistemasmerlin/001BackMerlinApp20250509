<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Xml\Pedido as PedidoXml;
use App\Mail\PedidoConfirmadoMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function enviarPedido($id)
    {
        DB::beginTransaction();

        try {
            $pedido = Pedido::findOrFail($id); // 🔧 CORREGIDO: Obtener un solo objeto
            $pedido->prefijo = 'PAM';
            $pedidoXml = new PedidoXml();
            $resultadoXml = $pedidoXml->generarXml($pedido);

            $pedido->nota = $resultadoXml['status'] === 'success' ? 'Creado en Siesa' : 'No creado en Siesa';
            $pedido->prefijo = 'PES';

            $pedido->save();

            if ($resultadoXml['status'] !== 'success') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Error al generar XML',
                    'mensaje' => $resultadoXml['mensaje'] ?? 'Error no especificado'
                ], 500);
            }

            // Validación en Siesa
            $info_pedido = Pedido::with('direccionEnvio')->find($pedido->id);

            $validacion_siesa = DB::connection('sqlsrv')->select("
                SELECT f_id_tipo_docto as prefijo, f_nrodocto as consecutivo 
                FROM BI_T430 
                WHERE f_parametro_biable = 3 AND f_id_cia = 3 
                AND f_cliente_desp = ? AND f_cliente_fact_suc = ? AND f_punto_envio = ? AND f_orden_compra = ?", [
                $info_pedido->nit,
                $info_pedido->id_sucursal,
                $info_pedido->direccionEnvio->id_punto_envio ?? null,
                $info_pedido->orden_compra
            ]);

            if (empty($validacion_siesa)) {
                DB::rollBack();
                return response()->json(['error' => 'No se ha creado el pedido en Siesa'], 500);
            }

            if ($validacion_siesa) {
                foreach ($validacion_siesa as $validar) {
                    $prefijo_siesa = $validar->prefijo;
                    $consecutivo_siesa = $validar->consecutivo;
                }


                $pedido = Pedido::findOrFail($id); // 🔧 CORREGIDO: Obtener un solo objeto
                $pedido->nota = $prefijo_siesa.'-'.$consecutivo_siesa;
                $pedido->prefijo = 'PES';
                $pedido->save();
            }

            // Datos de encabezado
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

            foreach ($detalles as $detalle) {
                $subtotal_pedido += $detalle->valor_unitario * $detalle->cantidad;
                $subtotal_descuento += $detalle->total_descuento;
            }

            $pedido->nota = $resultadoXml['status'] === 'success' ? $prefijo_siesa.'-'.$consecutivo_siesa : 'No creado en Siesa';
            $pedido->save();

            DB::commit();

            try {
                // Correos del cliente (pueden venir separados por coma)
                $correosCliente = array_filter(array_map('trim', explode(',', $pedido->correo_cliente ?? '')));
                $correosClienteValidos = array_filter($correosCliente, fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

                // Correo del asesor
                $correoAsesor = trim($pedido->correo_asesor ?? '');
                $correoAsesorValido = filter_var($correoAsesor, FILTER_VALIDATE_EMAIL) ? $correoAsesor : null;

                if (!empty($correosClienteValidos)) {
                    $mail = Mail::to($correosClienteValidos);

                    if ($correoAsesorValido) {
                        $mail->cc($correoAsesorValido);
                    }

                    $mail->send(new PedidoConfirmadoMail($encabezados, $detalles, $subtotal_pedido, $subtotal_descuento));
                }

                /* return response()->json([
                    'success' => 'ok full',
                    'mensaje' => 'Se ha enviado el pedido y fue creado en SIESA ' . $prefijo_siesa . '-' . $consecutivo_siesa
                ], 200); */

                return back()->with('success', 'Se ha enviado el pedido y fue creado en SIESA ' . $prefijo_siesa . '-' . $consecutivo_siesa);
            } catch (\Exception $e) {
                /* return response()->json([
                    'warning' => 'Pedido creado, pero falló el envío del correo',
                    'error' => $e->getMessage(),
                    'mensaje' => 'Se ha creado en SIESA ' . $prefijo_siesa . '-' . $consecutivo_siesa . ' - No se enviaron correos.'
                ], 200); */

                return back()->with([
                    'warning' => 'Pedido creado, pero falló el envío del correo',
                    'error' => $e->getMessage(),
                    'mensaje' => 'Se ha creado en SIESA ' . $prefijo_siesa . '-' . $consecutivo_siesa . ' - No se enviaron correos.'
                ]);
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
