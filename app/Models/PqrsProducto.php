<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PqrsProducto extends Model
{
    use SoftDeletes;

    protected $table = 'pqrs_productos';

    protected $fillable = [
        'pqrs_id',
        'causal_id',
        'responsable_id',
        'submotivo_id',
        'tipo_docto',
        'nro_docto',
        'fecha',
        'referencia',
        'descripcion_ref',
        'unidades_solicitadas',
        'precio_unitario',
        'valor_bruto',
        'valor_imp',
        'valor_neto',
        'requiere_recogida',
        'solicitud_recogida',
    ];

    protected $casts = [
        'fecha' => 'date',
        'requiere_recogida' => 'boolean',
        'unidades_solicitadas' => 'decimal:4',
        'precio_unitario' => 'decimal:4',
        'valor_bruto' => 'decimal:4',
        'valor_imp' => 'decimal:4',
        'valor_neto' => 'decimal:4',
    ];

    public function pqrs()
    {
        return $this->belongsTo(Pqrs::class, 'pqrs_id');
    }

    public function causal()
    {
        return $this->belongsTo(PqrsCausal::class, 'causal_id');
    }

    public function responsable()
    {
        return $this->belongsTo(PqrsResponsable::class, 'responsable_id');
    }

    public function adjuntos()
    {
        return $this->hasMany(PqrsProductoAdjunto::class, 'pqrs_producto_id');
    }
}