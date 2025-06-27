<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteresesCartera extends Model
{
    protected $table = 'intereses_cartera';

    protected $fillable = [
        'prefijo',
        'consecutivo',
        'valor_base',
        'impuestos',
        'valor_factura',
        'abono',
        'saldo',
        'fecha_factura',
        'fecha_hoy',
        'dias_transcurridos',
        'asesor',
        'condicion_pago',
        'valor_diario_interes',
        'valor_acumulado_interes',
        'razon_social',
        'nit',
        'estado'
    ];
}
