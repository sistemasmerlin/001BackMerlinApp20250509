<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FletesExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    public function __construct(
        private readonly Collection $fletes
    ) {
    }

    public function collection(): Collection
    {
        return $this->fletes;
    }

    public function headings(): array
    {
        return [
            '#',
            'Departamento',
            'Código Departamento',
            'Ciudad',
            'Código Ciudad',
            'Menor',
            'Mayor',
            'Mínimo',
            'Entrega',
            'Monto',
            'Monto Mínimo',
        ];
    }

    public function map($flete): array
    {
        static $numero = 0;

        $numero++;

        return [
            $numero,
            $flete->depto,
            $flete->cod_depto,
            $flete->ciudad,
            $flete->cod_ciudad,
            $flete->menor,
            $flete->mayor,
            $flete->minimo,
            $flete->entrega,
            $flete->monto,
            $flete->monto_minimo,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $ultimaFila = $this->fletes->count() + 1;

        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:K{$ultimaFila}");

        $sheet->getStyle("F2:G{$ultimaFila}")
            ->getNumberFormat()
            ->setFormatCode('0.00"%"');

        $sheet->getStyle("H2:H{$ultimaFila}")
            ->getNumberFormat()
            ->setFormatCode('$#,##0');

        $sheet->getStyle("J2:K{$ultimaFila}")
            ->getNumberFormat()
            ->setFormatCode('$#,##0');

        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}