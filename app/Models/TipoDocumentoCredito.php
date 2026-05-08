<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoDocumentoCredito extends Model
{
    use SoftDeletes;

    protected $table = 'tipos_documentos_credito';

    protected $fillable = [
        'nombre',
        'descripcion',
        'cantidad_minima',
        'cantidad_maxima',
        'obligatorio',
        'multiple',
        'estado',
        'orden',
    ];

    public function documentos()
    {
        return $this->hasMany(SolicitudCreditoDocumento::class);
    }
}