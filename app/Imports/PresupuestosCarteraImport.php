<?php

namespace App\Imports;

use App\Models\PresupuestoRecaudo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class PresupuestosCarteraImport implements ToCollection, WithHeadingRow, WithCustomCsvSettings
{
    public int $creados = 0;
    public int $actualizados = 0;
    public array $errores = [];
    private string $creadoPor;

    public function __construct(
        private string $delimiter = ',',
        string $creadoPor
    ) {
        $this->creadoPor = $creadoPor;
    }
    public function getCsvSettings(): array
    {
        return [
            'delimiter'      => $this->delimiter,
            'input_encoding' => 'UTF-8',
        ];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $fila = $i + 2; // encabezado en fila 1

            try {
                // Helper para leer claves tolerando BOM, espacios y variantes
                $val = function (array|Collection $r, string $key): string {
                    if ($r instanceof Collection) $r = $r->toArray();

                    $k = $key;
                    $candidatos = [
                        $k,
                        "\xEF\xBB\xBF" . $k,              // BOM
                        trim($k),
                        strtolower($k),
                        strtoupper($k),
                        str_replace(' ', '_', strtolower($k)),
                        str_replace(' ', '', strtolower($k)),
                    ];

                    foreach ($candidatos as $cand) {
                        if (array_key_exists($cand, $r) && $r[$cand] !== null) {
                            return trim((string) $r[$cand]);
                        }
                    }
                    return '';
                };

                // Campos del archivo
                $asesor        = $val($row, 'asesor');
                $nombre_asesor = $val($row, 'nombre_asesor');
                $prefijo       = $val($row, 'prefijo');
                $consecutivo   = $val($row, 'consecutivo');
                $rawSaldo      = $val($row, 'saldo');
                $nit_cliente   = $val($row, 'nit_cliente');
                $cliente       = $val($row, 'cliente');
                $fecha_doc     = $val($row, 'fecha_doc');   // 20251015
                $fecha_corte   = $val($row, 'fecha_corte'); // 20251201
                $dias          = $val($row, 'dias');
                $cond_pago     = $val($row, 'cond_pago');
                $periodo       = $val($row, 'periodo');     // 202512

                // Validaciones básicas
                if (!preg_match('/^\d{6}$/', $periodo)) {
                    throw new \Exception("Periodo inválido (YYYYMM) en fila {$fila}");
                }
                if ($asesor === '')      throw new \Exception("Falta asesor en fila {$fila}");
                if ($nombre_asesor === '') throw new \Exception("Falta nombre_asesor en fila {$fila}");
                if ($prefijo === '')     throw new \Exception("Falta prefijo en fila {$fila}");
                if ($consecutivo === '') throw new \Exception("Falta consecutivo en fila {$fila}");
                if ($nit_cliente === '') throw new \Exception("Falta nit_cliente en fila {$fila}");
                if ($cliente === '')     throw new \Exception("Falta cliente en fila {$fila}");
                if (!preg_match('/^\d{8}$/', $fecha_doc)) {
                    throw new \Exception("fecha_doc inválida (YYYYMMDD) en fila {$fila}");
                }
                if (!preg_match('/^\d{8}$/', $fecha_corte)) {
                    throw new \Exception("fecha_corte inválida (YYYYMMDD) en fila {$fila}");
                }

                // Normalizar SALDO (soporta 15.000,50 | 15,000,000.75 | 15000000)
                $saldo = $this->parseNumber($rawSaldo);

                // Normalizar días (entero)
                $diasNum = (int) $this->parseNumber($dias);

                // ✅ Llave única recomendada para evitar duplicados del mismo documento:
                // asesor + periodo + prefijo + consecutivo
                $keys = [
                    'asesor'      => $asesor,
                    'periodo'     => $periodo,
                    'prefijo'     => $prefijo,
                    'consecutivo' => $consecutivo,
                ];

                $vals = [
                    'nombre_asesor' => $nombre_asesor,
                    'saldo'         => $saldo,
                    'nit_cliente'   => $nit_cliente,
                    'cliente'       => $cliente,
                    'fecha_doc'     => $fecha_doc,
                    'fecha_corte'   => $fecha_corte,
                    'dias'          => $diasNum,
                    'cond_pago'     => $cond_pago,
                    // si tu tabla exige creado_por y no lo tienes en excel,
                    // debes permitir nullable en migración o setear un default aquí.
                    'creado_por' => $this->creadoPor,
                    'estado'        => 1,
                    'eliminado'     => 0,
                ];

                $m = PresupuestoRecaudo::updateOrCreate($keys, $vals);
                $m->wasRecentlyCreated ? $this->creados++ : $this->actualizados++;

            } catch (\Throwable $e) {
                $this->errores[] = "Fila {$fila}: " . $e->getMessage();
            }
        }
    }

    private function parseNumber(?string $raw): float
    {
        $raw = (string)($raw ?? '');
        $clean = str_replace(["\xC2\xA0", ' '], '', $raw);

        if (str_contains($clean, ',') && str_contains($clean, '.')) {
            // ES: 15.000,50
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (str_contains($clean, ',') && !str_contains($clean, '.')) {
            // 15000,50
            $clean = str_replace(',', '.', $clean);
        } else {
            // US: 15,000,000.75 o solo dígitos
            $clean = str_replace(',', '', $clean);
        }

        return (float) preg_replace('/[^\d\.\-]/', '', $clean);
    }
}
