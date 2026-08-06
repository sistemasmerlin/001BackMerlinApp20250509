<?php

namespace App\Exports\Sheets;

use App\Models\ReciboCajaRetencion;
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

class RetencionesSheet implements
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
        return ReciboCajaRetencion::query()
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
            'F350_ID_CO',
            'F350_ID_TIPO_DOCTO',
            'F350_CONSEC_DOCTO',
            'F353_ID_AUXILIAR_DOCTO_CRUCE',
            'F353_ID_CO_DOCTO_CRUCE',
            'F353_ID_SUCURSAL_DOCTO_CRUCE',
            'F353_ID_TIPO_DOCTO_CRUCE',
            'F353_CONSEC_DOCTO_CRUCE',
            'F351_ID_AUXILIAR_RETENCION',
            'F354_VALOR_DB',
            'F354_VALOR_CR',
            'F351_BASE_GRAVABLE',
        ];
    }

    public function map($retencion): array
    {
        return [
            $this->texto($retencion->F350_ID_CO),
            $this->texto($retencion->F350_ID_TIPO_DOCTO),
            $retencion->F350_CONSEC_DOCTO,
            $this->texto($retencion->F353_ID_AUXILIAR_DOCTO_CRUCE),
            $this->texto($retencion->F353_ID_CO_DOCTO_CRUCE),
            $this->texto($retencion->F353_ID_SUCURSAL_DOCTO_CRUCE),
            $this->texto($retencion->F353_ID_TIPO_DOCTO_CRUCE),
            $this->texto($retencion->F353_CONSEC_DOCTO_CRUCE),
            $this->texto($retencion->F351_ID_AUXILIAR_RETENCION),
            (float) $retencion->F354_VALOR_DB,
            (float) $retencion->F354_VALOR_CR,
            (float) $retencion->F351_BASE_GRAVABLE,
        ];
    }

    public function title(): string
    {
        return 'Retenciones';
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => '0',
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => '#,##0.00',
            'K' => '#,##0.00',
            'L' => '#,##0.00',
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