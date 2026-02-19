<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PqrsSubmotivo extends Model
{
    use SoftDeletes;

    protected $table = 'pqrs_submotivos';

    protected $fillable = ['motivo_id','nombre','activo','orden'];

    protected $casts = ['activo' => 'boolean'];

    public function motivo()
    {
        return $this->belongsTo(PqrsMotivo::class, 'motivo_id');
    }

    public function causales()
    {
        return $this->hasMany(PqrsCausal::class, 'submotivo_id');
    }
}
