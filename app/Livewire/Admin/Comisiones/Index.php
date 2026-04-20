<?php

namespace App\Livewire\Admin\Comisiones;

use Livewire\Component;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ComisionesController;

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

            /**
             * Si el controlador retorna un solo asesor como array asociativo,
             * lo envolvemos para poder pintarlo en tabla.
             */
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