<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromocionRelacion extends Model
{
    use HasFactory;

    protected  $table = 'promocion_relaciones';

    protected $fillable = [
        'promocion_id',
        'asignado',
        'subcanal',
        'estado',
        'eliminado',
        'creado_por',
    ];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }
}
