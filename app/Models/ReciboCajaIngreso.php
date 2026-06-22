<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReciboCajaIngreso extends Model
{
    protected $table = 'recibo_caja_ingresos';

    protected $guarded = [];

    protected $casts = [
        'F350_FECHA' => 'date',
        'F357_FECHA_RECAUDO' => 'date',
        'F357_VALOR_INGRESO' => 'decimal:2',
    ];

    public function encabezado()
    {
        return $this->belongsTo(RecibosEncabezado::class, 'recibo_encabezado_id');
    }
}