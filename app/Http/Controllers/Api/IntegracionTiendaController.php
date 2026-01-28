<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntegracionTiendaController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::connection('sqlsrv')
            ->select("SELECT rtrim(t120.f120_referencia) sku
                ,CONVERT(decimal(10), SUM(t400.f400_cant_existencia_1- t400.f400_cant_comprometida_1))  as disponible
                ,CONVERT(decimal(10), (PRECIOS1.PrecioImp*1.22)) 'price'
                FROM t400_cm_existencia t400
                    INNER JOIN t121_mc_items_extensiones t121
                        ON t400.f400_rowid_item_ext = t121.f121_rowid
                        AND t121.f121_ind_estado = 1
                    INNER JOIN t120_mc_items t120
                        ON t120.f120_rowid = t121.f121_rowid_item
                    LEFT JOIN t125_mc_items_criterios t125
                        ON t125.f125_rowid_item = t120.f120_rowid
                        AND PATINDEX('%[a-zA-Z]%', t120.f120_referencia) <= 0
                        AND t125.f125_id_cia= t120.f120_id_cia
                        AND t125.f125_id_plan='003'
                        AND t125.f125_id_cia = t120.f120_id_cia
                    INNER JOIN t106_mc_criterios_item_mayores t106
                        ON t106.f106_id = t125.f125_id_criterio_mayor
                        AND t106.f106_id_plan=t125.f125_id_plan
                        AND t106.f106_id_plan='003'
                        AND t106.f106_id_cia = t125.f125_id_cia
                        --Agregar marca en la creacion de pedidos
                        AND t106.f106_descripcion in ('RINOVA TIRES','BATERIAS RINOVA')
                LEFT JOIN
                (
                SELECT
                    t120.f120_id Items
                    ,rtrim(t120.f120_referencia) referencia
                    ,rtrim(t120.f120_descripcion) descripcion
                    ,rtrim(t120.f120_notas) notas
                    ,rtrim(t126.f126_id_unidad_medida) um
                    ,t126.f126_fecha_activacion fecha
                    ,COALESCE(t126.f126_precio,0) PrecioBase
                    ,CASE WHEN COALESCE(t037.f037_tasa,0)=0 THEN t126.f126_precio ELSE  t126.f126_precio*(1+(COALESCE(t037.f037_tasa,0)/100)) END PrecioImp
                    ,CASE WHEN COALESCE(t037.f037_tasa,0)=0 THEN t126.f126_precio ELSE (t126.f126_precio*(1+(COALESCE(t037.f037_tasa,0)/100)))- t126.f126_precio END Impuesto
                    FROM t126_mc_items_precios t126
                        INNER JOIN t120_mc_items t120 ON t120.f120_rowid = t126.f126_rowid_item
                        LEFT JOIN t114_mc_grupos_impo_impuestos t114 ON t120.f120_id_cia = t114.f114_id_cia
                        AND t114.f114_grupo_impositivo = t120.f120_id_grupo_impositivo AND t114.f114_id_clase_impuesto=1
                        AND t114.f114_ind_tipo_indicador=3
                        LEFT JOIN t037_mm_llaves_impuesto t037 ON t037.f037_id = t114.f114_id_llave_impuesto
                        AND t037.f037_id_cia = t114.f114_id_cia
                INNER JOIN
                (
                SELECT
                    t126.f126_rowid_item RowidItem
                    ,MAX(t126.f126_fecha_activacion) Fecha
                FROM t126_mc_items_precios t126
                WHERE t126.f126_id_cia = 3
                AND t126.f126_id_lista_precio= 001
                GROUP BY t126.f126_rowid_item
                )Act_Precio ON Act_Precio.RowidItem = t126.f126_rowid_item AND Act_Precio.Fecha = t126.f126_fecha_activacion
                WHERE t126.f126_id_cia = 3
                AND t126.f126_id_lista_precio= 001
                ) AS PRECIOS1 ON PRECIOS1.Items = t120.f120_id
                WHERE  t400.f400_id_cia= 3
                AND t400.f400_rowid_bodega in ('1062')
                GROUP BY t120.f120_id,
                t120.f120_referencia,
                t120.f120_id_unidad_orden,
                t120.f120_notas,
                t120.f120_descripcion,
                t120.f120_id_unidad_inventario,
                PRECIOS1.PrecioBase,
                PRECIOS1.PrecioImp,
                PRECIOS1.Impuesto,
                t106.f106_descripcion,
                t120.f120_fecha_creacion
                ORDER BY disponible");

        return response()->json([
            'productos' => $result,
        ]);
    }
}
