<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            // Si el mes ya existe, conserva su estado y programación.
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

            $dias = DB::table('rutero_dias')->where('plan_id', $plan->id);

            if (! $dias->exists()) {
                $filas = [];

                for ($numero = 1; $numero <= $inicio->daysInMonth; $numero++) {
                    $fecha = $inicio->day($numero);
                    $ultimoSabado = $fecha->isSaturday()
                        && $numero + 7 > $inicio->daysInMonth;

                    $filas[] = [
                        'plan_id' => $plan->id,
                        'fecha' => $fecha->toDateString(),
                        'dia_semana' => $fecha->dayOfWeekIso,
                        'habilitado' => $fecha->dayOfWeekIso <= 5 || $ultimoSabado,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }

                DB::table('rutero_dias')->insert($filas);
            }

            return $this->respuestaMes($mes, $plan);
        }, 3);
    }

    private function respuestaMes(string $mes, ?object $plan): JsonResponse
    {
        $dias = $plan
            ? DB::table('rutero_dias')
                ->where('plan_id', $plan->id)
                ->orderBy('fecha')
                ->get(['fecha', 'dia_semana', 'habilitado'])
                ->map(fn (object $dia) => [
                    'fecha' => $dia->fecha,
                    'dia_semana' => (int) $dia->dia_semana,
                    'habilitado' => (bool) $dia->habilitado,
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
}