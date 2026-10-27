<?php

namespace App\Livewire\Admin\ListasPrecios;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{
    public ?int $subcategoriaId = null;

    public string $nombre = '';

    public string $tipoCalculo = 'subtotal';

    public bool $activa = true;

    public int $orden = 0;

    /**
     * Una marca por línea.
     */
    public string $marcasTexto = '';

    /**
     * Cada rango contiene:
     * desde, hasta y porcentaje.
     */
    public array $rangos = [];

    public function mount(): void
    {
        Gate::authorize('Administrar imagenes productos');

        $this->iniciarFormulario();
    }

    public function iniciarFormulario(): void
    {
        $this->resetValidation();

        $this->subcategoriaId = null;
        $this->nombre = '';
        $this->tipoCalculo = 'subtotal';
        $this->activa = true;
        $this->orden = 0;
        $this->marcasTexto = '';

        $this->rangos = [
            [
                'desde' => '',
                'hasta' => '',
                'porcentaje' => '',
            ],
        ];
    }

    public function agregarRango(): void
    {
        Gate::authorize('Administrar imagenes productos');

        $this->rangos[] = [
            'desde' => '',
            'hasta' => '',
            'porcentaje' => '',
        ];
    }

    public function eliminarRango(int $indice): void
    {
        Gate::authorize('Administrar imagenes productos');

        if (!array_key_exists($indice, $this->rangos)) {
            return;
        }

        unset($this->rangos[$indice]);

        $this->rangos = array_values($this->rangos);

        if (count($this->rangos) === 0) {
            $this->agregarRango();
        }
    }

    public function editarSubcategoria(int $id): void
    {
        Gate::authorize('Administrar imagenes productos');

        $subcategoria = DB::table('listas_precios_subcategorias')
            ->where('id', $id)
            ->first();

        if (!$subcategoria) {
            session()->flash(
                'subcategoria_error',
                'La subcategoría ya no existe.'
            );

            return;
        }

        $marcas = DB::table('listas_precios_subcategoria_marcas')
            ->where('subcategoria_id', $id)
            ->orderBy('marca_excel')
            ->pluck('marca_excel')
            ->all();

        $rangos = DB::table('listas_precios_subcategoria_rangos')
            ->where('subcategoria_id', $id)
            ->orderBy('desde')
            ->get();

        $this->resetValidation();

        $this->subcategoriaId = $id;
        $this->nombre = $subcategoria->nombre;
        $this->tipoCalculo = $subcategoria->tipo_calculo;
        $this->activa = (bool) $subcategoria->activa;
        $this->orden = (int) $subcategoria->orden;

        $this->marcasTexto = implode("\n", $marcas);

        $this->rangos = $rangos
            ->map(fn ($rango) => [
                'desde' => (string) $rango->desde,
                'hasta' => $rango->hasta === null
                    ? ''
                    : (string) $rango->hasta,
                'porcentaje' => (string) $rango->porcentaje,
            ])
            ->all();

        if (count($this->rangos) === 0) {
            $this->agregarRango();
        }
    }

    public function guardarSubcategoria(): void
    {
        Gate::authorize('Administrar imagenes productos');

        $this->nombre = trim($this->nombre);
        $this->marcasTexto = trim($this->marcasTexto);

        $this->validate(
            [
                'nombre' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'tipoCalculo' => [
                    'required',
                    'in:subtotal,unidades',
                ],

                'activa' => [
                    'boolean',
                ],

                'orden' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'marcasTexto' => [
                    'required',
                    'string',
                ],

                'rangos' => [
                    'required',
                    'array',
                    'min:1',
                    'max:50',
                ],

                'rangos.*.desde' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'rangos.*.hasta' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'rangos.*.porcentaje' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],
            ],
            [
                'nombre.required' => 'Escriba el nombre de la subcategoría.',
                'tipoCalculo.in' => 'Seleccione un tipo de cálculo válido.',
                'marcasTexto.required' => 'Agregue al menos una marca.',
                'rangos.required' => 'Agregue al menos un rango.',
                'rangos.*.desde.required' => 'El valor inicial es obligatorio.',
                'rangos.*.desde.numeric' => 'El valor inicial debe ser numérico.',
                'rangos.*.hasta.numeric' => 'El valor final debe ser numérico.',
                'rangos.*.porcentaje.required' => 'El porcentaje es obligatorio.',
                'rangos.*.porcentaje.numeric' => 'El porcentaje debe ser numérico.',
                'rangos.*.porcentaje.max' => 'El descuento no puede superar el 100 %.',
            ]
        );

