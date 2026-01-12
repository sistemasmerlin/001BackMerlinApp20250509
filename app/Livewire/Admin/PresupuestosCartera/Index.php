<?php

namespace App\Livewire\Admin\PresupuestosCartera;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

use App\Models\PresupuestoRecaudo;
use App\Imports\PresupuestosCarteraImport;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public int $perPage = 500;

    public bool $modalImport = false;
    public $archivo;

    public array $erroresImport = [];
    public string $search = '';

    // filtros
    public string $fPeriodo = '';
    public string $fAsesor = '';
    public string $fCondPago = '';

    public function updating($name, $value)
    {
        if (in_array($name, ['search', 'fPeriodo', 'fAsesor', 'fCondPago'])) {
            $this->resetPage();
        }
    }

    public function abrirImport()
    {
        $this->resetValidation();
        $this->archivo = null;
        $this->erroresImport = [];
        $this->modalImport = true;
    }

    public function procesarImport()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,csv,txt|max:10240',
        ]);

        $path = $this->archivo->store('imports', 'local');
        $absolutePath = Storage::disk('local')->path($path);

        // detectar separador si es CSV
        $delimiter = ',';
        if (str_ends_with(strtolower($absolutePath), '.csv') || str_ends_with(strtolower($absolutePath), '.txt')) {
            $fh = fopen($absolutePath, 'r');
            $first = $fh ? fgets($fh, 4096) : '';
            if ($fh) fclose($fh);
            $delimiter = (substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';
        }

        $usuario = Auth::user();

        $import = new PresupuestosCarteraImport(
            $delimiter,
            $usuario->name // 👈 nombre del usuario logueado
        );

        $format = (str_ends_with(strtolower($absolutePath), '.csv') || str_ends_with(strtolower($absolutePath), '.txt'))
            ? ExcelFormat::CSV
            : null;

        if ($format === ExcelFormat::CSV) {
            Excel::import($import, $absolutePath, null, $format);
        } else {
            Excel::import($import, $absolutePath);
        }

        $this->erroresImport = $import->errores ?? [];
        $this->modalImport = false;

        $msg = "Importación: {$import->creados} creados, {$import->actualizados} actualizados.";
        if (!empty($this->erroresImport)) {
            $msg .= " Con " . count($this->erroresImport) . " fila(s) con error.";
        }
        session()->flash('success', $msg);
    }

    public function render()
    {
        $q = PresupuestoRecaudo::query()
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($q) use ($s) {
                    $q->where('asesor', 'like', $s)
                      ->orWhere('nombre_asesor', 'like', $s)
                      ->orWhere('nit_cliente', 'like', $s)
                      ->orWhere('cliente', 'like', $s)
                      ->orWhere('prefijo', 'like', $s)
                      ->orWhere('consecutivo', 'like', $s)
                      ->orWhere('periodo', 'like', $s)
                      ->orWhere('cond_pago', 'like', $s);
                });
            })
            ->when($this->fPeriodo, fn($q) => $q->where('periodo', $this->fPeriodo))
            ->when($this->fAsesor, fn($q) => $q->where('asesor', $this->fAsesor))
            ->when($this->fCondPago, fn($q) => $q->where('cond_pago', $this->fCondPago))
            ->where('eliminado', 0)
            ->orderByDesc('periodo')
            ->orderBy('asesor')
            ->orderBy('nit_cliente');

        $recaudos = $q->paginate($this->perPage);

        return view('livewire.admin.presupuestos-cartera.index', compact('recaudos'));
    }
}
