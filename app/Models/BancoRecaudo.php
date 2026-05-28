<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancoRecaudo extends Model
{
    use SoftDeletes;

    protected $table = 'banco_recaudos';

    protected $fillable = [
        'id_banco',
        'descripcion_banco',
        'id_cuenta',
        'descripcion_cuenta',
        'numero_cuenta',
        'id_medio_pago',
        'tipo_cuenta',
        'estado',
    ];
}