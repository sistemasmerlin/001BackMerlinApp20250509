<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductosController extends Controller
{
    public function index(Request $request)
    {

        $result = DB::connection('sqlsrv')
            ->select("SELECT t120.f120_id item
                ,rtrim(t120.f120_referencia) referencia
                ,CONCAT('storage/fichas_tecnicas/', RTRIM(t120.f120_referencia), '.pdf') AS ficha_tecnica
                ,rtrim(t120.f120_descripcion) descripcion
                ,REPLACE(REPLACE(REPLACE(REPLACE(t120.f120_descripcion, '/', ' '), '-', ' '), 'X', ' '), '=', ' ') descripcion_sin_espacios
                ,rtrim(t120.f120_id_unidad_inventario) und_inventario
                ,rtrim(t120.f120_id_unidad_orden) und_medida
                ,CASE
                    WHEN rtrim(t120.f120_referencia) = '510010901' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510011000' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510011001' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510011100' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510011102' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510011201' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510011202' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510011300' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510011301' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510011700' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510012401' THEN '1'
                    WHEN rtrim(t120.f120_referencia) = '510012501' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '510022303' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '511010100' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '511010101' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '511010800' THEN '50'
                    WHEN rtrim(t120.f120_referencia) = '511010900' THEN '50'
                    WHEN rtrim(t120.f120_referencia) = '511011000' THEN '50'
                    WHEN rtrim(t120.f120_referencia) = '511011100' THEN '10'
                    WHEN rtrim(t120.f120_referencia) = '511011300' THEN '10'
                ELSE '1' END AS minimo_venta
                ,CASE
                    WHEN rtrim(t106.f106_descripcion) = 'RINOVA LIGHTING LED' THEN 'RINOVA LIGHTING'
                    WHEN rtrim(t120.f120_referencia) = '101990000' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '101990001' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '101990002' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '101990003' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '101990004' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '101990005' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '101990006' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '101990007' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '511990000' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '511990001' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '511990002' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '101990008' THEN 'COMBOS'
                    WHEN rtrim(t120.f120_referencia) = '101990009' THEN 'COMBOS'
                    WHEN rtrim(t106.f106_descripcion) = 'PIRELLI RADIAL' THEN 'PIRELLI'
                    WHEN rtrim(t120.f120_referencia) = '407500000' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500001' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500002' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500003' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500004' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500005' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500006' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500007' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500008' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500009' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500010' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500011' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500012' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500013' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500014' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500015' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500016' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500017' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500020' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500021' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407500022' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510000' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510001' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510002' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510003' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510004' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510005' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510006' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510007' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510008' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510009' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '407510011' THEN 'CUNAS RNV EN PROMOCION'
                    WHEN rtrim(t120.f120_referencia) = '316426300' THEN 'WDT BIKE'
                    WHEN rtrim(t120.f120_referencia) = '316427300' THEN 'WDT BIKE'
                    WHEN rtrim(t120.f120_referencia) = '316429200' THEN 'WDT BIKE'
                    WHEN rtrim(t120.f120_referencia) = '316429300' THEN 'WDT BIKE'
                ELSE
                rtrim(t106.f106_descripcion) END AS marca
                ,CASE 
                    WHEN rtrim(t106.f106_descripcion) = 'HAKUBA - ARMOR - WDT' THEN 'Llantas'
                    WHEN rtrim(t106.f106_descripcion) = 'WDT' THEN 'Llantas'
                    WHEN rtrim(t106.f106_descripcion) = 'KOYO' THEN 'Rodamientos'
                    WHEN rtrim(t106.f106_descripcion) = 'RNV' THEN 'Rodamientos'
                    WHEN rtrim(t106.f106_descripcion) = 'RINOVA TIRES' THEN 'Llantas'
                    WHEN rtrim(t106.f106_descripcion) = 'NARVA' THEN 'Iluminacion'
                    WHEN rtrim(t106.f106_descripcion) = 'RINOVA - GOOD TUBE' THEN 'Neumaticos'
                    WHEN rtrim(t106.f106_descripcion) = 'PFI' THEN 'Rodamientos'
                    WHEN rtrim(t106.f106_descripcion) = 'PIRELLI' THEN 'Llantas'
                    WHEN rtrim(t106.f106_descripcion) = 'PIRELLI RADIAL' THEN 'Llantas'
                    WHEN rtrim(t106.f106_descripcion) = 'BATERIAS RINOVA' THEN 'Baterias'
                    WHEN rtrim(t106.f106_descripcion) = 'RINOVA LIGHTING' THEN 'Iluminacion'
                    WHEN rtrim(t106.f106_descripcion) = 'CST TIRES' THEN 'Llantas'
                    WHEN rtrim(t106.f106_descripcion) = 'CST ATV' THEN 'Llantas'
                    WHEN rtrim(t106.f106_descripcion) = 'WDT E-SCOOTER' THEN 'Llantas'
                    WHEN rtrim(t106.f106_descripcion) = 'WDT BIKE' THEN 'Llantas'
                    WHEN rtrim(t106.f106_descripcion) = 'WDT TUBE' THEN 'Neumaticos'
                    WHEN rtrim(t106.f106_descripcion) = 'RINOVA LIGHTING LED' THEN 'Iluminacion'
                    WHEN rtrim(t106.f106_descripcion) = 'FORERUNNER' THEN 'Llantas'
                ELSE 'n/a'END AS categoria
                ,rtrim(t120.f120_notas) notas
                ,CASE
                    WHEN DATEDIFF(DAY, f120_fecha_creacion, GETDATE()) <= 30 THEN 'Si'
                    ELSE 'No' END as nuevo
                ,CONVERT(decimal(10), SUM(t400.f400_cant_existencia_1- t400.f400_cant_comprometida_1))  as disponible
                ,CASE
                    WHEN CONVERT(decimal(10), SUM(t400.f400_cant_existencia_1- t400.f400_cant_comprometida_1)) <=0 THEN 'agotado'
                    WHEN CONVERT(decimal(10), SUM(t400.f400_cant_existencia_1- t400.f400_cant_comprometida_1)) >0 THEN 'disponible'
                ELSE 'Sin estado' END AS estado
                ,SUM(t400.f400_cant_existencia_1) existencia
                ,CONVERT(decimal(10), PRECIOS1.PrecioBase) 'precio_1'
                ,CONVERT(decimal(10), PRECIOS1.Impuesto) 'precio_1_iva'
                ,CONVERT(decimal(10), PRECIOS1.PrecioImp) 'precio_1_mas_iva'
                ,CONVERT(decimal(10), (PRECIOS1.PrecioBase*1.09)) 'precio_202'
                ,CONVERT(decimal(10), (PRECIOS1.Impuesto*1.09)) 'precio_202_iva'
                ,CONVERT(decimal(10), (PRECIOS1.PrecioImp*1.09)) 'precio_202_mas_iva'
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
                        AND t106.f106_descripcion in ('HAKUBA - ARMOR - WDT',
                            'WDT',
                            'KOYO',
                            'RNV',
                            'RINOVA TIRES',
                            'NARVA',
                            'RINOVA - GOOD TUBE',
                            'PFI',
                            'PIRELLI',
                            'PIRELLI RADIAL',
                            'BATERIAS RINOVA',
                            'RINOVA LIGHTING',
                            'CST TIRES',
                            'WDT E-SCOOTER',
                            'WDT BIKE',
                            'WDT TUBE',
                            'RINOVA LIGHTING LED',
                            'FORERUNNER')
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
                ORDER BY 1");

        return response()->json([
            'productos' => $result,
        ]);
    }
}
