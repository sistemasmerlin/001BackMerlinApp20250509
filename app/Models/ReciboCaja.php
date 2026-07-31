<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReciboCaja extends Model
{
    protected $table = 'recibo_cajas';

    protected $fillable = [
        'recibo_encabezado_id',
        'F350_ID_TIPO_DOCTO',
        'F350_CONSEC_DOCTO',
        'F358_ID_MEDIOS_PAGO',
        'F358_VALOR',
        'F358_REFERENCIA_OTROS',
        'F358_FECHA_CONSIGNACION',
        'f358_docto_banco_cg',
    ];

    protected $casts = [
        'F358_VALOR' => 'decimal:2',
        'F358_FECHA_CONSIGNACION' => 'date',
    ];

    public function encabezado()
    {
        return $this->belongsTo(
            RecibosEncabezado::class,
            'recibo_encabezado_id'
        );
    }
}