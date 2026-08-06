<?php

namespace App\Exports\Sheets;

use App\Models\ReciboCajaIngreso;
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

class OtrosIngresosSheet implements
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
        return ReciboCajaIngreso::query()
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
            'TIPO_DOCTO',
            'F350_CONSEC_DOCTO',
            'F350_FECHA',
            'F357_ID_CAJA',
            'F357_FECHA_RECAUDO',
            'F350_ID_TERCERO',
            'F357_VALOR_INGRESO',
            'F357_ID_COBRADORCOD',
            'F357_ID_UN',
            'F357_ID_FE',
            'F350_NOTAS',
            'F351_ID_AUXILIAR_AJUSTE',
            'F351_ID_AUXILIAR_PP',
            'F351_ID_CCOSTO_PP',
            'F351_ID_AUXILIAR_OTRO_ING',
            'F351_ID_TERCERO_OTRO_ING',
            'F351_ID_SUCURSAL_OTRO_ING',
            'F351_ID_CO_OTRO_ING',
            'F351_ID_UN_OTRO_ING',
            'F357_REFERENCIA',
        ];
    }

    public function map($ingreso): array
    {
        return [
            $this->texto($ingreso->F350_ID_CO),
            $this->texto($ingreso->TIPO_DOCTO),
            $ingreso->F350_CONSEC_DOCTO,

            $ingreso->F350_FECHA
                ? $ingreso->F350_FECHA->format('Ymd')
                : null,

            $this->texto($ingreso->F357_ID_CAJA),

            $ingreso->F357_FECHA_RECAUDO
                ? $ingreso->F357_FECHA_RECAUDO->format('Ymd')
                : null,

            $this->texto($ingreso->F350_ID_TERCERO),
            (float) $ingreso->F357_VALOR_INGRESO,
            $this->texto($ingreso->F357_ID_COBRADORCOD),
            $this->texto($ingreso->F357_ID_UN),
            $this->texto($ingreso->F357_ID_FE),
            $ingreso->F350_NOTAS,
            $this->texto($ingreso->F351_ID_AUXILIAR_AJUSTE),
            $this->texto($ingreso->F351_ID_AUXILIAR_PP),
            $this->texto($ingreso->F351_ID_CCOSTO_PP),
            $this->texto($ingreso->F351_ID_AUXILIAR_OTRO_ING),
            $this->texto($ingreso->F351_ID_TERCERO_OTRO_ING),
            $this->texto($ingreso->F351_ID_SUCURSAL_OTRO_ING),
            $this->texto($ingreso->F351_ID_CO_OTRO_ING),
            $this->texto($ingreso->F351_ID_UN_OTRO_ING),
            $this->texto($ingreso->F357_REFERENCIA),
        ];
    }

    public function title(): string
    {
        return 'R.C y otros ingresos';
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
            'H' => '#,##0.00',
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
            'M' => NumberFormat::FORMAT_TEXT,
            'N' => NumberFormat::FORMAT_TEXT,
            'O' => NumberFormat::FORMAT_TEXT,
            'P' => NumberFormat::FORMAT_TEXT,
            'Q' => NumberFormat::FORMAT_TEXT,
            'R' => NumberFormat::FORMAT_TEXT,
            'S' => NumberFormat::FORMAT_TEXT,
            'T' => NumberFormat::FORMAT_TEXT,
            'U' => NumberFormat::FORMAT_TEXT,
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