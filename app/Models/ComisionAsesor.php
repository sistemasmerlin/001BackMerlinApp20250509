<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComisionAsesor extends Model
{
    use SoftDeletes;

    protected $table = 'comisiones_asesores';

    protected $fillable = [
        'periodo',
        'cod_asesor',
        'user_id',
        'tipo',
        'valor',
        'updated_by',
    ];

    protected $casts = [
        'valor' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}