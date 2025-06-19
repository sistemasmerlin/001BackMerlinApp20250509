<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DireccionEnvio extends Model
{
    protected $fillable = [
        'pedido_id', 'id_punto_envio', 'direccion', 'ciudad',
        'departamento', 'codigo_ciudad', 'codigo_departamento'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
