<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PqrsAdjunto extends Model
{
    use SoftDeletes;

    protected $table = 'pqrs_adjuntos';

    protected $fillable = [
        'pqrs_id',
        'origen',
        'original_name',
        'mime',
        'size',
        'path',
    ];

    public function pqrs()
    {
        return $this->belongsTo(Pqrs::class, 'pqrs_id');
    }
}
