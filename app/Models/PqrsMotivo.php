<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PqrsMotivo extends Model
{
    use SoftDeletes;

    protected $table = 'pqrs_motivos';

    protected $fillable = ['nombre','activo','orden'];

    protected $casts = ['activo' => 'boolean'];

    public function submotivos()
    {
        return $this->hasMany(PqrsSubmotivo::class, 'motivo_id');
    }
}
