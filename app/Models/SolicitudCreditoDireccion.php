<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudCreditoDireccion extends Model
{
    protected $table = 'solicitud_credito_direcciones';

    protected $fillable = [
        'solicitud_credito_id',
        'contacto',
        'direccion',
        'cod_depto',
        'depto',
        'cod_ciudad',
        'ciudad',
        'telefono',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudCredito::class, 'solicitud_credito_id');
    }
}