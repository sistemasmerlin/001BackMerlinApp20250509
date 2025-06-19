<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backorder extends Model
{
    protected $fillable = [
            'pedido_id', 'fecha_backorder', 'estado','estado_backorder'
        ];

    public function detalles()
    {
        return $this->hasMany(DetalleBackorder::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
