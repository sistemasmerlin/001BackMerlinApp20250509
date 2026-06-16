<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'prefijo','flete','orden_compra','id_estado_pedido','nota','codigo_asesor','nombre_asesor','nit','razon_social','lista_precio','correo_cliente','id_sucursal','condicion_pago','fecha_pedido','estado_siesa','estado','observaciones'
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function direccionEnvio()
    {
        return $this->hasOne(DireccionEnvio::class);
    }

    public function backorder()
    {
        return $this->hasOne(Backorder::class);
    }
}


