<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transportadora extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transportadoras';

    protected $fillable = [
        'nit',
        'razon_social',
        'direccion',
        'departamento',
        'ciudad',
    ];

    public function orms()
    {
        return $this->hasMany(Orm::class, 'transportadora_id');
    }
}
