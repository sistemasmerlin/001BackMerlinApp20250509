<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class PivotVentasPorMesExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(
        private array $marcas,
        private array $tabla,      // tablaUnidades o tablaValor
        private string $tituloHoja // "Unidades" o "Valor"
    ) {}

    public function title(): string
    {
        return $this->tituloHoja;
    }

    public function headings(): array
    {
        return array_merge(
            ['Asesor', 'Código'],
            $this->marcas,
            ['TOTAL']
        );
    }

    public function array(): array
    {
        $out = [];

        foreach ($this->tabla as $row) {
            $line = [
                $row['nombre'] ?? '',
                $row['vendedor'] ?? '',
            ];

            foreach ($this->marcas as $marca) {
                $line[] = (float)($row['cells'][$marca] ?? 0);
            }

            $line[] = (float)($row['total'] ?? 0);

            $out[] = $line;
        }

        return $out;
    }
}
