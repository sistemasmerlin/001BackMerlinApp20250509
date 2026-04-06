<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Orm extends Model
{
    use HasFactory;

    protected $table = 'orms';

    protected $fillable = [
        'pqrs_id',
        'razon_social',
        'nit',
        'direccion',
        'departamento',
        'ciudad',
        'telefono',
        'transportadora_id',
        'lios',
        'cajas',
        'peso',
        'estado',
        'valor_declarado',
        'numero_guia',
        'fecha_llegada_bodega',
        'comentarios',
        'fecha_recogida_programada',
        'fecha_recibido_transportadora',
        'fecha_recogida_transportadora',
        'usuario_marca_recogida_transportadora_id',
        'usuario_recibe_id',
    ];

    protected $casts = [
        'fecha_recogida_programada' => 'date',
        'fecha_recibido_transportadora' => 'datetime',
        'fecha_recogida_transportadora' => 'datetime',
        'fecha_llegada_bodega' => 'datetime',
        'peso' => 'decimal:2',
        'valor_declarado' => 'decimal:2',
    ];

    public function pqrs()
    {
        return $this->belongsTo(Pqrs::class, 'pqrs_id');
    }

    public function transportadora()
    {
        return $this->belongsTo(Transportadora::class, 'transportadora_id');
    }

    public function usuarioRecibe()
    {
        return $this->belongsTo(User::class, 'usuario_recibe_id');
    }
    public function usuarioMarcaRecogidaTransportadora()
    {
        return $this->belongsTo(User::class, 'usuario_marca_recogida_transportadora_id');
    }
}
