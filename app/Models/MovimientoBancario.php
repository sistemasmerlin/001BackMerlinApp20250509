<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoBancario extends Model
{
    use SoftDeletes;

    protected $table = 'movimientos_bancarios';

    protected $fillable = [
        'archivo_origen',
        'fila_origen',
        'fecha_importacion',

        'fecha_movimiento',
        'oficina_canal',
        'descripcion_movimiento',

        'referencia_1',
        'referencia_2',
        'referencia_3',

        'moneda',
        'valor',
        'tipo_movimiento',

        'fecha_original',
        'valor_original',

        'hash_movimiento',

        'procesado',
        'fecha_procesado',

        'observaciones',
        'usuario_importacion_id',

  
        'recibo_encabezado_id',
        'estado',
        'fecha_aplicacion',
    ];

    protected $casts = [
        'fecha_importacion' => 'datetime',
        'fecha_movimiento' => 'date',
        'valor' => 'decimal:2',
        'procesado' => 'boolean',
        'fecha_procesado' => 'datetime',
    ];

    public function usuarioImportacion()
    {
        return $this->belongsTo(
            User::class,
            'usuario_importacion_id'
        );
    }

    public function scopeCreditos($query)
    {
        return $query->where('tipo_movimiento', 'CREDITO');
    }

    public function scopeDebitos($query)
    {
        return $query->where('tipo_movimiento', 'DEBITO');
    }

    public function scopePendientes($query)
    {
        return $query->where('procesado', false);
    }

    public function recibo()
    {
        return $this->belongsTo(
            RecibosEncabezado::class,
            'recibo_encabezado_id'
        );
    }
}