<?php

namespace App\Livewire\Admin\Comisiones;

use Livewire\Component;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ComisionesController;

class Index extends Component
{
    public string $periodoGenerar = '';
    public array $resultadoGenerado = [];

    public function mount(): void
    {
        $this->periodoGenerar = now()->format('Ym');
        $this->resultadoGenerado = [];
    }

    public function generarComisionesVenta(): void
    {
        $this->validate([
            'periodoGenerar' => ['required', 'regex:/^\d{6}$/'],
        ], [
            'periodoGenerar.required' => 'Debes ingresar un período.',
            'periodoGenerar.regex' => 'El período debe estar en formato YYYYMM.',
        ]);

        try {
            $request = new Request([
                'periodo' => $this->periodoGenerar,
            ]);

            $controller = app(ComisionesController::class);
            $resultado = $controller->indexVentas($request);

            $this->resultadoGenerado = is_array($resultado) ? $resultado : [];

            session()->flash('success', 'Generación completada correctamente.');
        } catch (\Throwable $e) {
            $this->resultadoGenerado = [];

            session()->flash('error', 'Ocurrió un error al generar: ' . $e->getMessage());
        }
    }

    public function limpiarResultado(): void
    {
        $this->resultadoGenerado = [];
    }

    public function render()
    {
        return view('livewire.admin.comisiones.index');
    }
}