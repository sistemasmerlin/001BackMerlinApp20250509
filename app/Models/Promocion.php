<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'eliminado',
        'creado_por',
    ];

    public function detalles()
    {
        return $this->hasMany(PromocionDetalle::class);
    }

    public function relaciones()
    {
        return $this->hasMany(PromocionRelacion::class);
    }
}
