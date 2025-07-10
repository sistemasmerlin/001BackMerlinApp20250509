<?php

namespace App\Imports;

use App\Models\PromocionDetalle;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportPromociones implements ToModel
{
    public function model(array $row)
    {
        // Validar que haya suficientes columnas
        if (count($row) < 7) {
            return null;
        }

        return new PromocionDetalle([
            'promocion_id' => $row[0],
            'tipo'         => $row[1],
            'descripcion'  => $row[2],
            'acumulado'    => $row[3],
            'modelo'       => $row[4],
            'desde'        => $row[5],
            'hasta'        => $row[6],
            'descuento'    => $row[7] ?? 0,
            'creado_por'   => Auth::user()?->name ?? 'system',
        ]);
    }
}
