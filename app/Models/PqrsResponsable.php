<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PqrsResponsable extends Model
{
    use SoftDeletes;

    protected $table = 'pqrs_responsables';

    protected $fillable = [
        'nombre','correos','sla_dias_default','activo','orden'
    ];

    protected $casts = [
        'correos' => 'array',
        'activo' => 'boolean',
    ];

    public function causales()
    {
        return $this->hasMany(PqrsCausal::class, 'responsable_id');
    }
}
