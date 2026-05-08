<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudCreditoDocumento extends Model
{
    use SoftDeletes;

    protected $table = 'solicitud_credito_documentos';

    protected $fillable = [
        'solicitud_credito_id',
        'tipo_documento_credito_id',
        'nombre_original',
        'archivo',
        'disk',
        'mime_type',
        'peso',
        'estado',
        'observacion',
        'aprobado_por',
        'fecha_revision',
    ];

    protected $casts = [
        'fecha_revision' => 'datetime',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudCredito::class, 'solicitud_credito_id');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumentoCredito::class, 'tipo_documento_credito_id');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}