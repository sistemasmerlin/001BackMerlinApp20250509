<?php

namespace App\Imports;

use App\Models\ProductoEmpaque;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductosEmpaquesImport implements
    ToCollection,
    WithHeadingRow,
    SkipsEmptyRows
{
    public int $procesadas = 0;

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo no contiene registros.',
            ]);
        }

        $encabezados = [
            'referencia',
            'descripcion',
            'unidades_por_empaque',
        ];

        if (array_diff($encabezados, array_keys($rows->first()->toArray()))) {
            throw ValidationException::withMessages([
                'archivo' => 'Los encabezados deben ser: referencia, descripcion, unidades_por_empaque.',
            ]);
        }

        $datos = $rows->map(fn ($row) => [
            'referencia' => trim((string) ($row['referencia'] ?? '')),
            'descripcion' => trim((string) ($row['descripcion'] ?? '')),
            'unidades_por_empaque' => $row['unidades_por_empaque'] ?? null,
        ])->values()->all();

        Validator::make(
            ['filas' => $datos],
            [
                'filas' => ['required', 'array', 'min:1'],
                'filas.*.referencia' => [
                    'required', 'string', 'max:100', 'distinct:ignore_case',
                ],
                'filas.*.descripcion' => [
                    'required', 'string', 'max:255',
                ],
                'filas.*.unidades_por_empaque' => [
                    'required', 'integer', 'min:1', 'max:2147483647',
                ],
            ],
            [
                'filas.*.referencia.required' =>
                    'Falta una referencia.',
                'filas.*.referencia.distinct' =>
                    'Hay referencias repetidas en el archivo.',
                'filas.*.referencia.max' =>
                    'Una referencia supera los 100 caracteres.',
                'filas.*.descripcion.required' =>
                    'Falta la descripción de un producto.',
                'filas.*.descripcion.max' =>
                    'Una descripción supera los 255 caracteres.',
                'filas.*.unidades_por_empaque.required' =>
                    'Faltan las unidades por empaque.',
                'filas.*.unidades_por_empaque.integer' =>
                    'Las unidades por empaque deben ser un número entero.',
                'filas.*.unidades_por_empaque.min' =>
                    'Las unidades por empaque deben ser mayores que cero.',
                'filas.*.unidades_por_empaque.max' =>
                    'Las unidades por empaque superan el máximo permitido.',
            ]
        )->validate();

        DB::transaction(function () use ($datos) {
            foreach ($datos as $fila) {
                ProductoEmpaque::updateOrCreate(
                    ['referencia' => $fila['referencia']],
                    [
                        'descripcion' => $fila['descripcion'],
                        'unidades_por_empaque' =>
                            (int) $fila['unidades_por_empaque'],
                    ]
                );
            }
        });

        $this->procesadas = count($datos);
    }
}