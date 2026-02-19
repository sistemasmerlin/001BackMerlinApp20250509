<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PqrsCausal extends Model
{
    use SoftDeletes;

    protected $table = 'pqrs_causales';

    protected $fillable = [
        'submotivo_id','responsable_id','nombre','visible_asesor',
        'requiere_adjunto','permite_recogida','sla_dias',
        'activo','orden'
    ];

    protected $casts = [
        'requiere_adjunto' => 'boolean',
        'permite_recogida' => 'boolean',
        'visible_asesor' => 'boolean',
        'activo' => 'boolean',
    ];

    public function submotivo()
    {
        return $this->belongsTo(PqrsSubmotivo::class, 'submotivo_id');
    }

    public function responsable()
    {
        return $this->belongsTo(PqrsResponsable::class, 'responsable_id');
    }
}
