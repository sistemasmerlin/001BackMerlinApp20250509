<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudCreditoComentario extends Model
{
    protected $table = 'solicitud_credito_comentarios';

    protected $fillable = [
        'solicitud_credito_id',
        'comentario',
        'user_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function solicitud()
    {
        return $this->belongsTo(SolicitudCredito::class, 'solicitud_credito_id');
    }
}
