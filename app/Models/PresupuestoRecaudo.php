<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoRecaudo extends Model
{
     protected $table = 'presupuesto_recaudo';
    protected $fillable = [
        'asesor',
        'nombre_asesor',
        'prefijo',
        'consecutivo',
        'saldo',
        'cond_pago',
        'nit_cliente',
        'cliente',
        'fecha_doc',
        'fecha_corte',
        'dias',
        'periodo',
        'creado_por',
        'estado',
        'eliminado',
    ];

    protected $casts = [
        'saldo'     => 'float',
        'dias'      => 'integer',
        'estado'    => 'boolean',
        'eliminado' => 'boolean',
    ];

    /*
     |--------------------------------------------------------------------------
     | Scopes (filtros)
     |--------------------------------------------------------------------------
     */

    public function scopePeriodo(Builder $q, ?string $periodo): Builder
    {
        if (!$periodo) return $q;
        return $q->where('periodo', $periodo);
    }

    public function scopeAsesor(Builder $q, ?string $asesor): Builder
    {
        if (!$asesor) return $q;
        return $q->where('asesor', $asesor);
    }

    public function scopeActivo(Builder $q): Builder
    {
        return $q->where('estado', 1)->where('eliminado', 0);
    }

    public function scopeBuscar(Builder $q, ?string $search): Builder
    {
        if (!$search) return $q;

        $s = '%' . trim($search) . '%';

        return $q->where(function ($qq) use ($s) {
            $qq->where('asesor', 'like', $s)
               ->orWhere('nombre_asesor', 'like', $s)
               ->orWhere('nit_cliente', 'like', $s)
               ->orWhere('cliente', 'like', $s)
               ->orWhere('prefijo', 'like', $s)
               ->orWhere('consecutivo', 'like', $s)
               ->orWhere('cond_pago', 'like', $s)
               ->orWhere('periodo', 'like', $s);
        });
    }
}
