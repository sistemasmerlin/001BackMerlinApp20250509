<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleBackorder extends Model
{
    protected $fillable = [
        'backorder_id', 'referencia', 'descripcion', 'cantidad','cantidad_enviar',
        'precio_unitario', 'descuento', 'subtotal'
    ];

    public function backorder()
    {
        return $this->belongsTo(Backorder::class);
    }
}
