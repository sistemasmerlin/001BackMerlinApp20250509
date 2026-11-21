<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class CalendarioRuteroController extends Controller
{
    public function __invoke(Request $request, string $mes): JsonResponse
    {
        //abort_unless($request->user()->hasRole('Asesor'), 403);

        $inicio = CarbonImmutable::createFromFormat(
            '!Y-m',
            $mes,
            'America/Bogota'
        );

        $dias = [];

        for ($numero = 1; $numero <= $inicio->daysInMonth; $numero++) {
            $fecha = $inicio->day($numero);

            $esUltimoSabado = $fecha->isSaturday()
                && $numero + 7 > $inicio->daysInMonth;

            $dias[] = [
                'fecha' => $fecha->toDateString(),
                'dia_semana' => $fecha->dayOfWeekIso,
                'habilitado' => $fecha->dayOfWeekIso <= 5 || $esUltimoSabado,
            ];
        }

        return response()->json([
            'mes' => $mes,
            'dias' => $dias,
        ]);
    }
}
