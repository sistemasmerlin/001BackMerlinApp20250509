<?php

namespace App\Exports\Sheets;

use App\Models\ReciboCajaCxc;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CxcSheet implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithColumnFormatting,
    WithStyles,
    ShouldAutoSize
{
    public function __construct(
        protected string $estado,
        protected ?array $reciboIds = null,
        protected ?string $usuarioAsignado = null,
    ) {
    }

    public function query(): Builder
    {
        return ReciboCajaCxc::query()
            ->with('encabezado')
            ->whereHas('encabezado', function (Builder $query) {
                $query->where('estado', $this->estado);

                if (!empty($this->reciboIds)) {
                    $query->whereIn('id', $this->reciboIds);
                }

                if ($this->usuarioAsignado) {
                    $query->where(
                        'usuarioAsignado',
                        $this->usuarioAsignado
                    );
                }
            })
            ->orderBy('recibo_encabezado_id')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'F350_ID_TIPO_DOCTO',
            'F350_CONSEC_DOCTO',
            'F353_ID_AUXILIAR_DOCTO_CRUCE',
            'F353_ID_SUCURSAL_DOCTO_CRUCE',
            'F353_ID_TIPO_DOCTO_CRUCE',
            'F353_CONSEC_DOCTO_CRUCE',
            'F354_VALOR_CR',
            'F354_VALOR_APLICADO_PP',
            'F354_VALOR_APROVECHA',
            'F354_VALOR_RETENCION',
        ];
    }

    public function map($cxc): array
    {
        return [
            $this->texto($cxc->F350_ID_TIPO_DOCTO),
            $cxc->F350_CONSEC_DOCTO,
            $this->texto($cxc->F353_ID_AUXILIAR_DOCTO_CRUCE),
            $this->texto($cxc->F353_ID_SUCURSAL_DOCTO_CRUCE),
            $this->texto($cxc->F353_ID_TIPO_DOCTO_CRUCE),
            $this->texto($cxc->F353_CONSEC_DOCTO_CRUCE),
            (float) $cxc->F354_VALOR_CR,
            (float) $cxc->F354_VALOR_APLICADO_PP,
            (float) $cxc->F354_VALOR_APROVECHA,
            (float) $cxc->F354_VALOR_RETENCION,
        ];
    }

    public function title(): string
    {
        return 'CxC';
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => '0',
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => '#,##0.00',
            'H' => '#,##0.00',
            'I' => '#,##0.00',
            'J' => '#,##0.00',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    private function texto($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return trim((string) $valor);
    }
}