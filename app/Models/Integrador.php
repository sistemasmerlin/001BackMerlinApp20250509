<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integrador extends Model
{
    protected $table = 'integradores';
    protected $fillable = [
        'user_id',
        'nit',
        'nombre_comercial',
        'prefijo_pedido',
        'lista_precio',
        'id_sucursal',
        'punto_envio',
        'condicion_pago',
        'codigo_asesor',
        'nombre_asesor',
        'correo_notificacion',
        'activo',
        'calcula_flete',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'calcula_flete' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}