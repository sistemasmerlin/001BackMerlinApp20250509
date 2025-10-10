<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use App\Imports\PresupuestosComercialesImport;

class PresupuestoComercial extends Model
{
     protected $table = 'presupuestos_comerciales';

    protected $fillable = [
        'codigo_asesor',
        'periodo',
        'presupuesto',
        'marca',
        'categoria',
        'clasificacion_asesor',
        'tipo_presupuesto',
    ];

    protected $casts = [
        'presupuesto' => 'decimal:2',
    ];

    // ✅ Scopes correctos
    public function scopePeriodo($query, ?string $periodo)
    {
        return $query->when($periodo, fn($q)=>$q->where('periodo', $periodo));
    }

    public function scopeAsesor($query, ?string $asesor)
    {
        return $query->when($asesor, fn($q)=>$q->where('codigo_asesor', $asesor));
    }

    public function scopeCategoria($query, ?string $cat)
    {
        return $query->when($cat, fn($q)=>$q->where('categoria', $cat));
    }
}
