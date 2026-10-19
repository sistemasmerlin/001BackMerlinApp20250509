<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductoEmpaque extends Model
{
    protected $table = 'productos_empaques';

    protected $fillable = [
        'referencia',
        'descripcion',
        'unidades_por_empaque',
    ];

    protected function casts(): array
    {
        return [
            'unidades_por_empaque' => 'integer',
        ];
    }

    public function scopeBuscar(Builder $query, string $buscar): Builder
    {
        $buscar = trim($buscar);

        return $query->when($buscar !== '', function (Builder $query) use ($buscar) {
            $query->where(function (Builder $query) use ($buscar) {
                $query->where('referencia', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        });
    }
}