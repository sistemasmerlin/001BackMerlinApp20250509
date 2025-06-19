<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelacionAsesores extends Model
{
    protected $table = 'relacion_asesores';

    protected $fillable = ['asesor_id', 'relacionado_id'];

    // Usuario principal
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Usuario relacionado
    public function relacionado()
    {
        return $this->belongsTo(User::class, 'relacionado_id');
    }
}