        $marcas = collect(
            preg_split('/\R/u', $this->marcasTexto) ?: []
        )
            ->map(fn ($marca) => trim($marca))
            ->filter(fn ($marca) => $marca !== '')
            ->values();

        if ($marcas->isEmpty()) {
            throw ValidationException::withMessages([
                'marcasTexto' => 'Agregue al menos una marca.',
            ]);
        }

        foreach ($marcas as $marca) {
            if (mb_strlen($marca) > 150) {
                throw ValidationException::withMessages([
                    'marcasTexto' =>
                        'Cada marca puede tener máximo 150 caracteres.',
                ]);
            }
        }

        $marcasNormalizadas = $marcas->map(
            fn ($marca) => mb_strtoupper(trim($marca))
        );

        if ($marcasNormalizadas->unique()->count() !== $marcas->count()) {
            throw ValidationException::withMessages([
                'marcasTexto' =>
                    'Hay marcas repetidas en esta subcategoría.',
            ]);
        }

        $rangos = collect($this->rangos)
            ->map(function ($rango) {
                return [
                    'desde' => (float) $rango['desde'],

                    'hasta' => (
                        $rango['hasta'] === '' ||
                        $rango['hasta'] === null
                    )
                        ? null
                        : (float) $rango['hasta'],

                    'porcentaje' => (float) $rango['porcentaje'],
                ];
            })
            ->sortBy('desde')
            ->values();

        foreach ($rangos as $indice => $rango) {
            if (
                $rango['hasta'] !== null &&
                $rango['hasta'] < $rango['desde']
            ) {
                throw ValidationException::withMessages([
                    'rangos' =>
                        'En un rango, el valor final no puede ser menor que el inicial.',
                ]);
            }

            if ($this->tipoCalculo === 'unidades') {
                if (
                    floor($rango['desde']) !== $rango['desde'] ||
                    (
                        $rango['hasta'] !== null &&
                        floor($rango['hasta']) !== $rango['hasta']
                    )
                ) {
                    throw ValidationException::withMessages([
                        'rangos' =>
                            'Los rangos por unidades deben usar números enteros.',
                    ]);
                }
            }

            if ($indice > 0) {
                $anterior = $rangos[$indice - 1];

                if (
                    $anterior['hasta'] === null ||
                    $rango['desde'] <= $anterior['hasta']
                ) {
                    throw ValidationException::withMessages([
                        'rangos' =>
                            'Los rangos se cruzan. Recuerde que ambos límites son incluyentes.',
                    ]);
                }
            }

            if (
                $rango['hasta'] === null &&
                $indice !== $rangos->count() - 1
            ) {
                throw ValidationException::withMessages([
                    'rangos' =>
                        'El rango sin límite superior debe ser el último.',
                ]);
            }

            foreach (['desde', 'hasta', 'porcentaje'] as $campo) {
                $valor = $rango[$campo];

                if (
                    $valor !== null &&
                    abs($valor * 100 - round($valor * 100)) > 0.000001
                ) {
                    throw ValidationException::withMessages([
                        'rangos' =>
                            'Los valores admiten máximo dos decimales.',
                    ]);
                }
            }
        }

