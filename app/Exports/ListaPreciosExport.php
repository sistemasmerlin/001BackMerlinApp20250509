<?php

namespace App\Exports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class ListaPreciosExport implements WithEvents, WithTitle
{
    private const MONEDA = '"$" #,##0.00';

    public function __construct(
        private array $productos,
        private string $lista,
        private bool $incluirImagenes = true
    ) {}

    public function title(): string
    {
        return 'RESUMEN PEDIDO';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $evento) {
                $resumen = $evento->sheet->getDelegate();
                $libro = $resumen->getParent();

                $this->construir($libro, $resumen);
            },
        ];
    }

    private function construir(
        Spreadsheet $libro,
        Worksheet $resumen
    ): void {
        $productos = $this->productos;

        usort($productos, function (array $a, array $b) {
            return strnatcasecmp($a['categoria'], $b['categoria'])
                ?: strnatcasecmp($a['marca'], $b['marca'])
                ?: strnatcasecmp($a['referencia'], $b['referencia']);
        });

        $grupos = [];

        foreach ($productos as $producto) {
            $clave = 'cat:' . mb_strtoupper($producto['categoria']);

            if (!isset($grupos[$clave])) {
                $grupos[$clave] = [
                    'categoria' => $producto['categoria'],
                    'productos' => [],
                ];
            }

            $grupos[$clave]['productos'][] = $producto;
        }

        $nombres = [
            'resumen pedido' => true,
            '_base_pedido' => true,
        ];

        // Indexar la carpeta una sola vez.
        $imagenes = $this->incluirImagenes
            ? $this->indiceImagenes()
            : [];

        $enlaces = [];

        foreach ($grupos as $grupo) {
            $nombre = $this->nombreHoja(
                $grupo['categoria'],
                $nombres
            );

            $hoja = $libro->createSheet();
            $hoja->setTitle($nombre);

            $conImagenes = $this->incluirImagenes
                && preg_match(
                    '/\b(LLANTAS?|NEUMATICOS?|REPUESTOS?)\b/',
                    Str::upper(Str::ascii($grupo['categoria']))
                );

            $ultimaColumna = $conImagenes ? 'G' : 'F';

            $this->texto($hoja, 'A1', $grupo['categoria']);
            $this->texto(
                $hoja,
                'A2',
                "Lista {$this->lista} · Generada "
                    . now()->format('d/m/Y H:i')
            );
            $this->texto(
                $hoja,
                'A3',
                'Moneda: COP. Digite las unidades en Cantidad.'
            );

            foreach ([1, 2, 3] as $fila) {
                $hoja->mergeCells("A{$fila}:{$ultimaColumna}{$fila}");
            }

            $encabezados = [
                'Referencia',
                'Descripción',
                'Precio',
                'Precio más IVA',
                'Cantidad',
                'Marca',
            ];

            if ($conImagenes) {
                $encabezados[] = 'Imagen de referencia';
            }

            $hoja->fromArray($encabezados, null, 'A5');

            $fila = 6;

            foreach ($grupo['productos'] as $producto) {
                $this->texto(
                    $hoja,
                    "A{$fila}",
                    $producto['referencia']
                );
                $this->texto(
                    $hoja,
                    "B{$fila}",
                    $producto['descripcion']
                );

                $hoja->setCellValue("C{$fila}", $producto['precio']);
                $hoja->setCellValue("D{$fila}", $producto['precio_iva']);
                $hoja->setCellValue("E{$fila}", 0);

                $this->texto($hoja, "F{$fila}", $producto['marca']);

                $hoja->getRowDimension($fila)
                    ->setRowHeight($conImagenes ? 96 : 32);

                if ($conImagenes) {
                    $ruta = $imagenes['ref:' . strtolower($producto['referencia'])] ?? null;

                    if (!$ruta || !$this->insertarImagen(
                        $hoja,
                        $ruta,
                        $fila
                    )) {
                        $this->texto($hoja, "G{$fila}", 'Sin imagen');
                    }
                }

                $enlaces[] = [
                    'hoja' => $nombre,
                    'fila' => $fila,
                    'marca' => $producto['marca'],
                ];

                $fila++;
            }

            $ultimaFila = $fila - 1;

            $hoja->getColumnDimension('A')->setWidth(20);
            $hoja->getColumnDimension('B')->setWidth(60);
            $hoja->getColumnDimension('C')->setWidth(18);
            $hoja->getColumnDimension('D')->setWidth(20);
            $hoja->getColumnDimension('E')->setWidth(12);
            $hoja->getColumnDimension('F')->setWidth(24);

            if ($conImagenes) {
                $hoja->getColumnDimension('G')->setWidth(24);
            }

            $hoja->getStyle("C6:D{$ultimaFila}")
                ->getNumberFormat()
                ->setFormatCode(self::MONEDA);

            $hoja->getStyle("E6:E{$ultimaFila}")
                ->getNumberFormat()
                ->setFormatCode('0');

            $hoja->getStyle("E6:E{$ultimaFila}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFFFF2CC');

            $this->estilo(
                $hoja,
                $ultimaColumna,
                $ultimaFila
            );

            $this->protegerHoja($hoja, $ultimaFila);

        }


        $base = $libro->createSheet();
        $base->setTitle('_BASE_PEDIDO');

        $this->crearResumen($resumen, $base, $enlaces);

        // Estas hojas se calculan automáticamente y no se diligencian.
        $this->protegerHoja($resumen);
        $this->protegerHoja($base);

        $base->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        // Evitar eliminar, renombrar o mostrar hojas desde Excel.
        $libro->getSecurity()->setLockStructure(true);
        $libro->getSecurity()->setWorkbookPassword('MerlinLista2026!');

        $libro->setActiveSheetIndex(0);
    }

    private function crearResumen(
        Worksheet $resumen,
        Worksheet $base,
        array $enlaces
    ): void {
        $base->fromArray([
            'Marca',
            'Referencia',
            'Descripción',
            'Cantidad',
            'Precio más IVA',
            'Total',
            'Acumulado',
            'Orden seleccionado',
        ], null, 'A1');

        foreach ($enlaces as $indice => $enlace) {
            $fila = $indice + 2;
            $origen = $enlace['fila'];

            $hoja = "'" . str_replace(
                "'",
                "''",
                $enlace['hoja']
            ) . "'";

            $this->texto($base, "A{$fila}", $enlace['marca']);

            $base->setCellValue(
                "B{$fila}",
                "={$hoja}!A{$origen}&\"\""
            );

            $base->setCellValue(
                "C{$fila}",
                "={$hoja}!B{$origen}&\"\""
            );

            $cantidad = "{$hoja}!E{$origen}";

            $base->setCellValue(
                "D{$fila}",
                "=IFERROR(IF(AND(ISNUMBER({$cantidad}),"
                    . "{$cantidad}>0,"
                    . "{$cantidad}=INT({$cantidad})),"
                    . "{$cantidad},0),0)"
            );

            $base->setCellValue(
                "E{$fila}",
                "={$hoja}!D{$origen}"
            );

            $base->setCellValue(
                "F{$fila}",
                "=ROUND(D{$fila}*E{$fila},2)"
            );

            $anterior = $indice === 0 ? '0' : 'G' . ($fila - 1);

            $base->setCellValue(
                "G{$fila}",
                "={$anterior}+IF(D{$fila}>0,1,0)"
            );

            $base->setCellValue(
                "H{$fila}",
                "=IF(D{$fila}>0,G{$fila},\"\")"
            );
        }

        $ultimaBase = count($enlaces) + 1;
        $ultimaResumen = count($enlaces) + 5;

        $this->texto($resumen, 'A1', 'RESUMEN PEDIDO');
        $resumen->mergeCells('A1:F1');

        $this->texto(
            $resumen,
            'A2',
            'Digite cantidades enteras en las hojas de cada categoría. '
                . 'Este resumen no calcula descuentos ni flete.'
        );

        $resumen->mergeCells('A2:F2');

        // No combinar la fila 3: allí están los totales.
        $this->texto($resumen, 'A3', 'Total unidades');

        $resumen->setCellValue(
            'B3',
            "=SUM('_BASE_PEDIDO'!D2:D{$ultimaBase})"
        );

        $this->texto($resumen, 'E3', 'Total pedido');

        $resumen->setCellValue(
            'F3',
            "=SUM('_BASE_PEDIDO'!F2:F{$ultimaBase})"
        );

        $resumen->getStyle('B3')
            ->getNumberFormat()->setFormatCode('0');

        $resumen->getStyle('F3')
            ->getNumberFormat()->setFormatCode(self::MONEDA);

        $resumen->fromArray([
            'Marca',
            'Referencia',
            'Descripción',
            'Cantidad',
            'Precio más IVA',
            'Total',
        ], null, 'A5');

        foreach ($enlaces as $indice => $enlace) {
            $fila = $indice + 6;
            $orden = $indice + 1;

            $coincidencia =
                "MATCH({$orden},"
                . "'_BASE_PEDIDO'!\$H\$2:\$H\${$ultimaBase},0)";

            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $columna) {
                $resumen->setCellValue(
                    "{$columna}{$fila}",
                    "=IFERROR(INDEX("
                        . "'_BASE_PEDIDO'!"
                        . "\${$columna}\$2:"
                        . "\${$columna}\${$ultimaBase},"
                        . "{$coincidencia}),\"\")"
                );
            }
        }

        foreach (
            [
                'A' => 24,
                'B' => 20,
                'C' => 60,
                'D' => 14,
                'E' => 20,
                'F' => 22,
            ] as $columna => $ancho
        ) {
            $resumen->getColumnDimension($columna)
                ->setWidth($ancho);
        }

        $resumen->getStyle("D6:D{$ultimaResumen}")
            ->getNumberFormat()->setFormatCode('0');

        $resumen->getStyle("E6:F{$ultimaResumen}")
            ->getNumberFormat()->setFormatCode(self::MONEDA);

        $this->estilo($resumen, 'F', $ultimaResumen);
        $resumen->getRowDimension(2)->setRowHeight(42);
    }

    private function estilo(
        Worksheet $hoja,
        string $ultimaColumna,
        int $ultimaFila
    ): void {
        $hoja->getStyle("A1:{$ultimaColumna}{$ultimaFila}")
            ->getFont()->setName('Calibri')->setSize(11);

        $hoja->getStyle("A1:{$ultimaColumna}{$ultimaFila}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $hoja->getStyle('A1')->getFont()
            ->setBold(true)->setSize(18)
            ->getColor()->setARGB('FFE30613');

        $cabecera = $hoja->getStyle("A5:{$ultimaColumna}5");

        $cabecera->getFont()
            ->setBold(true)->getColor()->setARGB('FFFFFFFF');

        $cabecera->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE30613');

        $hoja->getRowDimension(1)->setRowHeight(30);
        $hoja->getRowDimension(2)->setRowHeight(30);
        $hoja->getRowDimension(3)->setRowHeight(26);
        $hoja->getRowDimension(5)->setRowHeight(32);

        $hoja->freezePane('A6');
        $hoja->setAutoFilter("A5:{$ultimaColumna}{$ultimaFila}");
    }

    private function texto(
        Worksheet $hoja,
        string $celda,
        string $valor
    ): void {
        // Conserva referencias con ceros iniciales y evita
        // interpretar descripciones como fórmulas.
        $hoja->setCellValueExplicit(
            $celda,
            $valor,
            DataType::TYPE_STRING
        );
    }

    private function nombreHoja(
        string $categoria,
        array &$usados
    ): string {
        $base = str_replace(
            ['\\', '/', ':', '*', '?', '[', ']'],
            ' ',
            $categoria
        );

        $base = preg_replace('/[\x00-\x1F]/u', '', $base);
        $base = trim($base, " \t\n\r\0\x0B'") ?: 'SIN CATEGORIA';

        $nombre = rtrim(mb_substr($base, 0, 31), "'");
        $numero = 2;

        while (isset($usados[mb_strtolower($nombre)])) {
            $sufijo = ' (' . $numero++ . ')';

            $nombre = rtrim(
                mb_substr($base, 0, 31 - mb_strlen($sufijo)),
                "'"
            ) . $sufijo;
        }

        $usados[mb_strtolower($nombre)] = true;

        return $nombre;
    }

    private function indiceImagenes(): array
    {
        $carpeta = public_path('storage/productos');

        if (!is_dir($carpeta)) {
            return [];
        }

        $archivos = [];

        foreach (new \DirectoryIterator($carpeta) as $archivo) {
            if (!$archivo->isFile() || $archivo->isLink()) {
                continue;
            }

            if (!preg_match(
                '/\A([A-Za-z0-9][A-Za-z0-9_-]{0,99})-1'
                    . '\.(jpg|jpeg|png|webp)\z/i',
                $archivo->getFilename(),
                $coincidencia
            )) {
                continue;
            }

            $archivos[] = [
                'clave' => 'ref:' . strtolower($coincidencia[1]),
                'ruta' => $archivo->getPathname(),
            ];
        }

        usort(
            $archivos,
            fn($a, $b) => strcmp($a['ruta'], $b['ruta'])
        );

        $resultado = [];

        foreach ($archivos as $archivo) {
            $resultado[$archivo['clave']] ??= $archivo['ruta'];
        }

        return $resultado;
    }

    private function insertarImagen(
        Worksheet $hoja,
        string $ruta,
        int $fila
    ): bool {
        $datos = @getimagesize($ruta);

        if (!$datos || !is_readable($ruta)) {
            return false;
        }

        /*
         * Con GD se genera una miniatura en memoria:
         * evita incrustar fotos originales de varios MB.
         * También convierte WebP a JPEG.
         */
        if (
            function_exists('imagecreatefromstring')
            && function_exists('imagejpeg')
        ) {
            $contenido = @file_get_contents($ruta);

            if ($contenido === false) {
                return false;
            }

            $original = @imagecreatefromstring($contenido);

            if ($original === false) {
                return false;
            }

            $lado = 180;
            $miniatura = imagecreatetruecolor($lado, $lado);
            $blanco = imagecolorallocate($miniatura, 255, 255, 255);

            imagefill($miniatura, 0, 0, $blanco);

            $escala = min(
                $lado / imagesx($original),
                $lado / imagesy($original)
            );

            $ancho = max(1, (int) round(imagesx($original) * $escala));
            $alto = max(1, (int) round(imagesy($original) * $escala));

            imagecopyresampled(
                $miniatura,
                $original,
                (int) (($lado - $ancho) / 2),
                (int) (($lado - $alto) / 2),
                0,
                0,
                $ancho,
                $alto,
                imagesx($original),
                imagesy($original)
            );

            imagedestroy($original);

            $dibujo = new MemoryDrawing();
            $dibujo->setImageResource($miniatura);
            $dibujo->setRenderingFunction(
                MemoryDrawing::RENDERING_JPEG
            );
            $dibujo->setMimeType(MemoryDrawing::MIMETYPE_JPEG);
            $dibujo->setHeight(112);
        } else {
            // Sin GD se pueden insertar JPG y PNG originales.
            if (!in_array(
                $datos['mime'] ?? '',
                ['image/jpeg', 'image/png'],
                true
            )) {
                return false;
            }

            $dibujo = new Drawing();
            $dibujo->setPath($ruta);
            $dibujo->setWidthAndHeight(112, 112);
        }

        $dibujo->setName('Imagen de referencia');
        $dibujo->setCoordinates("G{$fila}");
        $dibujo->setOffsetX(20);
        $dibujo->setOffsetY(8);
        $dibujo->setWorksheet($hoja);

        return true;
    }


    private function protegerHoja(
        Worksheet $hoja,
        ?int $ultimaFilaEditable = null
    ): void {
        // Todas las celdas quedan bloqueadas por defecto.
        $hoja->getStyle($hoja->calculateWorksheetDimension())
            ->getProtection()
            ->setLocked(Protection::PROTECTION_PROTECTED);

        // Únicamente las cantidades de los productos quedan editables.
        if ($ultimaFilaEditable !== null && $ultimaFilaEditable >= 6) {
            $hoja->getStyle("E6:E{$ultimaFilaEditable}")
                ->getProtection()
                ->setLocked(Protection::PROTECTION_UNPROTECTED);
        }

        $proteccion = $hoja->getProtection();

        // Cambie esta contraseña por una de administración.
        $proteccion->setPassword('MerlinLista2026!');
        $proteccion->setSheet(true);

        // Permitir utilizar los filtros existentes.
        $proteccion->setAutoFilter(false);

        // Impedir alterar la estructura y el formato.
        $proteccion->setSort(true);
        $proteccion->setInsertRows(true);
        $proteccion->setDeleteRows(true);
        $proteccion->setInsertColumns(true);
        $proteccion->setDeleteColumns(true);
        $proteccion->setFormatCells(true);
        $proteccion->setFormatRows(true);
        $proteccion->setFormatColumns(true);
        $proteccion->setObjects(true);

        // Permitir seleccionar y copiar, sin editar las bloqueadas.
        $proteccion->setSelectLockedCells(false);
        $proteccion->setSelectUnlockedCells(false);
    }
}
