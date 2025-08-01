<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ReporteVisita extends Model
{
    protected $fillable = [
        'nit',
        'razon_social',
        'sucursal',
        'vendedor',
        'latitud',
        'longitud',
        'ciudad',
        'notas',
    ];

    public function motivos(): BelongsToMany
    {
        return $this->belongsToMany(MotivosVisita::class, 'motivo_reporte_visita', 'reporte_visita_id', 'motivos_visita_id');
    }
}
