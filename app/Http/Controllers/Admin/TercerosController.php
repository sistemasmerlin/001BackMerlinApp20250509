<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TercerosController extends Controller
{
    public function index(){

        $result = DB::connection('sqlsrv')
            ->select("SELECT TOP 1000
                        t200.f200_rowid AS tercero_id,
                        t200.f200_razon_social,
                        t200.f200_nit,
                        RTRIM(t200.f200_dv_nit) AS f200_dv_nit,
                        t200.f200_id_tipo_ident,
                        t200.f200_ind_tipo_tercero,
                        t200.f200_apellido1,
                        t200.f200_apellido2,
                        t200.f200_nombres,
                        RTRIM(t201.f201_id_vendedor) AS f201_id_vendedor,
                        t201.f201_id_sucursal,
                        t201.f201_descripcion_sucursal,
                        t215.f215_id AS punto_envio_id,
                        t015.f015_id_pais,
                        t015.f015_id_depto,
                        t012.f012_descripcion,
                        t015.f015_id_ciudad,
                        t013.f013_descripcion,
                        t015.f015_direccion1,
                        t015.f015_email,
                        t015.f015_contacto,
                        t015.f015_telefono,
                        t015.f015_celular,
                        t201.f201_id_cond_pago,
                        t201.f201_cupo_credito,
                        t201.f201_id_lista_precio,
                        t206.f206_descripcion,

                        CASE
                            WHEN pedidos.pedidos IS NULL THEN '0'
                            ELSE pedidos.pedidos
                        END AS pedidos,

                        CASE
                            WHEN cartera.cartera IS NULL THEN '0'
                            ELSE cartera.cartera
                        END AS cartera,


                        CASE
                            WHEN pago.Dias_Prom_pago IS NULL THEN '0'
                            ELSE pago.Dias_Prom_pago
                        END AS pago,

                        t215.f215_descripcion AS descripcion_punto_envio,
                        ult_factura.f461_ts as ultima_factura, -- Aquí se une la última factura,
                        t015.f015_contacto AS nombre_contacto,
                        t015.f015_telefono,
                        t015.f015_celular,
                        t015.f015_email,
                        t015.f015_rowid AS contacto_id
                    FROM t200_mm_terceros t200
                    JOIN t201_mm_clientes t201
                        ON t200.f200_rowid = t201.f201_rowid_tercero
                    JOIN t215_mm_puntos_envio_cliente t215
                        ON t215.f215_rowid_tercero = t201.f201_rowid_tercero
                        AND t215.f215_id_sucursal = t201.f201_id_sucursal
                    LEFT JOIN t015_mm_contactos t015
                        ON t015.f015_rowid = t215.f215_rowid_contacto
                    OUTER APPLY (
                        SELECT TOP 1 f461_ts
                        FROM t461_cm_docto_factura_venta f
                        WHERE f.f461_rowid_tercero_fact = t200.f200_rowid
                        AND f.f461_id_cia = 3
                        AND f.f461_id_concepto = 501
                        ORDER BY f.f461_ts DESC
                    ) ult_factura

                    LEFT JOIN
                        (
                            SELECT
                                t200.f200_rowid rowid
                                ,t201.f201_id_sucursal Suc
                                ,SUM(v431_vlr_neto_pen_local) Pedidos
                            FROM	t430_cm_pv_docto t430
                                INNER JOIN t201_mm_clientes t201 ON t201.f201_rowid_tercero = t430.f430_rowid_tercero_fact
                                    AND t201.f201_id_sucursal = t430.f430_id_sucursal_fact
                                    AND t201.f201_id_cia = t430.f430_id_cia
                                INNER JOIN t200_mm_terceros t200 ON t200.f200_rowid = t201.f201_rowid_tercero
                                INNER JOIN v431 on  v431_rowid_pv_docto = f430_rowid
                                    AND v431_ind_estado <> 4
                            WHERE	f430_cond_pago_dias_vcto <> 0
                                AND f430_id_grupo_clase_docto = 502
                                AND f430_ind_estado IN (2,3) --2:Aprob  3:Comprometidos.
                                AND t200.f200_rowid= t201.f201_rowid_tercero
                                GROUP BY t200.f200_rowid,t201.f201_id_sucursal
                        )pedidos 
                    ON pedidos.rowid = t200.f200_rowid

                    LEFT JOIN
                        ( SELECT
                                    t200.f200_rowid rowid
                                    ,t201.f201_id_sucursal Suc
                                    ,ISNULL(SUM(f353_total_db - f353_total_cr),0) Cartera
                                FROM t353_co_saldo_abierto t353
                                    INNER JOIN t201_mm_clientes t201 ON t201.f201_rowid_tercero = t353.f353_rowid_tercero
                                        AND t201.f201_id_sucursal = t353.f353_id_sucursal AND t201.f201_id_cia =t353.f353_id_cia
                                    INNER JOIN t200_mm_terceros t200 ON t200.f200_rowid = t201.f201_rowid_tercero
                                    INNER JOIN t253_co_auxiliares t253 ON t253.f253_rowid = t353.f353_rowid_auxiliar
                                        AND t253.f253_ind_sa=1
                                WHERE t353.f353_id_cia= 3
                                    AND f353_fecha_cancelacion IS NULL
                                GROUP BY t200.f200_rowid,t201.f201_id_sucursal                        
                        )cartera 
                    ON cartera.rowid = t200.f200_rowid


                    LEFT JOIN
                        ( SELECT
                            t201.f201_id_sucursal id_sucursal
                            ,t201.f201_rowid_tercero rowid
                            ,avg(datediff(day, f353_fecha, f353_fecha_cancelacion_rec)) Dias_Prom_pago
                        FROM t353_co_saldo_abierto
                            INNER JOIN t253_co_auxiliares on f253_rowid = f353_rowid_auxiliar
                            AND f253_ind_sa = 1 and f253_ind_naturaleza = 1
                            INNER JOIN t201_mm_clientes t201 ON t201.f201_rowid_tercero = f353_rowid_tercero
                                AND t201.f201_id_sucursal = f353_id_sucursal
                            INNER JOIN t200_mm_terceros t200 ON t200.f200_rowid = t201.f201_rowid_tercero
                        WHERE	NOT f353_fecha_cancelacion IS NULL
                            --and		f353_fecha >= dateadd(year,-1,'2012-01-01')
                            AND f353_id_cia= 3
                            AND t200.f200_rowid= t201.f201_rowid_tercero
                            AND		NOT EXISTS
                            (	SELECT 1
                                FROM t354_co_mov_saldo_abierto
                                    INNER JOIN t350_co_docto_contable ON f350_rowid = f354_rowid_docto
                                    AND f350_id_clase_docto in (25,37,521,525,526,531,1030,1250)
                                WHERE f354_rowid_sa = f353_rowid
                                    AND f350_id_cia= 3
                            )
                            GROUP BY t201.f201_id_sucursal,t201.f201_rowid_tercero
                        ) pago 
                    ON pago.rowid = t201.f201_rowid_tercero AND pago.id_sucursal=t201.f201_id_sucursal

                    LEFT JOIN t012_mm_deptos AS t012
                    ON t012.f012_id = t015.f015_id_depto
                    AND t012.f012_id_pais = 169

                    LEFT JOIN t013_mm_ciudades AS t013
                    ON t013.f013_id = t015.f015_id_ciudad
                    AND t013.f013_id_depto = t015.f015_id_depto
                    AND t013.f013_id_pais = 169

                    LEFT JOIN t207_mm_criterios_clientes t207 
                    ON t207.f207_rowid_tercero = t201.f201_rowid_tercero
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
                    AND t215.f215_id_cia = 3
                    AND t201.f201_id_vendedor = 117
        ");

        // Agrupar resultados
        $clientes = [];
    
        foreach ($result as $row) {
            $terceroId = $row->tercero_id;
            $sucursalId = $row->f201_id_sucursal;
    
            // Inicializar cliente si no existe
            if (!isset($clientes[$terceroId])) {

                $fechaUltimaFactura = $row->ultima_factura
                ? \Carbon\Carbon::parse($row->ultima_factura)
                : null;

                $estadoUltimaVenta = 'Sin datos';
                $diasDesdeUltimaVenta = null;

                if ($fechaUltimaFactura) {
                    $diasDesdeUltimaVenta = $fechaUltimaFactura->diffInDays(now());
                    
                    if ($diasDesdeUltimaVenta <= 60) {
                        $estadoUltimaVenta = 'Venta corriente';
                    } elseif ($diasDesdeUltimaVenta <= 179) {
                        $estadoUltimaVenta = 'Venta vencida';
                    } else {
                        $estadoUltimaVenta = 'Se requiere actualizar documentos';
                    }
                }

                $clientes[$terceroId] = [
                    'tercero_id' => $terceroId,
                    'nit' => $row->f200_nit,
                    'razon_social' => $row->f200_razon_social,
                    'apellido1' => $row->f200_apellido1,
                    'apellido2' => $row->f200_apellido2,
                    'nombres' => $row->f200_nombres,
                    'digito_verificacion' => $row->f200_dv_nit,
                    'tipo_identificacion' => $row->f200_id_tipo_ident,
                    'tipo_tercero' => $row->f200_ind_tipo_tercero,
                    'ultima_factura' =>$row->ultima_factura,
                    'dias_sin_compra' => round($diasDesdeUltimaVenta),
                    'estado_ultima_venta' => $estadoUltimaVenta,
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
            'ciudades' => $ciudadesUnicas
        ]);
    }
    
}
