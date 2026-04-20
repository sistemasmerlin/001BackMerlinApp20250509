<?php

namespace App\Livewire\Admin\Comisiones;

use App\Models\ComisionAsesor;
use App\Models\PresupuestoComercial;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $asesores = [];
    public $asesorSeleccionado = '';
    public $periodoGenerar = '';
    public $periodoConsulta = '';
    public $comisiones = [];

    public function mount()
    {
        $periodoAnterior = Carbon::now()->startOfMonth()->subMonth()->format('Ym');

        $this->periodoGenerar = $periodoAnterior;
        $this->periodoConsulta = $periodoAnterior;

        $this->cargarAsesores();
        $this->cargarComisiones();
    }

    public function cargarAsesores()
    {
        $this->asesores = User::query()
            ->whereHas('roles', function ($q) {
                $q->whereRaw('LOWER(name) = ?', ['asesor']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'codigo_asesor'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'codigo_asesor' => $user->codigo_asesor,
                ];
            })
            ->toArray();
    }

public function generarComisionesVenta()
{
    return redirect()->route('admin.comisiones.index', [
        'asesor' => $this->asesorSeleccionado,
        'periodo' => $this->periodoGenerar,
    ]);
}

/*
    public function generarComisionesVenta()
    {
        $this->validate([
            'asesorSeleccionado' => 'required',
            'periodoGenerar' => ['required', 'regex:/^\d{6}$/'],
        ], [
            'asesorSeleccionado.required' => 'Debes seleccionar un asesor.',
            'periodoGenerar.required' => 'Debes ingresar un período.',
            'periodoGenerar.regex' => 'El período debe estar en formato YYYYMM.',
        ]);

        $user = User::where('codigo_asesor', $this->asesorSeleccionado)->first();

        if (!$user) {
            session()->flash('error', 'No se encontró el asesor seleccionado.');
            return;
        }

        // Traer presupuestos del asesor y periodo
        $presupuestos = PresupuestoComercial::query()
            ->where('codigo_asesor', $this->asesorSeleccionado)
            ->where('periodo', $this->periodoGenerar)
            ->get();

        // Agrupar por categorías base
        $llantas = (float) $presupuestos->where('categoria', 'llantas')->sum('presupuesto');
        $repuestos = (float) $presupuestos->where('categoria', 'repuestos')->sum('presupuesto');
        $pirelli = (float) $presupuestos->where('categoria', 'pirelli')->sum('presupuesto');

        // Si ya existe categoría total, la usamos; si no, sumamos las demás
        $totalDesdeTabla = (float) $presupuestos->where('categoria', 'total')->sum('presupuesto');
        $total = $totalDesdeTabla > 0 ? $totalDesdeTabla : ($llantas + $repuestos + $pirelli);

        $registros = [
            'llantas_ppto' => $llantas,
            'llantas_ventas' => 0,
            'llantas_cumplimiento' => 0,
            'llantas_comision' => 0,

            'repuestos_ppto' => $repuestos,
            'repuestos_ventas' => 0,
            'repuestos_cumplimiento' => 0,
            'repuestos_comision' => 0,

            'pirelli_ppto' => $pirelli,
            'pirelli_ventas' => 0,
            'pirelli_cumplimiento' => 0,
            'pirelli_comision' => 0,

            'total_ppto' => $total,
            'total_ventas' => 0,
            'total_cumplimiento' => 0,
            'total_comision' => 0,
        ];

        foreach ($registros as $tipo => $valor) {
            ComisionAsesor::withTrashed()->updateOrCreate(
                [
                    'periodo' => $this->periodoGenerar,
                    'cod_asesor' => $this->asesorSeleccionado,
                    'tipo' => $tipo,
                ],
                [
                    'user_id' => $user->id,
                    'valor' => $valor,
                    'updated_by' => Auth::id(),
                    'deleted_at' => null,
                ]
            );
        }

        $this->periodoConsulta = $this->periodoGenerar;
        $this->cargarComisiones();

        session()->flash('success', 'Presupuestos base de comisiones generados correctamente.');
    }
*/
    public function updatedPeriodoConsulta()
    {
        $this->cargarComisiones();
    }

    public function updatedAsesorSeleccionado()
    {
        $this->cargarComisiones();
    }

    public function cargarComisiones()
    {
        $query = ComisionAsesor::query()
            ->with('user')
            ->where('periodo', $this->periodoConsulta);

        if (!empty($this->asesorSeleccionado)) {
            $query->where('cod_asesor', $this->asesorSeleccionado);
        }

        $registros = $query
            ->orderBy('cod_asesor')
            ->orderBy('tipo')
            ->get();

        $this->comisiones = $registros
            ->groupBy('cod_asesor')
            ->map(function ($items, $codAsesor) {
                $primero = $items->first();
                $tipos = $items->pluck('valor', 'tipo');

                return [
                    'cod_asesor' => $codAsesor,
                    'nombre_asesor' => optional($primero->user)->name ?? 'Sin nombre',

                    'llantas_ppto' => (float) ($tipos['llantas_ppto'] ?? 0),
                    'llantas_ventas' => (float) ($tipos['llantas_ventas'] ?? 0),
                    'llantas_cumplimiento' => (float) ($tipos['llantas_cumplimiento'] ?? 0),
                    'llantas_comision' => (float) ($tipos['llantas_comision'] ?? 0),

                    'pirelli_ppto' => (float) ($tipos['pirelli_ppto'] ?? 0),
                    'pirelli_ventas' => (float) ($tipos['pirelli_ventas'] ?? 0),
                    'pirelli_cumplimiento' => (float) ($tipos['pirelli_cumplimiento'] ?? 0),
                    'pirelli_comision' => (float) ($tipos['pirelli_comision'] ?? 0),

                    'repuestos_ppto' => (float) ($tipos['repuestos_ppto'] ?? 0),
                    'repuestos_ventas' => (float) ($tipos['repuestos_ventas'] ?? 0),
                    'repuestos_cumplimiento' => (float) ($tipos['repuestos_cumplimiento'] ?? 0),
                    'repuestos_comision' => (float) ($tipos['repuestos_comision'] ?? 0),

                    'total_ppto' => (float) ($tipos['total_ppto'] ?? 0),
                    'total_ventas' => (float) ($tipos['total_ventas'] ?? 0),
                    'total_cumplimiento' => (float) ($tipos['total_cumplimiento'] ?? 0),
                    'total_comision' => (float) ($tipos['total_comision'] ?? 0),
                ];
            })
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.comisiones.index')
            ->layout('components.layouts.app', ['title' => 'Comisiones']);
    }
}