        $nombreExistente = DB::table('listas_precios_subcategorias')
            ->where('nombre', $this->nombre)
            ->when(
                $this->subcategoriaId,
                fn ($query) => $query->where(
                    'id',
                    '!=',
                    $this->subcategoriaId
                )
            )
            ->exists();

        if ($nombreExistente) {
            throw ValidationException::withMessages([
                'nombre' => 'Ya existe una subcategoría con ese nombre.',
            ]);
        }

        $marcaOcupada = DB::table('listas_precios_subcategoria_marcas')
            ->whereIn('marca_excel', $marcas->all())
            ->when(
                $this->subcategoriaId,
                fn ($query) => $query->where(
                    'subcategoria_id',
                    '!=',
                    $this->subcategoriaId
                )
            )
            ->first();

        if ($marcaOcupada) {
            throw ValidationException::withMessages([
                'marcasTexto' =>
                    "La marca {$marcaOcupada->marca_excel} ya está asignada a otra subcategoría.",
            ]);
        }

        DB::transaction(function () use ($marcas, $rangos) {
            $ahora = now();

            if ($this->subcategoriaId !== null) {
                $existe = DB::table('listas_precios_subcategorias')
                    ->where('id', $this->subcategoriaId)
                    ->exists();

                if (!$existe) {
                    throw ValidationException::withMessages([
                        'nombre' => 'La subcategoría ya no existe.',
                    ]);
                }

                DB::table('listas_precios_subcategorias')
                    ->where('id', $this->subcategoriaId)
                    ->update([
                        'nombre' => $this->nombre,
                        'tipo_calculo' => $this->tipoCalculo,
                        'activa' => $this->activa,
                        'orden' => $this->orden,
                        'updated_at' => $ahora,
                    ]);

                $id = $this->subcategoriaId;

                DB::table('listas_precios_subcategoria_marcas')
                    ->where('subcategoria_id', $id)
                    ->delete();

                DB::table('listas_precios_subcategoria_rangos')
                    ->where('subcategoria_id', $id)
                    ->delete();
            } else {
                $id = DB::table('listas_precios_subcategorias')
                    ->insertGetId([
                        'nombre' => $this->nombre,
                        'tipo_calculo' => $this->tipoCalculo,
                        'activa' => $this->activa,
                        'orden' => $this->orden,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
            }

            DB::table('listas_precios_subcategoria_marcas')
                ->insert(
                    $marcas->map(fn ($marca) => [
                        'subcategoria_id' => $id,
                        'marca_excel' => $marca,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ])->all()
                );

            DB::table('listas_precios_subcategoria_rangos')
                ->insert(
                    $rangos->map(fn ($rango) => [
                        'subcategoria_id' => $id,
                        'desde' => $rango['desde'],
                        'hasta' => $rango['hasta'],
                        'porcentaje' => $rango['porcentaje'],
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ])->all()
                );
        });

        $mensaje = $this->subcategoriaId
            ? 'Subcategoría actualizada correctamente.'
            : 'Subcategoría creada correctamente.';

        $this->iniciarFormulario();

        session()->flash('subcategoria_success', $mensaje);
    }

    public function render()
    {
        Gate::authorize('Administrar imagenes productos');

        $exportaciones = DB::table('listas_precios_exportaciones')
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $subcategorias = DB::table('listas_precios_subcategorias')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $ids = $subcategorias->pluck('id');

        $marcasPorSubcategoria = DB::table('listas_precios_subcategoria_marcas')
            ->whereIn('subcategoria_id', $ids)
            ->orderBy('marca_excel')
            ->get()
            ->groupBy('subcategoria_id');

        $rangosPorSubcategoria = DB::table('listas_precios_subcategoria_rangos')
            ->whereIn('subcategoria_id', $ids)
            ->orderBy('desde')
            ->get()
            ->groupBy('subcategoria_id');

        return view(
            'livewire.admin.listas-precios.index',
            compact(
                'exportaciones',
                'subcategorias',
                'marcasPorSubcategoria',
                'rangosPorSubcategoria'
            )
        );
    }
}