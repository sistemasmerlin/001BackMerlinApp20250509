<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;

class UsuariosExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithCustomStartCell,
    WithStyles,
    ShouldAutoSize,
    WithEvents,
    WithDrawings
{
    private Collection $usuarios;

    private string $generadoPor;

    private int $totalUsuarios = 0;

    public function __construct(?string $generadoPor = null)
    {
        $this->generadoPor = $generadoPor ?: 'Sistema';

        $this->usuarios = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get();

        $this->totalUsuarios = $this->usuarios->count();
    }

    /**
     * Información que se exportará.
     */
    public function collection(): Collection
    {
        return $this->usuarios;
    }

    /**
     * La tabla comienza en la fila 6.
     */
    public function startCell(): string
    {
        return 'A6';
    }

    /**
     * Encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Zona',
            'Email',
            'Cédula',
            'Celular',
            'Código asesor',
            'Nombre asesor',
            'Código recibos',
            'Categoría asesor',
            'Roles',
            'Estado',
            'Fecha de creación',
            'Último acceso',
        ];
    }

    /**
     * Transformación de cada usuario.
     */
    public function map($usuario): array
    {
        $roles = $usuario->roles
            ->pluck('name')
            ->filter()
            ->implode(', ');

        return [
            $usuario->id,
            $usuario->name ?: '—',
            $usuario->email ?: '—',
            $usuario->cedula ?: '—',
            $usuario->celular ?: '—',
            $usuario->codigo_asesor ?: '—',
            $usuario->nombre_asesor ?: '—',
            $usuario->codigo_recibos ?: '—',
            $usuario->categoria_asesor
                ? ucfirst($usuario->categoria_asesor)
                : '—',
            $roles ?: 'Sin rol',
            $this->obtenerEstado($usuario),
            $this->formatearFecha($usuario->created_at),
            $this->formatearFecha(
                data_get($usuario, 'last_login_at')
                ?? data_get($usuario, 'ultimo_acceso')
            ),
        ];
    }

    /**
     * Estilos generales.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            6 => [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => 'FFFFFFFF',
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFE30613',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'argb' => 'FFD1D5DB',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Logo del informe.
     *
     * Guarda el logo en:
     * public/images/logo-merlin.png
     *
     * Si el archivo no existe, el Excel se genera sin logo.
     */
    public function drawings(): array
    {
        $logo = public_path('images/logo-merlin.png');

        if (!file_exists($logo)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Merlin');
        $drawing->setDescription('Logo Merlin');
        $drawing->setPath($logo);
        $drawing->setHeight(52);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(8);
        $drawing->setOffsetY(5);

        return [$drawing];
    }

    /**
     * Eventos para diseñar la hoja.
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('B1:M1');
                $sheet->mergeCells('B2:M2');
                $sheet->mergeCells('B3:M3');
                $sheet->mergeCells('B4:M4');

                $sheet->setCellValue('B1', 'INFORME GENERAL DE USUARIOS');
                $sheet->setCellValue(
                    'B2',
                    'Fecha de generación: ' . now()->format('d/m/Y h:i A')
                );
                $sheet->setCellValue(
                    'B3',
                    'Generado por: ' . $this->generadoPor
                );
                $sheet->setCellValue(
                    'B4',
                    'Total de usuarios: ' . number_format($this->totalUsuarios, 0, ',', '.')
                );

                $sheet->getStyle('B1:M1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => [
                            'argb' => 'FFE30613',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('B2:M4')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => [
                            'argb' => 'FF52525B',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(18);
                $sheet->getRowDimension(5)->setRowHeight(8);
            },

            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $filaEncabezado = 6;
                $primeraFilaDatos = 7;
                $ultimaFilaDatos = $filaEncabezado + $this->totalUsuarios;
                $filaTotal = $ultimaFilaDatos + 1;

                /*
                 * Mantener visible el encabezado al desplazarse.
                 */
                $sheet->freezePane('A7');

                /*
                 * Filtros automáticos.
                 */
                $sheet->setAutoFilter("A{$filaEncabezado}:M{$filaEncabezado}");

                /*
                 * Alto del encabezado.
                 */
                $sheet->getRowDimension($filaEncabezado)->setRowHeight(34);

                /*
                 * Bordes, alineación y texto ajustado.
                 */
                if ($this->totalUsuarios > 0) {
                    $sheet
                        ->getStyle("A{$primeraFilaDatos}:M{$ultimaFilaDatos}")
                        ->applyFromArray([
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => [
                                        'argb' => 'FFE4E4E7',
                                    ],
                                ],
                            ],
                        ]);

                    /*
                     * Filas alternadas.
                     */
                    for (
                        $fila = $primeraFilaDatos;
                        $fila <= $ultimaFilaDatos;
                        $fila++
                    ) {
                        $sheet->getRowDimension($fila)->setRowHeight(24);

                        if ($fila % 2 === 0) {
                            $sheet
                                ->getStyle("A{$fila}:M{$fila}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('FFF8FAFC');
                        }
                    }

                    /*
                     * Centrar columnas específicas.
                     */
                    $sheet
                        ->getStyle("A{$primeraFilaDatos}:A{$ultimaFilaDatos}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet
                        ->getStyle("F{$primeraFilaDatos}:F{$ultimaFilaDatos}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet
                        ->getStyle("H{$primeraFilaDatos}:I{$ultimaFilaDatos}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet
                        ->getStyle("K{$primeraFilaDatos}:M{$ultimaFilaDatos}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                /*
                 * Fila de total.
                 */
                $sheet->mergeCells("A{$filaTotal}:J{$filaTotal}");
                $sheet->setCellValue(
                    "A{$filaTotal}",
                    'TOTAL DE USUARIOS'
                );
                $sheet->setCellValue(
                    "K{$filaTotal}",
                    $this->totalUsuarios
                );
                $sheet->mergeCells("K{$filaTotal}:M{$filaTotal}");

                $sheet
                    ->getStyle("A{$filaTotal}:M{$filaTotal}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => [
                                'argb' => 'FFFFFFFF',
                            ],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'argb' => 'FF18181B',
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => [
                                    'argb' => 'FF18181B',
                                ],
                            ],
                        ],
                    ]);

                $sheet->getRowDimension($filaTotal)->setRowHeight(26);

                /*
                 * Anchos recomendados.
                 */
                $anchos = [
                    'A' => 10,
                    'B' => 28,
                    'C' => 34,
                    'D' => 18,
                    'E' => 18,
                    'F' => 17,
                    'G' => 30,
                    'H' => 18,
                    'I' => 19,
                    'J' => 38,
                    'K' => 15,
                    'L' => 21,
                    'M' => 21,
                ];

                foreach ($anchos as $columna => $ancho) {
                    $sheet
                        ->getColumnDimension($columna)
                        ->setAutoSize(false);

                    $sheet
                        ->getColumnDimension($columna)
                        ->setWidth($ancho);
                }

                /*
                 * Configuración de impresión.
                 */
                $sheet
                    ->getPageSetup()
                    ->setOrientation(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                    );

                $sheet
                    ->getPageSetup()
                    ->setFitToWidth(1);

                $sheet
                    ->getPageSetup()
                    ->setFitToHeight(0);

                $sheet
                    ->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.3)
                    ->setLeft(0.3)
                    ->setBottom(0.5);

                $sheet->setTitle('Usuarios');
            },
        ];
    }

    private function obtenerEstado(User $usuario): string
    {
        $estado = data_get($usuario, 'estado');

        if ($estado === null || $estado === '') {
            return 'Activo';
        }

        if (is_bool($estado)) {
            return $estado ? 'Activo' : 'Inactivo';
        }

        if (is_numeric($estado)) {
            return (int) $estado === 1 ? 'Activo' : 'Inactivo';
        }

        return ucfirst((string) $estado);
    }

    private function formatearFecha($fecha): string
    {
        if (empty($fecha)) {
            return '—';
        }

        try {
            return Carbon::parse($fecha)->format('d/m/Y h:i A');
        } catch (\Throwable) {
            return '—';
        }
    }
}