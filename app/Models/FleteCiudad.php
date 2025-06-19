<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleteCiudad extends Model
{
    protected $table = 'flete_ciudads';

    protected $fillable = [
        'depto',
        'ciudad',
        'menor',
        'mayor',
        'minimo',
        'entrega',
        'monto',
        'cod_ciudad',
        'cod_depto',
        'monto_minimo',
    ];
}
