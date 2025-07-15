<?php

namespace App\Imports;

use App\Models\FleteCiudad;
use Maatwebsite\Excel\Concerns\ToModel;


class ImportFletes implements ToModel
{
    public function model(array $row)
    {
        // Validar que haya suficientes columnas
        if (count($row) < 10) {
            return null;
        }

        return new FleteCiudad([
                'depto'             => trim($row[0] ?? ''),
                'cod_depto'         => trim($row[1] ?? ''),
                'ciudad'            => trim($row[2] ?? ''),
                'cod_ciudad'        => trim($row[3] ?? ''),
                'menor'             => trim($row[4] ?? ''),
                'mayor'             => trim($row[5] ?? 0),
                'minimo'            => trim($row[6] ?? 0),
                'entrega'           => trim($row[7] ?? 0),
                'monto'             => trim($row[8] ?? 0),
                'monto_minimo'      => trim($row[9] ?? 0),
        ]);
    }
}
