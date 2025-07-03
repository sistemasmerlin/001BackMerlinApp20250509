<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InteresesCartera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InteresesCarteraController extends Controller
{
    public function calcularInteresesDiarios()
    {
        //$existe = InteresesCartera::where('id','10')->get();
        // InteresesCartera::truncate();

        $fecha_hoy = date('Y-m-d');
        $fecha_ayer = date('Y-m-d', strtotime('-1 day'));

        // Verificar si ya se procesó la fecha de hoy
        //$existe = InteresesCartera::where('fecha_hoy', $fecha_hoy)->get();
        $existe = InteresesCartera::where('fecha_hoy', $fecha_hoy)->exists();

        if ($existe) {
            return redirect()->back()->with('error', 'Esta fecha ya fue procesada');
        }

        // Procesar registros de ayer
        $registros_ayer = InteresesCartera::where('fecha_hoy', $fecha_ayer)->get();

        foreach ($registros_ayer as $registro) {

            $data_validacion = DB::connection('sqlsrv')->select("
                SELECT 
                    f200_nit AS nit,
                    f200_razon_social AS razon_social,
                    f353_id_tipo_docto_cruce AS prefijo,
                    f353_consec_docto_cruce AS consecutivo,
                    f353_total_db AS valor_factura,
                    f353_valor_base AS valor_base,
                    f353_valor_impuesto AS impuestos,
                    f353_total_cr AS abono,
                    (f353_total_db - f353_total_cr) AS saldo,
                    f353_fecha_docto_cruce AS fecha_factura,
                    GETDATE() AS fecha_hoy,
                    DATEDIFF(DAY, f353_fecha_docto_cruce, GETDATE()) AS dias_transcurridos,
                    t201.f201_id_vendedor AS asesor,
                    f353_id_cond_pago AS condicion_pago
                FROM t353_co_saldo_abierto t353
                    INNER JOIN t201_mm_clientes t201 ON t201.f201_rowid_tercero = t353.f353_rowid_tercero
                    INNER JOIN t200_mm_terceros t200 ON t200.f200_rowid = t201.f201_rowid_tercero
                    INNER JOIN t253_co_auxiliares t253 ON t253.f253_rowid = t353.f353_rowid_auxiliar
                WHERE 
                    t353.f353_id_cia = 3
                    AND f353_fecha_cancelacion IS NULL
                    AND f353_id_tipo_docto_cruce = ?
                    AND f353_consec_docto_cruce = ?
                    AND DATEDIFF(DAY, f353_fecha_docto_cruce, GETDATE()) > 80
                    AND t253.f253_id = 13050501
                    AND (f353_total_db - f353_total_cr) > 0
            ", [$registro->prefijo, $registro->consecutivo]);

            // Si no hay saldo pendiente, marcamos como pagado
            if (empty($data_validacion)) {
                $registro->estado = 'Pagado';
                $registro->save();
            }
        }

        // Consultar facturas pendientes
        $facturas = DB::connection('sqlsrv')->select("SELECT 
                f200_nit AS nit,
                f200_razon_social AS razon_social,
                f353_id_tipo_docto_cruce AS prefijo,
                f353_consec_docto_cruce AS consecutivo,
                f353_total_db AS valor_factura,
                f353_valor_base AS valor_base,
                f353_valor_impuesto AS impuestos,
                f353_total_cr AS abono,
                (f353_total_db - f353_total_cr) AS saldo,
                f353_fecha_docto_cruce AS fecha_factura,
                GETDATE() AS fecha_hoy,
                DATEDIFF(DAY, f353_fecha_docto_cruce, GETDATE()) AS dias_transcurridos,
                t201.f201_id_vendedor AS asesor,
                f353_id_cond_pago AS condicion_pago
            FROM t353_co_saldo_abierto t353
                INNER JOIN t201_mm_clientes t201 ON t201.f201_rowid_tercero = t353.f353_rowid_tercero
                INNER JOIN t200_mm_terceros t200 ON t200.f200_rowid = t201.f201_rowid_tercero
                INNER JOIN t253_co_auxiliares t253 ON t253.f253_rowid = t353.f353_rowid_auxiliar
            WHERE 
                t353.f353_id_cia = 3
                AND f353_fecha_cancelacion IS NULL
                AND f353_id_tipo_docto_cruce IN ('FVM')
                AND DATEDIFF(DAY, f353_fecha_docto_cruce, GETDATE()) > 80
                AND t253.f253_id = 13050501
                AND (f353_total_db - f353_total_cr) > 0
        ");

        $porcentaje_interes = 2;

        foreach ($facturas as $factura) {

            $interes_diario = round(((($factura->saldo / 1.19) * ($porcentaje_interes / 100)) / 30), 2);
            $interes_acumulado = ($factura->dias_transcurridos - 80) * $interes_diario;

            $registro_existente = InteresesCartera::where('prefijo', $factura->prefijo)
                ->where('consecutivo', $factura->consecutivo)
                ->where('fecha_hoy', $fecha_hoy)
                ->first();

            if (!$registro_existente) {

                InteresesCartera::create([
                    'prefijo' => $factura->prefijo,
                    'consecutivo' => $factura->consecutivo,
                    'valor_base' => $factura->valor_base,
                    'impuestos' => $factura->impuestos,
                    'valor_factura' => $factura->valor_factura,
                    'abono' => $factura->abono,
                    'saldo' => $factura->saldo,
                    'fecha_factura' => $factura->fecha_factura,
                    'fecha_hoy' => $fecha_hoy,
                    'dias_transcurridos' => $factura->dias_transcurridos,
                    'asesor' => $factura->asesor,
                    'condicion_pago' => 'No pagado',
                    'valor_diario_interes' => $interes_diario,
                    'valor_acumulado_interes' => $interes_acumulado,
                    'razon_social' => $factura->razon_social,
                    'nit' => $factura->nit,
                    'estado' => 'Activo'
                ]);
            }
        }

        return redirect()->back()->with('success', 'Proceso de intereses ejecutado correctamente');
    }
}
