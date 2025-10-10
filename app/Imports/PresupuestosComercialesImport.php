<?php

namespace App\Imports;

use App\Models\PresupuestoComercial;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class PresupuestosComercialesImport implements ToCollection, WithHeadingRow, WithCustomCsvSettings
{
    public int $creados = 0;
    public int $actualizados = 0;
    public array $errores = [];

    public function __construct(private string $delimiter = ',')
    {
    }

    // Permite configurar separador y codificación
    public function getCsvSettings(): array
    {
        return [
            'delimiter'       => $this->delimiter,
            'input_encoding'  => 'UTF-8', // forzamos UTF-8
        ];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $fila = $i + 2; // encabezados en fila 1

            try {
                // --- Helper para leer una clave tolerando BOM y espacios ---
                $val = function(array|Collection $r, string $key): string {
                    $k = $key;
                    if ($r instanceof Collection) $r = $r->toArray();

                    // Claves posibles: normal, con BOM, con espacios/upper
                    $candidatos = [
                        $k,
                        "\xEF\xBB\xBF".$k,      // BOM en primera cabecera
                        trim($k),
                        strtolower($k),
                        str_replace(' ', '_', strtolower($k)),
                    ];
                    foreach ($candidatos as $cand) {
                        if (array_key_exists($cand, $r) && $r[$cand] !== null) {
                            return trim((string)$r[$cand]);
                        }
                    }
                    return '';
                };

                $periodo   = $val($row, 'periodo');
                $asesor    = $val($row, 'codigo_asesor');
                $tipo      = $val($row, 'tipo_presupuesto');
                $rawPresu  = $val($row, 'presupuesto');
                $marca     = $val($row, 'marca') ?: null;
                $categoria = $val($row, 'categoria') ?: null;
                $clasif    = $val($row, 'clasificacion_asesor') ?: null;

                // Validaciones básicas
                if (!preg_match('/^\d{6}$/', $periodo)) {
                    throw new \Exception("Periodo inválido (YYYYMM) en fila {$fila}");
                }
                if ($asesor === '') {
                    throw new \Exception("Falta codigo_asesor en fila {$fila}");
                }
                if ($tipo === '') {
                    throw new \Exception("Falta tipo_presupuesto en fila {$fila}");
                }
                if (!in_array($categoria, ['llantas','repuestos','total'])) {
                    throw new \Exception("Categoría debe ser 'llantas' o 'repuestos' en fila {$fila}");
                }

                // Normalizar presupuesto (soporta 15.000,50 | 15,000,000.75 | 15000000)
                $clean = str_replace(["\xC2\xA0", ' '], '', $rawPresu); // espacios duros y normales
                if (str_contains($clean, ',') && str_contains($clean, '.')) {
                    // Formato ES (15.000,50): quitar miles '.' y pasar ',' a '.'
                    $clean = str_replace('.', '', $clean);
                    $clean = str_replace(',', '.', $clean);
                } elseif (str_contains($clean, ',') && !str_contains($clean, '.')) {
                    // Solo coma => tratar como decimal
                    $clean = str_replace(',', '.', $clean);
                } else {
                    // US (15,000,000.75) o solo dígitos => quitar comas de miles
                    $clean = str_replace(',', '', $clean);
                }
                $presupuesto = (float)preg_replace('/[^\d\.\-]/', '', $clean);

                $keys = [
                    'codigo_asesor'    => $asesor,
                    'periodo'          => $periodo,
                    'marca'            => $marca,
                    'categoria'        => $categoria,
                    'tipo_presupuesto' => $tipo,
                ];

                $vals = [
                    'presupuesto'          => $presupuesto,
                    'clasificacion_asesor' => $clasif,
                ];

                $m = PresupuestoComercial::updateOrCreate($keys, $vals);
                $m->wasRecentlyCreated ? $this->creados++ : $this->actualizados++;
            } catch (\Throwable $e) {
                $this->errores[] = "Fila {$fila}: ".$e->getMessage();
            }
        }
    }
}
