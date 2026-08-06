<?php

namespace App\Exports\Sheets;

use App\Models\ReciboCaja;
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

class CajaSheet implements
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
        return ReciboCaja::query()
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
            'F358_ID_MEDIOS_PAGO',
            'F358_VALOR',
            'F358_REFERENCIA_OTROS',
            'F358_FECHA_CONSIGNACION',
            'f358_docto_banco_cg',
        ];
    }

    public function map($caja): array
    {
        return [
            $this->texto($caja->F350_ID_TIPO_DOCTO),
            $caja->F350_CONSEC_DOCTO,
            $this->texto($caja->F358_ID_MEDIOS_PAGO),
            (float) $caja->F358_VALOR,
            $this->texto($caja->F358_REFERENCIA_OTROS),

            $caja->F358_FECHA_CONSIGNACION
                ? $caja->F358_FECHA_CONSIGNACION->format('Ymd')
                : null,

            $this->texto($caja->f358_docto_banco_cg),
        ];
    }

    public function title(): string
    {
        return 'Caja';
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => '0',
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => '#,##0.00',
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
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