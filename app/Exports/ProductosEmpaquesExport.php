<?php

namespace App\Exports;

use App\Models\ProductoEmpaque;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ProductosEmpaquesExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithCustomValueBinder
{
    public function __construct(
        private string $buscar = '',
        private bool $plantilla = false
    ) {}

    public function headings(): array
    {
        return [
            'referencia',
            'descripcion',
            'unidades_por_empaque',
        ];
    }

    public function collection()
    {
        if ($this->plantilla) {
            return collect();
        }

        return ProductoEmpaque::query()
            ->buscar($this->buscar)
            ->orderBy('referencia')
            ->get([
                'referencia',
                'descripcion',
                'unidades_por_empaque',
            ]);
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), ['A', 'B'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}