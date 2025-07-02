<?php

namespace App\Imports;

use App\Models\PromocionDetalle;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportPromociones implements ToModel
{
    private $rowIndex = 0; // contador de filas para determinar la posición

    public function model(array $row)
    {
        if (count($row) < 7) {
            $this->rowIndex++;
            return null;
        }

        $data = [
            'tipo'        => $row[1],
            'descripcion' => $row[2],
            'acumulado'   => $row[3],
            'modelo'      => $row[4],
            'desde'       => $row[5],
            'hasta'       => $row[6],
            'descuento'   => $row[7] ?? 0,
            'creado_por'  => Auth::user()?->name ?? 'system',
        ];

       
        $registroExistente = PromocionDetalle::where('promocion_id', $row[0])
            ->orderBy('id')
            ->skip($this->rowIndex)
            ->first();

        if ($registroExistente) {
            $registroExistente->update($data);
        } else {
            PromocionDetalle::create(array_merge(['promocion_id' => $row[0]], $data));
        }

        $this->rowIndex++;
        return null; // No se retorna un nuevo modelo, ya se insertó o actualizó manualmente
    }
}
