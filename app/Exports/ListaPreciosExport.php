<?php

namespace App\Exports;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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
        // Leer la configuración vigente una sola vez al generar el archivo.
        $configuraciones = DB::table('listas_precios_subcategorias')
            ->where('activa', true)->orderBy('orden')->orderBy('nombre')->get();
        $ids = $configuraciones->pluck('id');
        $marcas = DB::table('listas_precios_subcategoria_marcas')
            ->whereIn('subcategoria_id', $ids)->get()->groupBy('subcategoria_id');
        $rangos = DB::table('listas_precios_subcategoria_rangos')
            ->whereIn('subcategoria_id', $ids)->orderBy('desde')->get()->groupBy('subcategoria_id');
        $porMarca = [];
        foreach ($configuraciones as $config) {
            foreach ($marcas->get($config->id, collect()) as $marca) {
                $porMarca[mb_strtoupper(trim($marca->marca_excel))] = [
                    'id' => $config->id, 'nombre' => $config->nombre,
                    'tipo' => $config->tipo_calculo,
                    'rangos' => $rangos->get($config->id, collect())->all(),
                    'orden' => (int) $config->orden,
                ];
            }
        }
        $grupos = [];
        foreach ($this->productos as $producto) {
            $config = $porMarca[mb_strtoupper(trim($producto['marca']))] ?? null;
            $clave = $config ? 'sub:'.$config['id'] : 'cat:'.mb_strtoupper($producto['categoria']);
            if (!isset($grupos[$clave])) {
                $grupos[$clave] = [
                    'categoria' => $config['nombre'] ?? $producto['categoria'],
                    'config' => $config, 'productos' => [],
                ];
            }
            $grupos[$clave]['productos'][] = $producto;
        }
        uasort($grupos, function ($a, $b) {
            $ordenA = $a['config']['orden'] ?? PHP_INT_MAX;
            $ordenB = $b['config']['orden'] ?? PHP_INT_MAX;
            return ($ordenA <=> $ordenB) ?: strnatcasecmp($a['categoria'], $b['categoria']);
        });
        foreach ($grupos as &$grupoOrdenado) {
            usort($grupoOrdenado['productos'], fn ($a, $b) =>
                strnatcasecmp($a['marca'], $b['marca']) ?: strnatcasecmp($a['referencia'], $b['referencia'])
            );
        }
        unset($grupoOrdenado);

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

            // La decisión de imágenes depende de la categoría original del producto.
            $conImagenes = $this->incluirImagenes && collect($grupo['productos'])->contains(
                fn ($p) => (bool) preg_match('/\b(LLANTAS?|NEUMATICOS?|REPUESTOS?)\b/', Str::upper(Str::ascii($p['categoria'])))
            );
            $porcentajes = collect($grupo['config']['rangos'] ?? [])
                ->map(fn ($r) => (float) $r->porcentaje)->unique()->sort()->values()->all();
            $ultimaColumna = count($porcentajes) ? Coordinate::stringFromColumnIndex(7 + count($porcentajes)) : ($conImagenes ? 'G' : 'F');

            $indiceColumnaAuxiliar =
                Coordinate::columnIndexFromString($ultimaColumna) + 1;

            $columnaAuxiliar = Coordinate::stringFromColumnIndex(
                $indiceColumnaAuxiliar
            );

            $this->texto($hoja, 'A1', $grupo['categoria']);
            $this->texto(
                $hoja,
                'A2',
                "Lista {$this->lista} · Generada "
                    . now()->format('d/m/Y H:i')
            );


            $escalas = [];

                foreach ($grupo['config']['rangos'] ?? [] as $rango) {
                    $esUnidades = ($grupo['config']['tipo'] ?? '') === 'unidades';

                    $desde = $esUnidades
                        ? number_format((float) $rango->desde, 0, ',', '.')
                        : '$' . number_format((float) $rango->desde, 2, ',', '.');

                    $hasta = $rango->hasta === null
                        ? null
                        : (
                            $esUnidades
                                ? number_format((float) $rango->hasta, 0, ',', '.')
                                : '$' . number_format(
                                    (float) $rango->hasta,
                                    2,
                                    ',',
                                    '.'
                                )
                        );

                    $porcentaje = rtrim(
                        rtrim(
                            number_format((float) $rango->porcentaje, 2, ',', ''),
                            '0'
                        ),
                        ','
                    );

                    $intervalo = $hasta === null
                        ? "Desde {$desde}"
                        : "{$desde} a {$hasta}";

                    $escalas[] = "{$intervalo}: {$porcentaje}%";
                }

                if (count($escalas) > 0) {
                    $tipoEscala = ($grupo['config']['tipo'] ?? '') === 'unidades'
                        ? 'Escalas por unidades'
                        : 'Escalas por subtotal antes de IVA';

                    $textoEncabezado = $tipoEscala
                        . ' | '
                        . implode('  ·  ', $escalas);
                } else {
                    $textoEncabezado =
                        'Moneda: COP. Digite las unidades en Cantidad. '
                        . 'Esta hoja no tiene descuentos configurados.';
                }

