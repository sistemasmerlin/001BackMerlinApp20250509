<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $fillable = [
        'pedido_id', 'referencia', 'descripcion', 'cantidad',
        'precio_unitario', 'descuento', 'subtotal', 'marca'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
