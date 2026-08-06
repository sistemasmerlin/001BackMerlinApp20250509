<?php

namespace App\Imports;

use App\Models\MovimientoBancario;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class MovimientosBancariosImport implements
    OnEachRow,
    SkipsEmptyRows,
    WithChunkReading
{
    public int $importados = 0;
    public int $duplicados = 0;
    public int $omitidos = 0;

    public array $errores = [];

    public function __construct(
        protected string $nombreArchivo
    ) {
    }

    public function onRow(Row $row): void
    {
        $numeroFila = $row->getIndex();
        $datos = $row->toArray();

        try {
            /*
            |--------------------------------------------------------------------------
            | Columnas del archivo
            |--------------------------------------------------------------------------
            |
            | 0: Fecha
            | 1: Oficina o canal
            | 2: Descripción
            | 3: Referencia 1
            | 4: Referencia 2
            | 5: Referencia 3
            | 6: Valor
            | 7: Campo secondary-menu, se ignora
            */

            $fechaOriginal = $datos[0] ?? null;
            $oficinaCanal = $this->limpiarTexto($datos[1] ?? null);
            $descripcion = $this->limpiarTexto($datos[2] ?? null);
            $referencia1 = $this->normalizarReferencia($datos[3] ?? null);
            $referencia2 = $this->normalizarReferencia($datos[4] ?? null);
            $referencia3 = $this->normalizarReferencia($datos[5] ?? null);
            $valorOriginal = $datos[6] ?? null;

            /*
            |--------------------------------------------------------------------------
            | Ignorar filas completamente vacías
            |--------------------------------------------------------------------------
            */

            if (
                blank($fechaOriginal) &&
                blank($descripcion) &&
                blank($valorOriginal)
            ) {
                $this->omitidos++;
                return;
            }

            if (blank($fechaOriginal)) {
                throw new \RuntimeException(
                    'La fecha del movimiento está vacía.'
                );
            }

            if (blank($descripcion)) {
                throw new \RuntimeException(
                    'La descripción del movimiento está vacía.'
                );
            }

            if (blank($valorOriginal)) {
                throw new \RuntimeException(
                    'El valor del movimiento está vacío.'
                );
            }

            $fechaMovimiento = $this->convertirFecha($fechaOriginal);
            $valor = $this->convertirValor($valorOriginal);

            $tipoMovimiento = $valor < 0
                ? 'DEBITO'
                : 'CREDITO';

            /*
            |--------------------------------------------------------------------------
            | Hash para evitar duplicados
            |--------------------------------------------------------------------------
            */

            $hash = hash('sha256', implode('|', [
                $fechaMovimiento->format('Y-m-d'),
                mb_strtoupper($oficinaCanal ?? ''),
                mb_strtoupper($descripcion),
                mb_strtoupper($referencia1 ?? ''),
                mb_strtoupper($referencia2 ?? ''),
                mb_strtoupper($referencia3 ?? ''),
                number_format($valor, 2, '.', ''),
            ]));

            $existe = MovimientoBancario::query()
                ->where('hash_movimiento', $hash)
                ->exists();

            if ($existe) {
                $this->duplicados++;
                return;
            }

            MovimientoBancario::create([
                'archivo_origen' => $this->nombreArchivo,
                'fila_origen' => $numeroFila,
                'fecha_importacion' => now(),

                'fecha_movimiento' => $fechaMovimiento->format('Y-m-d'),
                'oficina_canal' => $oficinaCanal,
                'descripcion_movimiento' => $descripcion,

                'referencia_1' => $referencia1,
                'referencia_2' => $referencia2,
                'referencia_3' => $referencia3,

                'moneda' => 'COP',
                'valor' => $valor,
                'tipo_movimiento' => $tipoMovimiento,

                'fecha_original' => $this->valorOriginalTexto(
                    $fechaOriginal
                ),

                'valor_original' => $this->valorOriginalTexto(
                    $valorOriginal
                ),

                'hash_movimiento' => $hash,

                'procesado' => false,
                'fecha_procesado' => null,
                'observaciones' => null,

                'usuario_importacion_id' => Auth::id(),
            ]);

            $this->importados++;
        } catch (Throwable $e) {
            $this->omitidos++;

            $this->errores[] = [
                'fila' => $numeroFila,
                'mensaje' => $e->getMessage(),
            ];

            Log::warning('Fila de movimiento bancario no importada', [
                'archivo' => $this->nombreArchivo,
                'fila' => $numeroFila,
                'mensaje' => $e->getMessage(),
                'datos' => $datos,
            ]);
        }
    }

    private function convertirFecha(mixed $valor): Carbon
    {
        if ($valor instanceof DateTimeInterface) {
            return Carbon::instance($valor)->startOfDay();
        }

        if (is_numeric($valor)) {
            return Carbon::instance(
                Date::excelToDateTimeObject((float) $valor)
            )->startOfDay();
        }

        $fecha = trim((string) $valor);

        if ($fecha === '') {
            throw new \RuntimeException(
                'La fecha no tiene un formato válido.'
            );
        }

        /*
         * Reemplazo de meses en español por meses reconocidos
         * de forma estable por Carbon.
         */
        $meses = [
            'ene' => 'jan',
            'feb' => 'feb',
            'mar' => 'mar',
            'abr' => 'apr',
            'may' => 'may',
            'jun' => 'jun',
            'jul' => 'jul',
            'ago' => 'aug',
            'sep' => 'sep',
            'sept' => 'sep',
            'oct' => 'oct',
            'nov' => 'nov',
            'dic' => 'dec',
        ];

        $fechaMinuscula = mb_strtolower($fecha);

        foreach ($meses as $espanol => $ingles) {
            $fechaMinuscula = preg_replace(
                '/-' . preg_quote($espanol, '/') . '-/iu',
                '-' . $ingles . '-',
                $fechaMinuscula
            );
        }

        foreach ([
            'd-M-y',
            'd-M-Y',
            'd/m/Y',
            'd/m/y',
            'Y-m-d',
        ] as $formato) {
            try {
                $resultado = Carbon::createFromFormat(
                    $formato,
                    $fechaMinuscula
                );

                if ($resultado !== false) {
                    return $resultado->startOfDay();
                }
            } catch (Throwable) {
                // Intenta el siguiente formato.
            }
        }

        try {
            return Carbon::parse($fechaMinuscula)->startOfDay();
        } catch (Throwable) {
            throw new \RuntimeException(
                "No fue posible interpretar la fecha: {$fecha}."
            );
        }
    }

    private function convertirValor(mixed $valor): float
    {
        if (is_int($valor) || is_float($valor)) {
            return round((float) $valor, 2);
        }

        $texto = trim((string) $valor);

        if ($texto === '') {
            throw new \RuntimeException(
                'El valor está vacío.'
            );
        }

        /*
         * Ejemplos:
         *
         * COP -$ 3.057,37  => -3057.37
         * COP $ 4.539.024,00 => 4539024.00
         */

        $esNegativo =
            str_contains($texto, '-$') ||
            preg_match('/-\s*\$/u', $texto) === 1 ||
            str_starts_with(
                preg_replace('/\s+/u', '', $texto),
                '-'
            );

        $texto = str_replace(
            [
                'COP',
                '$',
                "\u{00A0}",
                ' ',
                '-',
            ],
            '',
            $texto
        );

        $texto = str_replace('.', '', $texto);
        $texto = str_replace(',', '.', $texto);

        $texto = preg_replace('/[^0-9.]/', '', $texto);

        if ($texto === '' || !is_numeric($texto)) {
            throw new \RuntimeException(
                "No fue posible interpretar el valor: {$valor}."
            );
        }

        $numero = round((float) $texto, 2);

        return $esNegativo
            ? abs($numero) * -1
            : abs($numero);
    }

    private function limpiarTexto(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim(
            preg_replace('/\s+/u', ' ', (string) $valor)
        );

        if (
            $texto === '' ||
            $texto === '-' ||
            $texto === '- -'
        ) {
            return null;
        }

        return $texto;
    }

    private function normalizarReferencia(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        /*
         * Si Excel entrega una referencia numérica, se convierte
         * sin notación científica.
         */
        if (is_int($valor)) {
            return (string) $valor;
        }

        if (is_float($valor)) {
            return sprintf('%.0f', $valor);
        }

        $texto = $this->limpiarTexto($valor);

        if ($texto === null) {
            return null;
        }

        /*
         * Convierte referencias tipo 8,08297E+16 a texto entero.
         * Puede existir pérdida de precisión si Excel ya guardó
         * el dato como número en vez de texto.
         */
        $cientifico = str_replace(',', '.', $texto);

        if (preg_match('/^[+-]?\d+(\.\d+)?E[+-]?\d+$/i', $cientifico)) {
            return sprintf('%.0f', (float) $cientifico);
        }

        return $texto;
    }

    private function valorOriginalTexto(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if ($valor instanceof DateTimeInterface) {
            return $valor->format('Y-m-d');
        }

        return trim((string) $valor);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}