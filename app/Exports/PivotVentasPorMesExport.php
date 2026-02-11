<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PivotVentasPorMesExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        private array $marcas,
        private array $tabla,
        private string $titulo = 'Pivot'
    ) {}

    public function title(): string
    {
        return $this->titulo;
    }

    public function headings(): array
    {
        // Asesor | COD | <marcas...> | TOTAL
        return array_merge(['ASESOR', 'CODIGO'], $this->marcas, ['TOTAL']);
    }

    public function collection()
    {
        $out = [];

        foreach ($this->tabla as $row) {
            $fila = [
                $row['nombre'] ?? '',
                $row['vendedor'] ?? '',
            ];

            foreach ($this->marcas as $marca) {
                $fila[] = (float) ($row['cells'][$marca] ?? 0);
            }

            $fila[] = (float) ($row['total'] ?? 0);
            $out[] = $fila;
        }

        return new Collection($out);
    }

    public function styles(Worksheet $sheet)
    {
        // Header bold
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        // Congelar encabezado
        $sheet->freezePane('A2');

        return [];
    }
}
