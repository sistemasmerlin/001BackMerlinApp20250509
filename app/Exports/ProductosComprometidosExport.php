<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosComprometidosExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithColumnFormatting
{
    public function __construct(
        private array $productos
    ) {
    }

    public function collection(): Collection
    {
        return collect($this->productos)->map(function ($producto) {
            return [
                'referencia'  => $producto['referencia'],
                'descripcion' => $producto['descripcion'],
                'marca'       => $producto['marca'],
                'unidades'    => $producto['unidades'],
                'valor'       => $producto['valor'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'REFERENCIA',
            'DESCRIPCIÓN',
            'MARCA',
            'UNIDADES',
            'VALOR COMPROMETIDO',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'E' => '"$"#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $ultimaFila = count($this->productos) + 1;

        // Encabezado rojo
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['ARGB' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['ARGB' => 'FFE30613'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Contenido blanco y texto negro
        if ($ultimaFila >= 2) {
            $sheet->getStyle("A2:E{$ultimaFila}")->applyFromArray([
                'font' => [
                    'color' => ['ARGB' => 'FF000000'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['ARGB' => 'FFFFFFFF'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['ARGB' => 'FFE30613'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            $sheet->getStyle("D2:E{$ultimaFila}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:E{$ultimaFila}");

        // Límites para evitar columnas exageradamente anchas
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(55);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(23);

        $sheet->getStyle("B2:B{$ultimaFila}")
            ->getAlignment()
            ->setWrapText(true);

        return [];
    }
}