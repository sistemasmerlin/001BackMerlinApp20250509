<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pqrs extends Model
{
    use SoftDeletes;

    protected $table = 'pqrs';

    protected $fillable = [
        'nit',
        'razon_social',
        'departamento',
        'ciudad',
        'direccion',
        'telefono',
        'correo_cliente',
        'correo_asesor',
        'cod_asesor',
        'nombre_asesor',
        'fecha_creacion',
        'fecha_revisado',
        'fecha_cierre',
        'estado',
        'comentario_cierre',
        'numero_orm',
        'orm_id',
        'tipo_acuerdo',
        'nota_acuerdo',
        'valor_acuerdo',
        'creado_por',
        'revisado_por',
        'cerrado_por',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_revisado' => 'datetime',
        'fecha_cierre'   => 'datetime',
        'valor_acuerdo'  => 'decimal:2',
    ];

    // Relación futura (cuando exista la tabla pqrs_orms)
    // public function orm()
    // {
    //     return $this->belongsTo(PqrsOrm::class, 'orm_id');
    // }
}
