<?php

namespace App\Livewire\Admin\Comisiones;

use Livewire\Component;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ComisionesController;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;

class Index extends Component
{
    public string $periodoGenerar = '';
    public array $resultadoGenerado = [];
    public string $tipoResultado = ''; // ventas | cartera

    public function mount(): void
    {
        $this->periodoGenerar = now()->format('Ym');
        $this->resultadoGenerado = [];
        $this->tipoResultado = '';
    }

    public function generarComisionesVenta(): void
    {
        $this->validatePeriodo();

        try {
            $this->limpiarResultado();

            $request = new Request([
                'periodo' => $this->periodoGenerar,
            ]);

            $controller = app(ComisionesController::class);
            $resultado = $controller->indexVentas($request);

            $this->resultadoGenerado = is_array($resultado) ? $resultado : [];
            $this->tipoResultado = 'ventas';

            session()->flash('success', 'Comisiones de ventas generadas correctamente.');
        } catch (\Throwable $e) {
            $this->limpiarResultado();
            session()->flash('error', 'Ocurrió un error al generar ventas: ' . $e->getMessage());
        }
    }

    public function generarComisionesCartera(): void
    {
        $this->validatePeriodo();

        try {
            $this->limpiarResultado();

            $request = new Request([
                'periodo' => $this->periodoGenerar,
            ]);

            $controller = app(ComisionesController::class);
            $resultado = $controller->indexCartera($request);

            if (is_array($resultado) && array_key_exists('codigo_asesor', $resultado)) {
                $this->resultadoGenerado = [$resultado];
            } else {
                $this->resultadoGenerado = is_array($resultado) ? $resultado : [];
            }

            $this->tipoResultado = 'cartera';

            session()->flash('success', 'Comisiones de cartera generadas correctamente.');
        } catch (\Throwable $e) {
            $this->limpiarResultado();
            session()->flash('error', 'Ocurrió un error al generar cartera: ' . $e->getMessage());
        }
    }

    public function exportarVentas()
    {
        if (empty($this->resultadoGenerado) || $this->tipoResultado !== 'ventas') {
            session()->flash('error', 'Primero debes generar el informe de ventas.');
            return;
        }

        $rows = [];
        $rows[] = [
            'Asesor',
            'Código',
            'Categoría',
            '% Llantas',
            'Comisión Llantas',
            '% Repuestos',
            'Comisión Repuestos',
            '% Pirelli',
            'Comisión Pirelli',
            '% Total',
            'Comisión Total',
        ];

        foreach ($this->resultadoGenerado as $fila) {
            $rows[] = [
                $fila['nombre_asesor'] ?? '',
                $fila['codigo_asesor'] ?? '',
                $fila['categoria_asesor'] ?? '',
                $fila['llantas']['cumplimiento'] ?? 0,
                $fila['llantas']['comision'] ?? 0,
                $fila['repuestos']['cumplimiento'] ?? 0,
                $fila['repuestos']['comision'] ?? 0,
                $fila['pirelli']['cumplimiento'] ?? 0,
                $fila['pirelli']['comision'] ?? 0,
                $fila['total']['cumplimiento'] ?? 0,
                (($fila['repuestos']['comision'] ?? 0) + ($fila['llantas']['comision'] ?? 0) + ($fila['pirelli']['comision'] ?? 0)),
            ];
        }

        $rows[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'TOTAL GENERAL',
            collect($this->resultadoGenerado)->sum(
                fn($i) => (($i['repuestos']['comision'] ?? 0) + ($i['llantas']['comision'] ?? 0) + ($i['pirelli']['comision'] ?? 0))
            ),
        ];

        return Excel::download(
            new class($rows) implements FromArray {
                public function __construct(private array $rows) {}
                public function array(): array
                {
                    return $this->rows;
                }
            },
            'comisiones_ventas_' . $this->periodoGenerar . '.xlsx'
        );
    }

    public function exportarCartera()
    {
        if (empty($this->resultadoGenerado) || $this->tipoResultado !== 'cartera') {
            session()->flash('error', 'Primero debes generar el informe de cartera.');
            return;
        }

        $rows = [];
        $rows[] = [
            'Asesor',
            'Código',
            'Categoría',
            'Presupuesto',
            'Recaudo Presupuesto',
            '% Cumplimiento',
            '% Clientes',
            '% Comisión',
            'Recaudo 1-15',
            'Comisión 1-15',
            'Recaudo 16-30',
            'Comisión 16-30',
            'Recaudo 31-45',
            'Comisión 31-45',
            'Recaudo 46-65',
            'Comisión 46-65',
            'Recaudo 66-80',
            'Comisión 66-80',
            'Recaudo >81',
            'Comisión >81',
            'Total Recaudo Sin Flete',
            'Comisión a Pagar',
        ];

        foreach ($this->resultadoGenerado as $fila) {
            $detalle = data_get($fila, 'catera.recuadoPorDias.data_asesores.0', []);

            $rows[] = [
                $fila['nombre_asesor'] ?? '',
                $fila['codigo_asesor'] ?? '',
                $fila['categoria_asesor'] ?? '',
                data_get($fila, 'catera.totalPresupuesto', 0),
                data_get($fila, 'catera.recaudoPresupuesto', 0),
                data_get($fila, 'catera.cumplimiento', 0),
                data_get($fila, 'catera.porcentajeClientes', 0),
                data_get($fila, 'catera.comisionRecaudoPresupuesto', 0),

                data_get($detalle, 'recaudo_1_15', 0),
                data_get($detalle, 'comision_1_a_15', 0),

                data_get($detalle, 'recaudo_16_30', 0),
                data_get($detalle, 'comision_16_a_30', 0),

                data_get($detalle, 'recaudo_31_45', 0),
                data_get($detalle, 'comision_31_a_45', 0),

                data_get($detalle, 'recaudo_46_65', 0),
                data_get($detalle, 'comision_46_a_65', 0),

                data_get($detalle, 'recaudo_66_80', 0),
                data_get($detalle, 'comision_66_a_80', 0),

                data_get($detalle, 'recaudo_mayor_81', 0),
                data_get($detalle, 'comision_mayor_81', 0),

                data_get($fila, 'catera.totalRecaudoSinFlete', 0),
                (data_get($detalle, 'comision_a_pagar', 0) + data_get($fila, 'catera.comisionRecaudoPresupuesto', 0)),
            ];
        }

        $rows[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'TOTAL GENERAL',
            collect($this->resultadoGenerado)->sum(fn($i) => data_get($i, 'catera.totalRecaudoSinFlete', 0)),
            collect($this->resultadoGenerado)->sum(
                fn($i) => data_get($i, 'catera.recuadoPorDias.totales.total_comision_dias', 0) + data_get($i, 'catera.comisionRecaudoPresupuesto', 0)
            ),
        ];

        return Excel::download(
            new class($rows) implements FromArray {
                public function __construct(private array $rows) {}
                public function array(): array
                {
                    return $this->rows;
                }
            },
            'comisiones_cartera_' . $this->periodoGenerar . '.xlsx'
        );
    }

    public function limpiarResultado(): void
    {
        $this->resultadoGenerado = [];
        $this->tipoResultado = '';
    }

    private function validatePeriodo(): void
    {
        $this->validate([
            'periodoGenerar' => ['required', 'regex:/^\d{6}$/'],
        ], [
            'periodoGenerar.required' => 'Debes ingresar un período.',
            'periodoGenerar.regex' => 'El período debe estar en formato YYYYMM.',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.comisiones.index');
    }
}