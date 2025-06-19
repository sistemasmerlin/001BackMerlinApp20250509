<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FacturasController extends Controller
{
    public function cargarFacturasAsesor($codigo_asesor){
        
        $facturas = DB::connection('sqlsrv')
        ->select("SELECT TOP (100) [f_id_tipo_docto] as prefijo
            ,CASE 
                WHEN [f_id_tipo_docto]  = 'FVM' THEN 'FEDQ'
            ELSE 'N/A' end as prefijo_fe
            ,[f_nrodocto] consecutivo
            ,[f_fecha] fecha
            ,[f_estado] estado
            ,[f_cliente_fact] nit
            ,t200_cliente.f200_razon_social razon_social
            ,[f_cliente_fact_suc] sucursal
            ,[f_notas] notas
            ,[f_pedidos] pedido
            ,[f_punto_envio] punto_envio
            ,[f_valor_bruto_local] valor_bruto
            ,[f_valor_dscto_local] valor_descuento
            ,[f_valor_subtotal] valor_subtotal
            ,[f_valor_imp_local] valor_iva
            ,[f_valor_neto_local] valor_local
            ,[f_desc_cond_pago] condicion_pago
            ,[f_vendedor] vendedor
            ,t210.f210_id id_vendedor
            FROM [BI_T461]

            LEFT JOIN [t200_mm_terceros] AS t200
            ON t200.f200_nit = [f_vendedor]
            AND t200.f200_id_cia = 3

            LEFT JOIN [t200_mm_terceros] AS t200_cliente
            ON t200_cliente.f200_nit = [f_cliente_fact]
            AND t200_cliente.f200_id_cia = 3

            LEFT JOIN [t210_mm_vendedores] AS t210
            ON t210.f210_rowid_tercero = t200.f200_rowid
            AND t210.f210_id_cia = 3

            WHERE [f_id_cia] = 3
            AND [f_parametro_biable] = 3
            AND [f_id_tipo_docto] = 'FVM'

            AND t210.f210_id = '$codigo_asesor'
            ORDER BY [f_nrodocto] DESC;");

        return response()->json([
            'facturas' => $facturas,
            'estado' => true,
            'mensaje' => 'Se retornan los pedidos que no llegaron a Siesa pero se crearon en la app',
        ], 200);
    }

    public function cargarFacturasCliente($nit){
        
        $facturas = DB::connection('sqlsrv')
        ->select("SELECT TOP (20) [f_id_tipo_docto] as prefijo
            ,CASE 
                WHEN [f_id_tipo_docto]  = 'FVM' THEN 'FEDQ'
            ELSE 'N/A' end as prefijo_fe
            ,[f_nrodocto] consecutivo
            ,[f_fecha] fecha
            ,[f_estado] estado
            ,[f_cliente_fact] nit
            ,t200_cliente.f200_razon_social razon_social
            ,[f_cliente_fact_suc] sucursal
            ,[f_notas] notas
            ,[f_pedidos] pedido
            ,[f_punto_envio] punto_envio
            ,[f_valor_bruto_local] valor_bruto
            ,[f_valor_dscto_local] valor_descuento
            ,[f_valor_subtotal] valor_subtotal
            ,[f_valor_imp_local] valor_iva
            ,[f_valor_neto_local] valor_local
            ,[f_desc_cond_pago] condicion_pago
            ,[f_vendedor] vendedor
            ,t210.f210_id id_vendedor
            FROM [BI_T461]

            LEFT JOIN [t200_mm_terceros] AS t200
            ON t200.f200_nit = [f_vendedor]
            AND t200.f200_id_cia = 3

            LEFT JOIN [t200_mm_terceros] AS t200_cliente
            ON t200_cliente.f200_nit = [f_cliente_fact]
            AND t200_cliente.f200_id_cia = 3

            LEFT JOIN [t210_mm_vendedores] AS t210
            ON t210.f210_rowid_tercero = t200.f200_rowid
            AND t210.f210_id_cia = 3

            WHERE [f_id_cia] = 3
            AND [f_parametro_biable] = 3
            AND [f_id_tipo_docto] = 'FVM'

            AND [f_cliente_fact] = '$nit'
            ORDER BY [f_nrodocto] DESC;");

        return response()->json([
            'facturas' => $facturas,
            'estado' => true,
            'mensaje' => 'Se retornan las facturas',
        ], 200);
    }

    public function consultarFactura($prefijo, $consecutivo)
    {
        $body = [
            "Key" => "12177dc3ec45485eada8014a6d1d32ca",
            "Secret" => "0abcfb0be01c27edc595ad962c1af3d4",
            "Filters" => [
                "DocumentoTipoEstandar" => "facturadeventa",
                "EmpresaNit" => "9013683375",
                "DocumentoNumeroCompleto" => $prefijo.$consecutivo,
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://www.numrot.net/NRWApi/api/Documents/Find', $body);

        return response()->json($response->json());
    }

}
