<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CumplimientoMixExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        private array $marcas,
        private array $tabla,
        private string $titulo = 'Cumplimiento Mix'
    ) {}

    public function title(): string
    {
        return $this->titulo;
    }

    public function headings(): array
    {
        // Asesor | Código | (por cada marca: Presu, Real, %) | Total %
        $head = ['ASESOR', 'CODIGO'];

        foreach ($this->marcas as $marca) {
            $head[] = "{$marca} - PRESU";
            $head[] = "{$marca} - REAL";
            $head[] = "{$marca} - %";
        }

        $head[] = "TOTAL %";

        return $head;
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
                $c = $row['cells'][$marca] ?? ['presu' => 0, 'real' => 0, 'pct' => 0];

                $fila[] = (float) ($c['presu'] ?? 0);
                $fila[] = (float) ($c['real'] ?? 0);
                $fila[] = (float) ($c['pct'] ?? 0); // porcentaje numérico
            }

            $fila[] = (float) ($row['tot_pct'] ?? 0);

            $out[] = $fila;
        }

        return new Collection($out);
    }

    public function styles(Worksheet $sheet)
    {
        // Header bold
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        // Freeze header
        $sheet->freezePane('A2');

        // Opcional: formato de porcentaje para columnas de % (desde la 2 + 3*marcas...)
        // No es obligatorio; lo puedes dejar así para que quede como número.

        return [];
    }
}
