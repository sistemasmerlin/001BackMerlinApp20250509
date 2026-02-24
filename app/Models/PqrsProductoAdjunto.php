<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PqrsProductoAdjunto extends Model
{
    use SoftDeletes;

    protected $table = 'pqrs_producto_adjuntos';

    protected $fillable = [
        'pqrs_producto_id',
        'original_name',
        'mime',
        'size',
        'path',
    ];

    public function producto()
    {
        return $this->belongsTo(PqrsProducto::class, 'pqrs_producto_id');
    }
}