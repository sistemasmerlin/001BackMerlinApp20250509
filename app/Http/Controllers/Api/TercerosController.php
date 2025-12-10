<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TercerosController extends Controller
{
    public function index(Request $request, $id){


        $result = DB::connection('sqlsrv')
        ->select("SELECT TOP 1000
            t200.f200_rowid AS tercero_id,
            t200.f200_nit,
            RTRIM(t200.f200_dv_nit) AS f200_dv_nit,
            t201.f201_id_sucursal,
            t215.f215_id AS punto_envio_id,

            MAX(t200.f200_razon_social) AS f200_razon_social,
            MAX(t200.f200_id_tipo_ident) AS f200_id_tipo_ident,
            MAX(t200.f200_ind_tipo_tercero) AS f200_ind_tipo_tercero,
            MAX(t200.f200_apellido1) AS f200_apellido1,
            MAX(t200.f200_apellido2) AS f200_apellido2,
            MAX(t200.f200_nombres) AS f200_nombres,
            MAX(t200.f200_nombre_est) AS f200_nombre_est,
            MAX(RTRIM(t201.f201_id_vendedor)) AS f201_id_vendedor,
            MAX(t201.f201_descripcion_sucursal) AS f201_descripcion_sucursal,

            MAX(t015.f015_id_pais) AS f015_id_pais,
            MAX(t015.f015_id_depto) AS f015_id_depto,
            MAX(t012.f012_descripcion) AS f012_descripcion,
            MAX(t015.f015_id_ciudad) AS f015_id_ciudad,
            MAX(t013.f013_descripcion) AS f013_descripcion,
            MAX(t015.f015_direccion1) AS f015_direccion1,
            MAX(t015.f015_email) AS f015_email,
            MAX(t015.f015_contacto) AS f015_contacto,
            MAX(t015.f015_telefono) AS f015_telefono,
            MAX(t015.f015_celular) AS f015_celular,

            MAX(t201.f201_id_cond_pago) AS f201_id_cond_pago,
            MAX(t201.f201_cupo_credito) AS f201_cupo_credito,
            MAX(t201.f201_id_lista_precio) AS f201_id_lista_precio,
            MAX(t206.f206_descripcion) AS f206_descripcion,

            (  SELECT 
                COUNT(*) AS TotalRegistros
            FROM [UnoEE].[dbo].[t353_co_saldo_abierto]
            WHERE f353_rowid_tercero = t200.f200_rowid 
            AND [f353_fecha_cancelacion] IS NULL
            AND f353_id_tipo_docto_cruce = 'FVM'
            AND DATEDIFF(DAY, [f353_fecha], GETDATE()) > 60) as facturasVencidas,

            SUM(CAST(pedidos.pedidos AS FLOAT)) AS pedidos,
            SUM(CAST(cartera.cartera AS FLOAT)) AS cartera,
            AVG(CAST(pago.Dias_Prom_pago AS FLOAT)) AS pago,

            MAX(t215.f215_descripcion) AS descripcion_punto_envio,
            MAX(ult_factura.f461_ts) AS ultima_factura,
            MAX(t015.f015_rowid) AS contacto_id
        FROM t200_mm_terceros t200
        JOIN t201_mm_clientes t201
            ON t200.f200_rowid = t201.f201_rowid_tercero
        JOIN t215_mm_puntos_envio_cliente t215
            ON t215.f215_rowid_tercero = t201.f201_rowid_tercero AND t215.f215_id_sucursal = t201.f201_id_sucursal
        LEFT JOIN t015_mm_contactos t015
            ON t015.f015_rowid = t215.f215_rowid_contacto
        OUTER APPLY (
            SELECT TOP 1 f461_ts
            FROM t461_cm_docto_factura_venta f
            WHERE f.f461_rowid_tercero_fact = t200.f200_rowid
                AND f.f461_id_cia = 3 AND f.f461_id_concepto = 501
                AND f.f461_num_docto_referencia IS NOT NULL
                AND LTRIM(RTRIM(f.f461_num_docto_referencia)) <> ''
            ORDER BY f.f461_ts DESC
        ) ult_factura
        LEFT JOIN (
            SELECT t200.f200_rowid AS rowid, t201.f201_id_sucursal AS sucursal, SUM(v431_vlr_neto_pen_local) AS pedidos
            FROM t430_cm_pv_docto t430
            INNER JOIN t201_mm_clientes t201 ON t201.f201_rowid_tercero = t430.f430_rowid_tercero_fact
                AND t201.f201_id_sucursal = t430.f430_id_sucursal_fact
                AND t201.f201_id_cia = t430.f430_id_cia
            INNER JOIN t200_mm_terceros t200 ON t200.f200_rowid = t201.f201_rowid_tercero
            INNER JOIN v431 ON v431_rowid_pv_docto = f430_rowid AND v431_ind_estado <> 4
            WHERE f430_cond_pago_dias_vcto <> 0
                AND f430_id_grupo_clase_docto = 502
                AND f430_ind_estado IN (2, 3)
            GROUP BY t200.f200_rowid, t201.f201_id_sucursal
        ) pedidos ON pedidos.rowid = t200.f200_rowid AND pedidos.sucursal = t201.f201_id_sucursal
        LEFT JOIN (
            SELECT t200.f200_rowid AS rowid, t201.f201_id_sucursal AS sucursal,
                ISNULL(SUM(f353_total_db - f353_total_cr), 0) AS cartera
            FROM t353_co_saldo_abierto t353
            INNER JOIN t201_mm_clientes t201 ON t201.f201_rowid_tercero = t353.f353_rowid_tercero
                AND t201.f201_id_sucursal = t353.f353_id_sucursal AND t201.f201_id_cia = t353.f353_id_cia
            INNER JOIN t200_mm_terceros t200 ON t200.f200_rowid = t201.f201_rowid_tercero
            INNER JOIN t253_co_auxiliares t253 ON t253.f253_rowid = t353.f353_rowid_auxiliar AND t253.f253_ind_sa = 1
            WHERE t353.f353_id_cia = 3 AND f353_fecha_cancelacion IS NULL
            GROUP BY t200.f200_rowid, t201.f201_id_sucursal
        ) cartera ON cartera.rowid = t200.f200_rowid AND cartera.sucursal = t201.f201_id_sucursal
        LEFT JOIN (
            SELECT t201.f201_rowid_tercero AS rowid, t201.f201_id_sucursal AS sucursal,
                AVG(DATEDIFF(DAY, f353_fecha, f353_fecha_cancelacion_rec)) AS Dias_Prom_pago
            FROM t353_co_saldo_abierto
            INNER JOIN t253_co_auxiliares ON f253_rowid = f353_rowid_auxiliar
                AND f253_ind_sa = 1 AND f253_ind_naturaleza = 1
            INNER JOIN t201_mm_clientes t201 ON t201.f201_rowid_tercero = f353_rowid_tercero
                AND t201.f201_id_sucursal = f353_id_sucursal
            INNER JOIN t200_mm_terceros t200 ON t200.f200_rowid = t201.f201_rowid_tercero
            WHERE f353_fecha_cancelacion IS NOT NULL
                AND f353_id_cia = 3
                AND NOT EXISTS (
                    SELECT 1 FROM t354_co_mov_saldo_abierto
                    INNER JOIN t350_co_docto_contable ON f350_rowid = f354_rowid_docto
                        AND f350_id_clase_docto IN (25, 37, 521, 525, 526, 531, 1030, 1250)
                    WHERE f354_rowid_sa = f353_rowid AND f350_id_cia = 3
                )
            GROUP BY t201.f201_rowid_tercero, t201.f201_id_sucursal
        ) pago ON pago.rowid = t201.f201_rowid_tercero AND pago.sucursal = t201.f201_id_sucursal
        LEFT JOIN t012_mm_deptos t012 ON t012.f012_id = t015.f015_id_depto AND t012.f012_id_pais = 169
        LEFT JOIN t013_mm_ciudades t013 ON t013.f013_id = t015.f015_id_ciudad
            AND t013.f013_id_depto = t015.f015_id_depto AND t013.f013_id_pais = 169
        LEFT JOIN t207_mm_criterios_clientes t207 ON t207.f207_rowid_tercero = t201.f201_rowid_tercero
            AND t207.f207_id_sucursal = t201.f201_id_sucursal
            AND t207.f207_id_cia = t201.f201_id_cia
            AND t207.f207_id_plan_criterios = '005'
                            LEFT JOIN  t206_mm_criterios_mayores t206 
                            ON t206.f206_id_plan = t207.f207_id_plan_criterios
                            AND t206.f206_id_cia = t207.f207_id_cia 
                            AND t206.f206_id = t207.f207_id_criterio_mayor
        WHERE t200.f200_ind_cliente = 1
            AND t200.f200_ind_estado = 1
            AND t200.f200_id_cia = 3
            AND t201.f201_ind_estado_activo = 1
            AND t201.f201_id_cia = 3
            AND t215.f215_ind_estado = 1
            AND f201_id_sucursal != '001'
            AND t215.f215_id_cia = 3
            AND t201.f201_id_vendedor = '$id'
        -- AND t200.f200_nit = '805020771'
        GROUP BY
            t200.f200_rowid,
            t200.f200_nit,
            t200.f200_dv_nit,
            t201.f201_id_sucursal,
            t215.f215_id");

        $clientes = [];


        $clientesCredito = 0;
    
        foreach ($result as $row) {
            $terceroId = $row->tercero_id;
            $sucursalId = $row->f201_id_sucursal;

            if($row->f201_id_cond_pago == '10D' OR $row->f201_id_cond_pago == '30D' OR $row->f201_id_cond_pago == '30E'){
                $clientesCredito ++;
            }

    
            // Inicializar cliente si no existe
            if (!isset($clientes[$terceroId])) {

                $fechaUltimaFactura = $row->ultima_factura
                ? \Carbon\Carbon::parse($row->ultima_factura)
                : null;

                $estadoUltimaVenta = 'Sin venta';
                $diasDesdeUltimaVenta = 0;

                if ($fechaUltimaFactura) {
                    $diasDesdeUltimaVenta = round($fechaUltimaFactura->diffInDays(now()));

                    if ($diasDesdeUltimaVenta <= 60) {
                        $estadoUltimaVenta = 'Venta corriente';
                    } elseif ($diasDesdeUltimaVenta <= 180) {
                        $estadoUltimaVenta = 'Venta vencida';
                    } else {
                        $estadoUltimaVenta = 'Solicitar documentación';
                    }
                }

                $clientes[$terceroId] = [
                    'tercero_id' => $terceroId,
                    'nit' => $row->f200_nit,
                    'facturas_vencidas' => $row->facturasVencidas,
                    'razon_social' => $row->f200_razon_social,
                    'nombre_establecimiento' => $row->f200_nombre_est,
                    'apellido1' => $row->f200_apellido1,
                    'apellido2' => $row->f200_apellido2,
                    'nombres' => $row->f200_nombres,
                    'digito_verificacion' => $row->f200_dv_nit,
                    'tipo_identificacion' => $row->f200_id_tipo_ident,
                    'tipo_tercero' => $row->f200_ind_tipo_tercero,
                    'ultima_factura' =>$row->ultima_factura,
                    'dias_sin_compra' => round($diasDesdeUltimaVenta),
                    'estado_ultima_venta' => $estadoUltimaVenta,
                    'dias_desde_ultima_venta' => $diasDesdeUltimaVenta,
                    'sucursales' => []
                ];
            }
    
            // Buscar sucursal actual
            $sucursalKey = $row->f201_id_sucursal;
            $sucursales =& $clientes[$terceroId]['sucursales'];
    
            if (!isset($sucursales[$sucursalKey])) {
                $sucursales[$sucursalKey] = [
                    'id_sucursal' => $row->f201_id_sucursal,
                    'descripcion_sucursal' => $row->f201_descripcion_sucursal,
                    'id_vendedor' => $row->f201_id_vendedor,
                    'cartera' => $row->cartera,
                    // 'remisiones' => $row->remisiones,
                    'pedidos' => $row->pedidos,
                    'promedio_dias_pago' => $row->pago,
                    'cond_pago' => $row->f201_id_cond_pago,
                    'cupo_credito' => $row->f201_cupo_credito,
                    'lista_precio' => $row->f201_id_lista_precio,
                    'categoria' => $row->f206_descripcion,
                    'puntos_envio' => []
                ];
            }

            $sucursales[$sucursalKey]['puntos_envio'][] = [
                'punto_envio_id' => $row->punto_envio_id,
                'descripcion_punto_envio' => $row->descripcion_punto_envio,
                'contacto' => $row->contacto_id,
                'cod_departamento' => $row->f015_id_depto,
                'departamento' => $row->f012_descripcion,
                'cod_ciudad' => $row->f015_id_ciudad,
                'ciudad' => $row->f013_descripcion,
                'direccion' => $row->f015_direccion1,
                'email' => $row->f015_email,
                'contacto' => $row->f015_contacto,
                'celular' => $row->f015_celular,
                'telefono' => $row->f015_telefono,
            ];
        }

        $ciudadesUnicas = [];

        foreach ($result as $row) {
            $codigoDepto = $row->f015_id_depto;
            $nombreDepto = $row->f012_descripcion;
            $codigoCiudad = $row->f015_id_ciudad;
            $nombreCiudad = $row->f013_descripcion;

            // Clave compuesta única
            $clave = $codigoDepto . '-' . $codigoCiudad;

            if (!isset($ciudadesUnicas[$clave])) {
                $ciudadesUnicas[$clave] = [
                    'codigo_departamento' => $codigoDepto,
                    'departamento' => $nombreDepto,
                    'codigo_ciudad' => $codigoCiudad,
                    'ciudad' => $nombreCiudad
                ];
            }
        }
    
        $ciudadesUnicas = array_values($ciudadesUnicas);

        // Reindexar los arrays
        $clientes = array_values(array_map(function ($cliente) {
            $cliente['sucursales'] = array_values($cliente['sucursales']);
            return $cliente;
        }, $clientes));
    
        return response()->json([
            'clientes' => $clientes,
            'clientes_credito' => $clientesCredito,
            'ciudades' => $ciudadesUnicas
        ]);
    }

    public function consultarTercero(Request $request){

        $productos = DB::connection('sqlsrv')
        ->select("SELECT  
            bi.f_ref_item AS referencia,
            i.f120_descripcion AS descripcion,
            m.f106_descripcion AS marca,
            CONVERT(decimal(10, 2), SUM(bi.f_cant_base)) AS cantidad,
            CONVERT(decimal(10, 2), SUM(bi.f_valor_sub_local)) AS subtotal
        FROM 
            [UnoEE].[dbo].[BI_T461_1] bi
        INNER JOIN [t120_mc_items] i 
            ON i.f120_referencia = bi.f_ref_item 
            AND i.f120_id_cia = bi.f_id_cia
        INNER JOIN [t125_mc_items_criterios] ic 
            ON ic.f125_rowid_item = i.f120_rowid 
            AND ic.f125_id_cia = '3' 
            AND ic.f125_id_plan = '003'
        INNER JOIN [t105_mc_criterios_item_planes] p 
            ON p.f105_id = ic.f125_id_plan 
            AND p.f105_id_cia = '3'
        INNER JOIN [t106_mc_criterios_item_mayores] m 
            ON m.f106_id = ic.f125_id_criterio_mayor 
            AND m.f106_id_plan = '003' 
            AND m.f106_id_cia = '3'
        WHERE 
            bi.f_parametro_biable = 3
            AND bi.f_id_cia = 3
            AND bi.f_id_tipo_docto = 'FVM'
            AND bi.f_cliente_fact = '$request->nit'
            AND YEAR(bi.f_fecha) >= YEAR(GETDATE()) - 3
            --AND m.f106_descripcion NOT IN ('ZFLETE', 'NO APLICA')
        GROUP BY 
            bi.f_ref_item,
            i.f120_descripcion,
            m.f106_descripcion
        ORDER BY 
           bi.f_ref_item DESC;");

        $ventas = \DB::connection('sqlsrv')
        ->select("SELECT  YEAR(f_fecha) as periodo,                             
            -- ,[f_periodo] as periodo
            SUM([f_valor_subtotal]) as subtotal
            ,SUM([f_valor_imp_local]) as impuestos
            ,SUM([f_valor_neto_local]) as neto
            FROM [UnoEE].[dbo].[BI_T461]
            WHERE f_id_cia = 3
            AND [f_parametro_biable] = 3
            AND [f_cliente_fact] = '$request->nit'
            AND [f_id_tipo_docto] in ('FVM','CNC')
            --AND f_fecha
            AND [f_co] = 003
            AND f_estado1 = 1
            GROUP BY YEAR(f_fecha)
            ORDER BY periodo DESC");

        return $data = [
            'productos' => $productos,
            'ventas' => $ventas,
        ];
    }

}
