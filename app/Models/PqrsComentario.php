<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqrsComentario extends Model
{
    protected $table = 'pqrs_comentarios';

    protected $fillable = [
        'pqrs_id',
        'user_id',
        'comentario',
    ];

    public function pqrs()
    {
        return $this->belongsTo(Pqrs::class, 'pqrs_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}