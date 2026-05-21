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

        'quien_da_referencia',
        'cupo_asignado',
        'antiguedad_comercial',
        'promedio_pago',
        'cheques_devueltos',
        'activo',
        'concepto',
        'fecha_referencia',
        'ultimo_despacho',
        'verifico_referencia',
    ];

    protected $casts = [
        'cupo_credito' => 'decimal:2',
        'cupo_asignado' => 'decimal:2',
        'fecha_referencia' => 'date',
        'ultimo_despacho' => 'date',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudCredito::class, 'solicitud_credito_id');
    }

    public function usuarioVerifico()
    {
        return $this->belongsTo(User::class, 'verifico_referencia');
    }
}