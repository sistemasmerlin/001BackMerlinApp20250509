<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReciboCajaCxc extends Model
{
    protected $table = 'recibo_caja_cxcs';

    protected $guarded = [];

    protected $casts = [
        'F354_VALOR_CR' => 'decimal:2',
        'F354_VALOR_APLICADO_PP' => 'decimal:2',
        'F354_VALOR_APROVECHA' => 'decimal:2',
        'F354_VALOR_RETENCION' => 'decimal:2',
    ];

    public function encabezado()
    {
        return $this->belongsTo(RecibosEncabezado::class, 'recibo_encabezado_id');
    }
}