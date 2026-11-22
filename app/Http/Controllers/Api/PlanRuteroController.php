<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Symfony\Component\HttpFoundation\Response;

class PlanRuteroController extends Controller
{
    public function consultar(Request $request, string $mes): JsonResponse
    {
        $asesor = $request->user();

        abort_unless($asesor->hasRole('Asesor'), 403, 'Se requiere el rol Asesor.');

        $plan = DB::table('rutero_planes')
            ->where('asesor_id', $asesor->getKey())
            ->where('periodo', $mes . '-01')
            ->first();

        return $this->respuestaMes($mes, $plan);
    }

    public function crear(Request $request, string $mes): JsonResponse
    {
        $asesor = $request->user();

        abort_unless($asesor->hasRole('Asesor'), 403, 'Se requiere el rol Asesor.');

        $inicio = CarbonImmutable::createFromFormat('!Y-m', $mes, 'America/Bogota');
        $asesorId = $asesor->getKey();
        $periodo = $inicio->toDateString();

        return DB::transaction(function () use ($asesorId, $mes, $inicio, $periodo) {
            $ahora = now();

            DB::table('rutero_planes')->upsert([
                [
                    'asesor_id' => $asesorId,
                    'periodo' => $periodo,
                    'estado' => 'borrador',
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ],
            ], ['asesor_id', 'periodo'], ['periodo']);

            $plan = DB::table('rutero_planes')
                ->where('asesor_id', $asesorId)
                ->where('periodo', $periodo)
                ->lockForUpdate()
                ->first();

            if (! DB::table('rutero_dias')->where('plan_id', $plan->id)->exists()) {
                $dias = [];

                for ($numero = 1; $numero <= $inicio->daysInMonth; $numero++) {
                    $fecha = $inicio->day($numero);
                    $ultimoSabado = $fecha->isSaturday()
                        && $numero + 7 > $inicio->daysInMonth;

                    $dias[] = [
                        'plan_id' => $plan->id,
                        'fecha' => $fecha->toDateString(),
                        'dia_semana' => $fecha->dayOfWeekIso,
                        'habilitado' => $fecha->dayOfWeekIso <= 5 || $ultimoSabado,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }

                DB::table('rutero_dias')->insert($dias);
            }

            return $this->respuestaMes($mes, $plan);
        }, 3);
    }

    public function consultarDia(Request $request, string $fecha): JsonResponse
    {
        $asesor = $request->user();

        abort_unless($asesor->hasRole('Asesor'), 403, 'Se requiere el rol Asesor.');

        $this->validarFecha($fecha);

        $dia = $this->consultaDia($asesor->getKey(), $fecha)->first();

        abort_if($dia === null, 404, 'Primero debe crear el mes en su rutero.');

        return $this->respuestaDia($dia, $fecha);
    }

public function guardarDia(Request $request, string $fecha): JsonResponse
{
    $asesor = $request->user();

    abort_unless($asesor->hasRole('Asesor'), 403, 'Se requiere el rol Asesor.');

    $this->validarFecha($fecha);

    $datos = $request->validate([
        'ciudades' => ['present', 'array', 'list'],
        'ciudades.*' => [
            'required',
            'array:codigo_departamento,codigo_ciudad,paradas',
        ],
        'ciudades.*.codigo_departamento' => ['required', 'string', 'max:20'],
        'ciudades.*.codigo_ciudad' => ['required', 'string', 'max:20'],
        'ciudades.*.paradas' => ['present', 'array', 'list'],
        'ciudades.*.paradas.*' => [
            'required',
            'array:nit,id_sucursal,punto_envio_id',
        ],
        'ciudades.*.paradas.*.nit' => ['required', 'string', 'max:32'],
        'ciudades.*.paradas.*.id_sucursal' => ['required', 'string', 'max:32'],
        'ciudades.*.paradas.*.punto_envio_id' => ['required', 'string', 'max:32'],
    ]);

    $asesorId = $asesor->getKey();
    $periodo = substr($fecha, 0, 7) . '-01';

    $dia = $this->consultaDia($asesorId, $fecha)->first();

    abort_if($dia === null, 404, 'Primero debe crear el mes en su rutero.');

    $this->validarDiaEditable($dia);

    $ciudades = [];

    if ($datos['ciudades'] !== []) {
        $codigoAsesor = trim((string) $asesor->codigo_asesor);

        abort_if(
            $codigoAsesor === '',
            422,
            'El usuario no tiene código de asesor.'
        );

        $ciudades = $this->resolverProgramacion(
            $datos['ciudades'],
            $codigoAsesor
        );
    }

    return DB::transaction(function () use ($asesorId, $periodo, $fecha, $ciudades) {
        // Tomar el mismo bloqueo que usa enviarRevision().
        $plan = DB::table('rutero_planes')
            ->where('asesor_id', $asesorId)
            ->where('periodo', $periodo)
            ->lockForUpdate()
            ->first();

        abort_if($plan === null, 404, 'Primero debe crear el mes en su rutero.');

        $dia = $this->consultaDia($asesorId, $fecha)
            ->lockForUpdate()
            ->first();

        abort_if($dia === null, 404, 'La fecha no existe en su rutero.');

        $this->validarDiaEditable($dia);

        $this->guardarCiudades((int) $dia->dia_id, $ciudades);

        return $this->respuestaDia($dia, $fecha);
    }, 3);
}

    private function respuestaMes(string $mes, ?object $plan): JsonResponse
    {
        $dias = $plan
            ? DB::table('rutero_dias as d')
            ->where('d.plan_id', $plan->id)
            ->select(['d.fecha', 'd.dia_semana', 'd.habilitado'])
            ->selectSub(
                DB::table('rutero_dia_ciudades as c')
                    ->whereColumn('c.dia_id', 'd.id')
                    ->selectRaw('COUNT(*)'),
                'ciudades_programadas'
            )
            ->orderBy('d.fecha')
            ->get()
            ->map(fn(object $dia) => [
                'fecha' => $dia->fecha,
                'dia_semana' => (int) $dia->dia_semana,
                'habilitado' => (bool) $dia->habilitado,
                'tiene_ruta' => (int) $dia->ciudades_programadas > 0,
            ])
            ->all()
            : [];

        return response()->json([
            'mes' => $mes,
            'existe' => $plan !== null,
            'plan_id' => $plan ? (int) $plan->id : null,
            'estado' => $plan?->estado,
            'dias' => $dias,
        ]);
    }
    private function validarFecha(string $fecha): void
    {
        Validator::make(
            ['fecha' => $fecha],
            ['fecha' => ['required', 'date_format:Y-m-d']]
        )->validate();
    }

    private function consultaDia(int $asesorId, string $fecha): Builder
    {
        return DB::table('rutero_dias as dias')
            ->join('rutero_planes as planes', 'planes.id', '=', 'dias.plan_id')
            ->where('planes.asesor_id', $asesorId)
            ->where('planes.periodo', substr($fecha, 0, 7) . '-01')
            ->where('dias.fecha', $fecha)
            ->select([
                'dias.id as dia_id',
                'dias.plan_id',
                'dias.habilitado',
                'planes.estado',
            ]);
    }

    private function validarDiaEditable(object $dia): void
    {
        abort_unless(
            (bool) $dia->habilitado,
            422,
            'Esta fecha está bloqueada para programar.'
        );

        abort_unless(
            in_array($dia->estado, ['borrador', 'devuelto'], true),
            409,
            'Este plan no se puede editar en su estado actual. Actualice el calendario.'
        );
    }

    private function puntosDelAsesor(string $codigoAsesor): Collection
    {
        return DB::connection('sqlsrv')
            ->table('t200_mm_terceros as t200')
            ->join('t201_mm_clientes as t201', function ($join): void {
                $join->on('t201.f201_rowid_tercero', '=', 't200.f200_rowid')
                    ->on('t201.f201_id_cia', '=', 't200.f200_id_cia');
            })
            ->join('t215_mm_puntos_envio_cliente as t215', function ($join): void {
                $join->on('t215.f215_rowid_tercero', '=', 't201.f201_rowid_tercero')
                    ->on('t215.f215_id_sucursal', '=', 't201.f201_id_sucursal')
                    ->on('t215.f215_id_cia', '=', 't201.f201_id_cia');
            })
            ->join(
                't015_mm_contactos as t015',
                't015.f015_rowid',
                '=',
                't215.f215_rowid_contacto'
            )
            ->join('t012_mm_deptos as t012', function ($join): void {
                $join->on('t012.f012_id', '=', 't015.f015_id_depto')
                    ->on('t012.f012_id_pais', '=', 't015.f015_id_pais');
            })
            ->join('t013_mm_ciudades as t013', function ($join): void {
                $join->on('t013.f013_id', '=', 't015.f015_id_ciudad')
                    ->on('t013.f013_id_depto', '=', 't015.f015_id_depto')
                    ->on('t013.f013_id_pais', '=', 't015.f015_id_pais');
            })
            ->where('t200.f200_id_cia', 3)
            ->where('t200.f200_ind_cliente', 1)
            ->where('t200.f200_ind_estado', 1)
            ->where('t201.f201_ind_estado_activo', 1)
            ->where('t201.f201_id_sucursal', '<>', '001')
            ->where('t201.f201_id_vendedor', $codigoAsesor)
            ->where('t215.f215_ind_estado', 1)
            ->where('t015.f015_id_pais', 169)
            ->get([
                't200.f200_nit as nit',
                't200.f200_razon_social as razon_social',
                't201.f201_id_sucursal as id_sucursal',
                't215.f215_id as punto_envio_id',
                't015.f015_id_depto as codigo_departamento',
                't015.f015_id_ciudad as codigo_ciudad',
                't012.f012_descripcion as departamento',
                't013.f013_descripcion as ciudad',
                't015.f015_direccion1 as direccion',
            ]);
    }

    private function resolverProgramacion(array $seleccion, string $codigoAsesor): array
    {
        $catalogo = [];

        foreach ($this->puntosDelAsesor($codigoAsesor) as $fila) {
            $codigoDepartamento = trim((string) $fila->codigo_departamento);
            $codigoCiudad = trim((string) $fila->codigo_ciudad);
            $nit = trim((string) $fila->nit);
            $sucursal = trim((string) $fila->id_sucursal);
            $puntoEnvio = trim((string) $fila->punto_envio_id);
            $direccion = trim((string) $fila->direccion);

            if ($codigoDepartamento === '' || $codigoCiudad === '') {
                continue;
            }

            $claveCiudad = $this->clave($codigoDepartamento, $codigoCiudad);

            $catalogo[$claveCiudad] ??= [
                'ciudad' => [
                    'codigo_departamento' => $codigoDepartamento,
                    'codigo_ciudad' => $codigoCiudad,
                    'departamento' => trim((string) $fila->departamento),
                    'ciudad' => trim((string) $fila->ciudad),
                ],
                'paradas' => [],
            ];

            $claveParada = $this->clave($nit, $sucursal, $puntoEnvio);

            $catalogo[$claveCiudad]['paradas'][$claveParada] = [
                'nit' => $nit,
                'id_sucursal' => $sucursal,
                'punto_envio_id' => $puntoEnvio,
                'razon_social' => trim((string) $fila->razon_social),
                'direccion' => $direccion === '' ? null : $direccion,
            ];
        }

        $resultado = [];

        foreach ($seleccion as $indice => $ciudad) {
            $claveCiudad = $this->clave(
                $ciudad['codigo_departamento'],
                $ciudad['codigo_ciudad']
            );

            if (! isset($catalogo[$claveCiudad]) || isset($resultado[$claveCiudad])) {
                throw ValidationException::withMessages([
                    "ciudades.$indice" => 'La ciudad está repetida o no pertenece a su cartera.',
                ]);
            }

            $resultado[$claveCiudad] = [
                'ciudad' => $catalogo[$claveCiudad]['ciudad'],
                'paradas' => [],
            ];

            foreach ($ciudad['paradas'] as $posicion => $parada) {
                $claveParada = $this->clave(
                    $parada['nit'],
                    $parada['id_sucursal'],
                    $parada['punto_envio_id']
                );

                $punto = $catalogo[$claveCiudad]['paradas'][$claveParada] ?? null;

                if ($punto === null || isset($resultado[$claveCiudad]['paradas'][$claveParada])) {
                    throw ValidationException::withMessages([
                        "ciudades.$indice.paradas.$posicion" =>
                        'El punto de envío está repetido o no corresponde a esta ciudad y asesor.',
                    ]);
                }

                $resultado[$claveCiudad]['paradas'][$claveParada] = $punto;
            }
        }

        return $resultado;
    }

    private function guardarCiudades(int $diaId, array $ciudades): void
    {
        $ahora = now();
        $idsCiudades = [];

        foreach ($ciudades as $grupo) {
            $datosCiudad = $grupo['ciudad'];
            $consultaCiudad = DB::table('rutero_dia_ciudades')->where('dia_id', $diaId);

            $ciudadId = (clone $consultaCiudad)
                ->where('codigo_departamento', $datosCiudad['codigo_departamento'])
                ->where('codigo_ciudad', $datosCiudad['codigo_ciudad'])
                ->value('id');

            // Reutilizar una ciudad guardada antes de incorporar los códigos.
            $ciudadId ??= (clone $consultaCiudad)
                ->whereNull('codigo_departamento')
                ->whereNull('codigo_ciudad')
                ->where('departamento', $datosCiudad['departamento'])
                ->where('ciudad', $datosCiudad['ciudad'])
                ->value('id');

            if ($ciudadId === null) {
                $ciudadId = DB::table('rutero_dia_ciudades')->insertGetId([
                    ...$datosCiudad,
                    'dia_id' => $diaId,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            } else {
                (clone $consultaCiudad)->where('id', $ciudadId)->update([
                    ...$datosCiudad,
                    'updated_at' => $ahora,
                ]);
            }

            $idsCiudades[] = $ciudadId;

            $paradas = DB::table('rutero_paradas')->where('dia_ciudad_id', $ciudadId);

            $idsRetirar = (clone $paradas)
                ->get(['id', 'nit', 'id_sucursal', 'punto_envio_id'])
                ->reject(fn(object $fila) => isset($grupo['paradas'][$this->clave($fila->nit, $fila->id_sucursal, $fila->punto_envio_id)]))
                ->pluck('id');

            foreach ($idsRetirar->chunk(100) as $ids) {
                (clone $paradas)->whereIn('id', $ids)->delete();
            }

            foreach (array_chunk($grupo['paradas'], 100) as $lote) {
                $filas = array_map(fn(array $parada) => [
                    ...$parada,
                    'dia_ciudad_id' => $ciudadId,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ], $lote);

                DB::table('rutero_paradas')->upsert(
                    $filas,
                    ['dia_ciudad_id', 'nit', 'id_sucursal', 'punto_envio_id'],
                    ['razon_social', 'direccion', 'updated_at']
                );
            }
        }

        DB::table('rutero_dia_ciudades')
            ->where('dia_id', $diaId)
            ->whereNotIn('id', $idsCiudades)
            ->delete();
    }

    private function respuestaDia(object $dia, string $fecha): JsonResponse
    {
        $filas = DB::table('rutero_dia_ciudades as c')
            ->leftJoin('rutero_paradas as p', 'p.dia_ciudad_id', '=', 'c.id')
            ->where('c.dia_id', $dia->dia_id)
            ->orderBy('c.ciudad')
            ->orderBy('p.razon_social')
            ->get([
                'c.id as ciudad_id',
                'c.codigo_departamento',
                'c.codigo_ciudad',
                'c.departamento',
                'c.ciudad',
                'p.id as parada_id',
                'p.nit',
                'p.razon_social',
                'p.id_sucursal',
                'p.punto_envio_id',
                'p.direccion',
            ]);

        $ciudades = $filas->groupBy('ciudad_id')->map(function (Collection $grupo): array {
            $ciudad = $grupo->first();

            return [
                'id' => (int) $ciudad->ciudad_id,
                'codigo_departamento' => $ciudad->codigo_departamento,
                'codigo_ciudad' => $ciudad->codigo_ciudad,
                'departamento' => $ciudad->departamento,
                'ciudad' => $ciudad->ciudad,
                'paradas' => $grupo
                    ->whereNotNull('parada_id')
                    ->map(fn(object $fila) => [
                        'id' => (int) $fila->parada_id,
                        'nit' => $fila->nit,
                        'razon_social' => $fila->razon_social,
                        'id_sucursal' => $fila->id_sucursal,
                        'punto_envio_id' => $fila->punto_envio_id,
                        'direccion' => $fila->direccion,
                    ])
                    ->values()
                    ->all(),
            ];
        })->values()->all();

        return response()->json([
            'fecha' => $fecha,
            'dia_id' => (int) $dia->dia_id,
            'plan_id' => (int) $dia->plan_id,
            'estado' => $dia->estado,
            'habilitado' => (bool) $dia->habilitado,
            'editable' => (bool) $dia->habilitado
                && in_array($dia->estado, ['borrador', 'devuelto'], true),
            'ciudades' => $ciudades,
        ]);
    }

    private function clave(string|int ...$partes): string
    {
        return json_encode(
            array_map(fn(string|int $parte) => trim((string) $parte), $partes),
            JSON_THROW_ON_ERROR
        );
    }


    private const CAMPOS_GASTOS = [
        'hospedaje',
        'alimentacion',
        'transporte_intermunicipal',
        'transporte_urbano_combustible',
    ];

    public function consultarGastos(Request $request, string $mes): JsonResponse
    {
        $plan = $this->planDelAsesor($request, $mes)->first();

        abort_if($plan === null, 404, 'Primero debe crear el mes en su rutero.');

        return response()->json([
            'editable' => $this->planEditable($plan->estado),
            'dias' => $this->gastosDelPlan((int) $plan->id),
        ]);
    }

    public function guardarGastosSemana(
        Request $request,
        string $mes,
        string $lunes
    ): JsonResponse {
        $planes = $this->planDelAsesor($request, $mes);

        $this->validarFecha($lunes);

        $inicio = CarbonImmutable::parse($lunes, 'America/Bogota');

        abort_unless($inicio->isMonday(), 422, 'Seleccione el lunes de la semana.');

        $reglas = [
            'dias' => ['required', 'array', 'list', 'max:7'],
            'dias.*' => ['required', 'array:fecha,' . implode(',', self::CAMPOS_GASTOS)],
            'dias.*.fecha' => ['required', 'date_format:Y-m-d', 'distinct'],
        ];

        foreach (self::CAMPOS_GASTOS as $campo) {
            $reglas["dias.*.$campo"] = ['required', 'integer', 'min:0', 'max:2147483647'];
        }

        $datos = $request->validate($reglas);

        return DB::transaction(function () use ($planes, $inicio, $datos) {
            $plan = $planes->lockForUpdate()->first();

            abort_if($plan === null, 404, 'Primero debe crear el mes en su rutero.');
            abort_unless(
                $this->planEditable($plan->estado),
                409,
                'El plan está en revisión o publicado. Los gastos están bloqueados.'
            );

            $fechasPermitidas = DB::table('rutero_dias')
                ->where('plan_id', $plan->id)
                ->where('habilitado', true)
                ->whereBetween('fecha', [
                    $inicio->toDateString(),
                    $inicio->addDays(6)->toDateString(),
                ])
                ->pluck('id', 'fecha');

            $ahora = now();
            $filas = [];

            foreach ($datos['dias'] as $indice => $dia) {
                $diaId = $fechasPermitidas->get($dia['fecha']);

                if ($diaId === null) {
                    throw ValidationException::withMessages([
                        "dias.$indice.fecha" => 'La fecha no está habilitada en esta semana del plan.',
                    ]);
                }

                $fila = [
                    'dia_id' => $diaId,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                foreach (self::CAMPOS_GASTOS as $campo) {
                    $fila[$campo] = (int) $dia[$campo];
                }

                $filas[] = $fila;
            }

            DB::table('rutero_gastos')->upsert(
                $filas,
                ['dia_id'],
                [...self::CAMPOS_GASTOS, 'updated_at']
            );

            return response()->json([
                'editable' => true,
                'dias' => $this->gastosDelPlan((int) $plan->id),
            ]);
        }, 3);
    }

    public function enviarRevision(Request $request, string $mes): JsonResponse
    {
        $planes = $this->planDelAsesor($request, $mes);

        return DB::transaction(function () use ($planes) {
            $plan = $planes->lockForUpdate()->first();

            abort_if($plan === null, 404, 'El plan mensual no existe.');

            if ($plan->estado === 'en_revision') {
                return response()->json(['estado' => 'en_revision']);
            }

            abort_unless(
                $this->planEditable($plan->estado),
                409,
                'El plan no se puede enviar en su estado actual.'
            );

            $ahora = now();

            DB::table('rutero_planes')->where('id', $plan->id)->update([
                'estado' => 'en_revision',
                'enviado_revision_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            return response()->json(['estado' => 'en_revision']);
        }, 3);
    }

    public function descargarPdf(Request $request, string $mes): Response
    {
        $planes = $this->planDelAsesor($request, $mes);

        $datos = DB::transaction(function () use ($planes, $request, $mes) {
            $plan = $planes->lockForUpdate()->first();

            abort_if($plan === null, 404, 'El plan mensual no existe.');

            $filas = DB::table('rutero_dias as d')
                ->join('rutero_dia_ciudades as c', 'c.dia_id', '=', 'd.id')
                ->leftJoin('rutero_paradas as p', 'p.dia_ciudad_id', '=', 'c.id')
                ->where('d.plan_id', $plan->id)
                ->orderBy('d.fecha')
                ->orderBy('c.ciudad')
                ->orderBy('p.razon_social')
                ->get([
                    'd.fecha',
                    'c.id as ciudad_id',
                    'c.ciudad',
                    'c.departamento',
                    'p.id as parada_id',
                    'p.razon_social',
                    'p.nit',
                    'p.id_sucursal',
                    'p.punto_envio_id',
                    'p.direccion',
                ]);

            $gastos = $this->gastosDelPlan((int) $plan->id)
                ->filter(fn(array $dia) => $dia['total'] > 0);

            return [
                'mes' => $mes,
                'plan' => $plan,
                'asesor' => $request->user(),
                'ruta' => $filas->groupBy('fecha'),
                'gastosPorSemana' => $gastos->groupBy(
                    fn(array $dia) =>
                    CarbonImmutable::parse($dia['fecha'])
                        ->startOfWeek(CarbonInterface::MONDAY)
                        ->toDateString()
                ),
                'totalMes' => $gastos->sum('total'),
            ];
        }, 3);

        return Pdf::loadView('pdf.rutero-plan', $datos)
            ->setPaper('a4')
            ->download("rutero-{$mes}.pdf");
    }

    private function planDelAsesor(Request $request, string $mes): Builder
    {
        $asesor = $request->user();

        abort_unless($asesor->hasRole('Asesor'), 403, 'Se requiere el rol Asesor.');

        return DB::table('rutero_planes')
            ->where('asesor_id', $asesor->getKey())
            ->where('periodo', $mes . '-01');
    }

    private function planEditable(string $estado): bool
    {
        return in_array($estado, ['borrador', 'devuelto'], true);
    }

    private function gastosDelPlan(int $planId): Collection
    {
        return DB::table('rutero_dias as d')
            ->leftJoin('rutero_gastos as g', 'g.dia_id', '=', 'd.id')
            ->where('d.plan_id', $planId)
            ->where('d.habilitado', true)
            ->orderBy('d.fecha')
            ->get([
                'd.fecha',
                'g.hospedaje',
                'g.alimentacion',
                'g.transporte_intermunicipal',
                'g.transporte_urbano_combustible',
            ])
            ->map(function (object $fila): array {
                $dia = ['fecha' => (string) $fila->fecha, 'total' => 0];

                foreach (self::CAMPOS_GASTOS as $campo) {
                    $dia[$campo] = (int) ($fila->{$campo} ?? 0);
                    $dia['total'] += $dia[$campo];
                }

                return $dia;
            });
    }
}
