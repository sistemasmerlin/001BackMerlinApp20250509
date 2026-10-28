<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FacturasController extends Controller
{
    public function cargarFacturasAsesor($codigo_asesor)
    {

        $facturas = DB::connection('sqlsrv')
            ->select("SELECT TOP (100) [f_id_tipo_docto] as prefijo
            ,CASE 
                WHEN [f_id_tipo_docto]  = 'FVM' THEN 'FEDQ'
            ELSE 'CNC' end as prefijo_fe
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
            AND [f_id_tipo_docto] IN ('FVM','CNC')

            AND t210.f210_id = '$codigo_asesor'
            ORDER BY [f_nrodocto] DESC;");

        return response()->json([
            'facturas' => $facturas,
            'estado' => true,
            'mensaje' => 'Se retornan los pedidos que no llegaron a Siesa pero se crearon en la app',
        ], 200);
    }

    public function cargarFacturasCliente($nit)
    {

        $facturas = DB::connection('sqlsrv')
            ->select("SELECT TOP (20) [f_id_tipo_docto] as prefijo
            ,CASE 
                WHEN [f_id_tipo_docto]  = 'FVM' THEN 'FEDQ'
            ELSE 'CNC' end as prefijo_fe
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
            AND [f_id_tipo_docto] IN ('FVM','CNC')

            AND [f_cliente_fact] = '$nit'
            ORDER BY [f_nrodocto] DESC;");

        return response()->json([
            'facturas' => $facturas,
            'estado' => true,
            'mensaje' => 'Se retornan las facturas',
        ], 200);
    }

    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    public function consultarFactura($prefijo, $consecutivo)
    {
        try {

            $url = rtrim(config('services.numrot.url'), '/');

            // 1. AUTENTICACIÓN NUMROT
            $authResponse = Http::acceptJson()
                ->timeout(30)
                ->post($url . '/Auth/GetToken', [
                    'User' => config('services.numrot.user'),
                    'Password' => config('services.numrot.password'),
                ]);

            if (!$authResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al autenticar con Numrot',
                    'status' => $authResponse->status(),
                ], 502);
            }

            $authData = $authResponse->json();

            // Ajustar según la estructura real de respuesta de Numrot
            $token = data_get($authData, 'Token')
                ?? data_get($authData, 'token')
                ?? data_get($authData, 'access_token')
                ?? data_get($authData, 'AccessToken')
                ?? data_get($authData, 'Data.Token');

            if (!$token || !is_string($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el token en la respuesta de Numrot',
                ], 502);
            }

            // 2. CONFIGURAR FILTROS
            $esNota = strtoupper(trim($prefijo)) === 'CNC';

            $body = [
                'Filters' => [
                    'DocumentoTipoEstandar' => $esNota
                        ? 'nota'
                        : 'facturadeventa',

                    'EmpresaNit' => config('services.numrot.empresa_nit'),

                    'DocumentoNumeroCompleto' => $esNota
                        ? (string) $consecutivo
                        : $prefijo . $consecutivo,
                ],
            ];

            // 3. CONSULTAR FACTURA CON TOKEN
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(30)
                ->post($url . '/Documents/Find', $body);

            if (!$response->successful()) {
                Log::warning('Error consultando factura Numrot', [
                    'prefijo' => $prefijo,
                    'consecutivo' => $consecutivo,
                    'status' => $response->status(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error consultando documento en Numrot',
                    'status' => $response->status(),
                    'detalle' => $response->json(),
                ], 502);
            }

            // 4. DEVOLVER RESPUESTA ORIGINAL
            return response()->json($response->json());
        } catch (\Throwable $e) {

            Log::error('Error integración Numrot', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible consultar la factura',
            ], 500);
        }
    }

    // public function consultarFactura($prefijo, $consecutivo)
    // {


    //     if($prefijo == 'CNC'){
    //         $body = [
    //             "Key" => "12177dc3ec45485eada8014a6d1d32ca",
    //             "Secret" => "0abcfb0be01c27edc595ad962c1af3d4",
    //             "Filters" => [
    //                 "DocumentoTipoEstandar" => "nota",
    //                 "EmpresaNit" => "9013683375",
    //                 "DocumentoNumeroCompleto" => $consecutivo,
    //             ]
    //         ];
    //     }else{
    //         $body = [
    //             "Key" => "12177dc3ec45485eada8014a6d1d32ca",
    //             "Secret" => "0abcfb0be01c27edc595ad962c1af3d4",
    //             "Filters" => [
    //                 "DocumentoTipoEstandar" => "facturadeventa",
    //                 "EmpresaNit" => "9013683375",
    //                 "DocumentoNumeroCompleto" => $prefijo.$consecutivo,
    //             ]
    //         ];

    //     }

    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //     ])->post('https://www.numrot.net/NRWApi/api/Documents/Find', $body);

    //     return response()->json($response->json());
    // }

    public function cargarFacturasFechaCliente($codigo_asesor, $nit, $desde, $hasta)
    {

        $facturas = DB::connection('sqlsrv')
            ->select("SELECT TOP (100) [f_id_tipo_docto] as prefijo
            ,CASE 
                WHEN [f_id_tipo_docto]  = 'FVM' THEN 'FEDQ'
            ELSE 'CNC' end as prefijo_fe
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
            AND [f_id_tipo_docto] IN ('FVM', 'CNC')

            AND t210.f210_id = '$codigo_asesor'

            AND t200_cliente.f200_nit = '$nit'

            AND [f_fecha] BETWEEN '$desde' AND '$hasta'
            ORDER BY [f_nrodocto] DESC;");

        return response()->json([
            'facturas' => $facturas,
            'estado' => true,
            'mensaje' => 'Se retornan los pedidos que no llegaron a Siesa pero se crearon en la app',
        ], 200);
    }
}
