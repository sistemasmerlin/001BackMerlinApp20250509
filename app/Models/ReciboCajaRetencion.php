<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReciboCajaRetencion extends Model
{
    protected $table = 'recibo_caja_retenciones';

    protected $guarded = [];

    protected $casts = [
        'F354_VALOR_DB' => 'decimal:2',
        'F354_VALOR_CR' => 'decimal:2',
        'F351_BASE_GRAVABLE' => 'decimal:2',
    ];

    public function encabezado()
    {
        return $this->belongsTo(RecibosEncabezado::class, 'recibo_encabezado_id');
    }
}