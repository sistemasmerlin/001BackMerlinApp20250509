<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BancoRecaudo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class RecaudoController extends Controller
{
    public function consultar(Request $request)
    {
        $nitCliente = $request->nit;
        $vendedor = $request->vendedor;
        
        return DB::connection('sqlsrv')->select("
            SELECT 
                t200.f200_rowid as rowid,
                f200_nit as nit,
                f200_razon_social as razon_social,
                f353_id_tipo_docto_cruce as factura,
                f353_consec_docto_cruce as cons_factura,
                f353_total_db AS valor_factura,
                f353_valor_base as valor_base,
                f353_total_cr AS valor_abonos,
                (f353_total_db - f353_total_cr) as saldo,
                f353_fecha_docto_cruce as fecha_factura,
                GETDATE() as fecha_hoy,
                DATEDIFF(DAY, f353_fecha_docto_cruce, GETDATE()) as dias_vcto,
                f353_id_cond_pago as cond_pag,
                t201.f201_id_sucursal as suc_cliente,
                f353_valor_impuesto as iva,

                CASE
                    WHEN (
                        SELECT f047_id_valor_tercero
                        FROM UnoEE.dbo.t047_mm_cliente_base_retencion
                        WHERE f047_rowid_tercero = t200.f200_rowid
                            AND f047_id_clase_retencion = 1
                            AND f047_id_cia = 3
                            AND f047_id_sucursal = t201.f201_id_sucursal
                    ) = 1 THEN 1
                    ELSE 0
                END AS esRetenedor,

                CASE
                    WHEN (
                        SELECT f047_id_valor_tercero
                        FROM UnoEE.dbo.t047_mm_cliente_base_retencion
                        WHERE f047_rowid_tercero = t200.f200_rowid
                            AND f047_id_clase_retencion = 2
                            AND f047_id_cia = 3
                            AND f047_id_sucursal = t201.f201_id_sucursal
                    ) = 1 THEN 1
                    ELSE 0
                END AS esReteIva,

                CASE
                    WHEN (
                        SELECT f047_id_valor_tercero
                        FROM UnoEE.dbo.t047_mm_cliente_base_retencion
                        WHERE f047_rowid_tercero = t200.f200_rowid
                            AND f047_id_clase_retencion = 2
                            AND f047_id_cia = 3
                            AND f047_id_sucursal = t201.f201_id_sucursal
                    ) = 1 THEN ((f353_valor_impuesto / 100) * 15)
                    ELSE 0
                END as rete_iva,

                (
                    SELECT f210_id
                    FROM UnoEE.dbo.t210_mm_vendedores
                    WHERE f210_rowid_tercero = f353_rowid_vend
                        AND f210_id_cia = 3
                ) as vendedor

            FROM UnoEE.dbo.t353_co_saldo_abierto t353

            INNER JOIN UnoEE.dbo.t201_mm_clientes as t201
                ON t201.f201_rowid_tercero = t353.f353_rowid_tercero
                AND t201.f201_id_sucursal = t353.f353_id_sucursal
                AND t201.f201_id_cia = t353.f353_id_cia

            INNER JOIN UnoEE.dbo.t200_mm_terceros as t200
                ON t200.f200_rowid = t201.f201_rowid_tercero

            INNER JOIN UnoEE.dbo.t253_co_auxiliares as t253
                ON t253.f253_rowid = t353.f353_rowid_auxiliar
                AND t253.f253_ind_sa = 1

            WHERE t353.f353_id_cia = 3
                AND f353_fecha_cancelacion IS NULL
                AND f353_id_tipo_docto_cruce = 'FVM'
                AND t200.f200_nit = ?
                AND (
                    SELECT f210_id
                    FROM UnoEE.dbo.t210_mm_vendedores
                    WHERE f210_rowid_tercero = f353_rowid_vend
                        AND f210_id_cia = 3
                ) = ?
        ", [$nitCliente, $vendedor]);
    }

    public function bancos()
    {
        $bancos = BancoRecaudo::query()
            ->where('estado', true)
            ->orderBy('descripcion_banco')
            ->get([
                'id',
                'id_banco',
                'descripcion_banco',
                'id_cuenta',
                'descripcion_cuenta',
                'numero_cuenta',
                'id_medio_pago',
                'tipo_cuenta',
            ]);

        return response()->json([
            'success' => true,
            'data' => $bancos,
        ]);
    }
}
