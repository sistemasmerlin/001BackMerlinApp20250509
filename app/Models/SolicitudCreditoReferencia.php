<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudCreditoReferencia extends Model
{
    protected $table = 'solicitud_credito_referencias';

    protected $fillable = [
        'solicitud_credito_id',
        'empresa',
        'nit',
        'cod_depto',
        'depto',
        'cod_ciudad',
        'ciudad',
        'telefono',
        'cupo_credito',
    ];

    protected $casts = [
        'cupo_credito' => 'decimal:2',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudCredito::class, 'solicitud_credito_id');
    }
}