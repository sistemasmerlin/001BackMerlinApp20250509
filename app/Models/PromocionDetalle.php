<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromocionDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'promocion_id',
        'tipo',
        'descripcion',
        'acumulado',
        'modelo',
        'desde',
        'hasta',
        'descuento',
        'estado',
        'eliminado',
        'creado_por',
    ];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }
}