$this->texto($hoja, 'A3', $textoEncabezado);


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
            foreach ($porcentajes as $i => $porcentaje) {
                $columna = Coordinate::stringFromColumnIndex(8 + $i);
                $this->texto($hoja, $columna.'5', 'Precio sin IVA - '.rtrim(rtrim(number_format($porcentaje, 2, '.', ''), '0'), '.').'%');
                $hoja->getColumnDimension($columna)->setWidth(22);
            }

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

                $hoja->setCellValue(
                    "{$columnaAuxiliar}{$fila}",
                    "=IFERROR(IF(AND("
                        . "ISNUMBER(E{$fila}),"
                        . "E{$fila}>0,"
                        . "E{$fila}=INT(E{$fila})"
                        . "),E{$fila},0),0)"
                );

                $this->texto($hoja, "F{$fila}", $producto['marca']);
                foreach ($porcentajes as $i => $porcentaje) {
                    $columna = Coordinate::stringFromColumnIndex(8 + $i);
                    $factor = number_format($porcentaje / 100, 6, '.', '');
                    $hoja->setCellValue("{$columna}{$fila}", "=ROUND(C{$fila}*(1-{$factor}),2)");
                    $hoja->getStyle("{$columna}{$fila}")->getNumberFormat()->setFormatCode(self::MONEDA);
                }

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
            $this->texto($hoja, 'A4', ($grupo['config']['tipo'] ?? '') === 'unidades' ? 'Unidades válidas' : 'Subtotal sin IVA');
            // Se ignoran cantidades vacías, texto, fracciones y negativos, igual que en el resumen.

            $rangoCantidades =
    "{$columnaAuxiliar}6:{$columnaAuxiliar}{$ultimaFila}";

if (($grupo['config']['tipo'] ?? '') === 'unidades') {
    // Suma las cantidades válidas de toda la subcategoría.
    $hoja->setCellValue(
        'B4',
        "=SUM({$rangoCantidades})"
    );
} else {
    // Suma precio antes de IVA × cantidad válida.
    $hoja->setCellValue(
        'B4',
        "=SUMPRODUCT(C6:C{$ultimaFila},{$rangoCantidades})"
    );
}

// La columna auxiliar no necesita ser visible.
$hoja->getColumnDimension($columnaAuxiliar)
    ->setVisible(false);
            

            $this->texto($hoja, 'C4', 'Descuento aplicado');
            $formulaDescuento = '0';
            foreach (array_reverse($grupo['config']['rangos'] ?? []) as $rango) {
                $desde = number_format((float) $rango->desde, 2, '.', '');
                $hasta = $rango->hasta === null ? null : number_format((float) $rango->hasta, 2, '.', '');
                $porcentaje = number_format((float) $rango->porcentaje / 100, 6, '.', '');
                $condicion = $hasta === null ? "B4>={$desde}" : "AND(B4>={$desde},B4<={$hasta})";
                $formulaDescuento = "IF({$condicion},{$porcentaje},{$formulaDescuento})";
            }
            $hoja->setCellValue('D4', '='.$formulaDescuento);
            $hoja->getStyle('D4')->getNumberFormat()->setFormatCode('0.00%');
            $hoja->getStyle('B4')->getNumberFormat()->setFormatCode(($grupo['config']['tipo'] ?? '') === 'unidades' ? '0' : self::MONEDA);

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

// Dar espacio para mostrar las escalas del encabezado.
$hoja->getRowDimension(3)->setRowHeight(42);

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
            'Descuento',
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
                "=ROUND({$hoja}!D{$origen}*(1-{$hoja}!\$D\$4),2)"
            );

            $base->setCellValue(
                "F{$fila}",
                "=ROUND(D{$fila}*E{$fila},2)"
            );

            $base->setCellValue("I{$fila}", "={$hoja}!\$D\$4");

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
        $resumen->mergeCells('A1:G1');

        $this->texto(
            $resumen,
            'A2',
            'Digite cantidades enteras en las hojas de cada categoría. '
                . 'Incluye descuentos configurados. El flete no está incluido.'
        );

        $resumen->mergeCells('A2:G2');

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
            'Precio con descuento e IVA',
            'Total',
            'Descuento aplicado',
        ], null, 'A5');

        foreach ($enlaces as $indice => $enlace) {
            $fila = $indice + 6;
            $orden = $indice + 1;

            $coincidencia =
                "MATCH({$orden},"
                . "'_BASE_PEDIDO'!\$H\$2:\$H\${$ultimaBase},0)";

            foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $columna) {
                $columnaBase = $columna === 'G' ? 'I' : $columna;
                $resumen->setCellValue(
                    "{$columna}{$fila}",
                    "=IFERROR(INDEX("
                        . "'_BASE_PEDIDO'!"
                        . "\${$columnaBase}\$2:"
                        . "\${$columnaBase}\${$ultimaBase},"
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
                'G' => 22,
            ] as $columna => $ancho
        ) {
            $resumen->getColumnDimension($columna)
                ->setWidth($ancho);
        }

        $resumen->getStyle("D6:D{$ultimaResumen}")
            ->getNumberFormat()->setFormatCode('0');

        $resumen->getStyle("E6:F{$ultimaResumen}")
            ->getNumberFormat()->setFormatCode(self::MONEDA);

        $resumen->getStyle("G6:G{$ultimaResumen}")->getNumberFormat()->setFormatCode('0.00%');
        $this->estilo($resumen, 'G', $ultimaResumen);
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
