<?php

namespace App\Livewire\Admin\Rutero;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Planes extends Component
{
    use WithPagination;

    public const ROLES_GLOBALES = [
        'Administrador General',
        'Director Comercial',
    ];

    public const ROL_LIDER = 'Líder comercial';

    public string $mes = '';

    public ?int $planSeleccionadoId = null;

    public function mount(): void
    {
        $this->mes = now('America/Bogota')->format('Y-m');
    }

    public function updatedMes(): void
    {
        $this->planSeleccionadoId = null;
        $this->resetPage();
    }

    public function abrirPlan(int $planId): void
    {
        $plan = $this->planesDelMes()
            ->where('p.id', $planId)
            ->first();

        abort_if($plan === null, 404);

        $this->planSeleccionadoId = (int) $plan->id;
    }

    public function cerrarPlan(): void
    {
        $this->planSeleccionadoId = null;
    }

    public function render(): View
    {
        $consulta = $this->planesDelMes();

        $plan = $this->planSeleccionadoId === null
            ? null
            : (clone $consulta)
                ->where('p.id', $this->planSeleccionadoId)
                ->first();

        $dias = collect();

        if ($plan) {
            $dias = DB::table('rutero_dias as d')
                ->join('rutero_dia_ciudades as c', 'c.dia_id', '=', 'd.id')
                ->leftJoin('rutero_paradas as v', 'v.dia_ciudad_id', '=', 'c.id')
                ->where('d.plan_id', $plan->id)
                ->orderBy('d.fecha')
                ->orderBy('c.ciudad')
                ->orderBy('c.departamento')
                ->orderBy('v.razon_social')
                ->get([
                    'd.fecha',
                    'c.id as ciudad_id',
                    'c.ciudad',
                    'c.departamento',
                    'v.id as parada_id',
                    'v.nit',
                    'v.razon_social',
                    'v.id_sucursal',
                    'v.punto_envio_id',
                    'v.direccion',
                ])
                ->groupBy('fecha')
                ->map(fn ($filas) => $filas->groupBy('ciudad_id'));
        }

        $planes = $consulta
            ->selectSub(
                DB::table('rutero_dias as d')
                    ->join('rutero_dia_ciudades as c', 'c.dia_id', '=', 'd.id')
                    ->whereColumn('d.plan_id', 'p.id')
                    ->selectRaw('COUNT(DISTINCT d.id)'),
                'dias_programados'
            )
            ->selectSub(
                DB::table('rutero_dias as d')
                    ->join('rutero_dia_ciudades as c', 'c.dia_id', '=', 'd.id')
                    ->join('rutero_paradas as v', 'v.dia_ciudad_id', '=', 'c.id')
                    ->whereColumn('d.plan_id', 'p.id')
                    ->selectRaw('COUNT(*)'),
                'visitas_programadas'
            )
            ->orderBy('u.name')
            ->orderBy('p.id')
            ->paginate(25);

        return view('livewire.admin.rutero.planes', compact('planes', 'plan', 'dias'));
    }

    private function planesDelMes(): Builder
    {
        $usuario = auth()->user();

        abort_if($usuario === null, 403);

        $accesoGlobal = $usuario->hasAnyRole(self::ROLES_GLOBALES);

        abort_unless($accesoGlobal || $usuario->hasRole(self::ROL_LIDER), 403);

        $periodo = preg_match('/\A[1-9][0-9]{3}-(0[1-9]|1[0-2])\z/', $this->mes)
            ? $this->mes . '-01'
            : null;

        $consulta = DB::table('rutero_planes as p')
            ->join('users as u', 'u.id', '=', 'p.asesor_id')
            ->where('p.periodo', $periodo)
            ->select([
                'p.id',
                'p.periodo',
                'p.estado',
                'u.name as asesor',
                'u.codigo_asesor',
            ]);

        if (! $accesoGlobal) {
            $consulta->whereExists(function (Builder $asignacion) use ($usuario): void {
                $asignacion->select('equipo.asesor_id')
                    ->from('rutero_lider_asesor as equipo')
                    ->whereColumn('equipo.asesor_id', 'p.asesor_id')
                    ->where('equipo.lider_id', $usuario->getKey());
            });
        }

        return $consulta;
    }
}