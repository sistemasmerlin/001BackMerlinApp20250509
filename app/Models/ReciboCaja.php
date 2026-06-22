<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReciboCaja extends Model
{
    protected $table = 'recibo_cajas';

    protected $guarded = [];

    protected $casts = [
        'F358_VALOR' => 'decimal:2',
        'F358_FECHA_CONSIGNACION' => 'date',
    ];

    public function encabezado()
    {
        return $this->belongsTo(RecibosEncabezado::class, 'recibo_encabezado_id');
    }
}