<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MotivosVisita extends Model
{
    protected $table = 'motivos_visitas';

    protected $fillable = [
        'motivo',
        'estado',
    ];

    public function reportes(): BelongsToMany
    {
        return $this->belongsToMany(ReporteVisita::class, 'motivo_reporte_visita', 'motivos_visita_id', 'reporte_visita_id');
    }
}
