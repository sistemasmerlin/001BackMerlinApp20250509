<?php

namespace App\Livewire\Admin\PresupuestosComerciales;

use Livewire\Component;
use App\Models\PresupuestoComercial;
use Livewire\WithPagination;
use App\Imports\PresupuestosComercialesImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithPagination, WithFileUploads,  WithPagination;

    public int $perPage = 500;
    public bool $modal = false;
    public bool $modoEditar = false;
    public bool $confirmarBorrar = false;
    public bool $modalImport = false;
    public $archivo; // input file
    public array $statsImport = ['creados' => 0, 'actualizados' => 0];
    public array $erroresImport = [];
    public $search = '';
    public $fPeriodo = '';
    public $fAsesor = '';
    public $fCategoria = '';
    public $fMarca = '';
    public $fTipo = '';

    // Campos del formulario
    public $presupuestoId = null;
    public $codigo_asesor = '';
    public $periodo = '';
    public $presupuesto = '';
    public $marca = '';
    public $categoria = 'llantas';
    public $clasificacion_asesor = '';
    public $tipo_presupuesto = '';

    public $showModal = false;

    protected function rules()
    {
        $unique = 'unique:presupuestos_comerciales,codigo_asesor,NULL,id,periodo,' . $this->periodo . ',marca,' . $this->marca . ',categoria,' . $this->categoria . ',tipo_presupuesto,' . $this->tipo_presupuesto;

        if ($this->presupuestoId) {
            $unique = 'unique:presupuestos_comerciales,codigo_asesor,' . $this->presupuestoId . ',id,periodo,' . $this->periodo . ',marca,' . $this->marca . ',categoria,' . $this->categoria . ',tipo_presupuesto,' . $this->tipo_presupuesto;
        }

        return [
            'codigo_asesor'      => ['required', 'string', 'max:20', $unique],
            'periodo'            => ['required', 'regex:/^\d{6}$/'], // YYYYMM
            'presupuesto'        => ['required', 'numeric', 'min:0'],
            'marca'              => ['nullable', 'string', 'max:100'],
            'categoria'          => ['required', 'in:llantas,repuestos, total'],
            'clasificacion_asesor' => ['nullable', 'string', 'max:50'],
            'tipo_presupuesto'   => ['required', 'string', 'max:50'],
        ];
    }

    public function updating($name, $value)
    {
        if (in_array($name, ['search', 'fPeriodo', 'fAsesor', 'fCategoria', 'fMarca', 'fTipo'])) {
            $this->resetPage(); // paginación
        }
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->presupuestoId = $id;

        if ($id) {
            $p = PresupuestoComercial::findOrFail($id);
            $this->codigo_asesor = $p->codigo_asesor;
            $this->periodo = $p->periodo;
            $this->presupuesto = $p->presupuesto;
            $this->marca = $p->marca;
            $this->categoria = $p->categoria;
            $this->clasificacion_asesor = $p->clasificacion_asesor;
            $this->tipo_presupuesto = $p->tipo_presupuesto;
        } else {
            $this->codigo_asesor = '';
            $this->periodo = '';
            $this->presupuesto = '';
            $this->marca = '';
            $this->categoria = 'llantas';
            $this->clasificacion_asesor = '';
            $this->tipo_presupuesto = '';
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'codigo_asesor' => $this->codigo_asesor,
            'periodo' => $this->periodo,
            'presupuesto' => $this->presupuesto ?: 0,
            'marca' => $this->marca ?: null,
            'categoria' => $this->categoria,
            'clasificacion_asesor' => $this->clasificacion_asesor ?: null,
            'tipo_presupuesto' => $this->tipo_presupuesto,
        ];

        PresupuestoComercial::updateOrCreate(
            ['id' => $this->presupuestoId],
            $data
        );

        $this->showModal = false;
        session()->flash('success', 'Presupuesto guardado correctamente.');
    }

    public function delete($id)
    {
        PresupuestoComercial::findOrFail($id)->delete();
        session()->flash('success', 'Presupuesto eliminado.');
    }

    public function crear()
    {
        $this->resetValidation();
        $this->presupuestoId = null;
        $this->modoEditar = false;
        $this->codigo_asesor = '';
        $this->periodo = '';
        $this->tipo_presupuesto = '';
        $this->presupuesto = '';
        $this->marca = null;
        $this->categoria = 'llantas';
        $this->clasificacion_asesor = null;
        $this->modal = true;
    }

    public function editar(int $id)
    {
        $this->resetValidation();
        $p = PresupuestoComercial::findOrFail($id);

        $this->presupuestoId = $p->id;
        $this->codigo_asesor = $p->codigo_asesor;
        $this->periodo = $p->periodo;
        $this->tipo_presupuesto = $p->tipo_presupuesto;
        $this->presupuesto = (string)$p->presupuesto;
        $this->marca = $p->marca;
        $this->categoria = $p->categoria;
        $this->clasificacion_asesor = $p->clasificacion_asesor;

        $this->modoEditar = true;
        $this->modal = true;
    }

    public function cerrarModal()
    {
        $this->modal = false;
        $this->modoEditar = false;
    }

    public function guardar()
    {
        $this->validate([
            'codigo_asesor' => ['required', 'max:20'],
            'periodo' => ['required', 'regex:/^\d{6}$/'],
            'tipo_presupuesto' => ['required', 'max:50'],
            'presupuesto' => ['required', 'numeric', 'min:0'],
            'categoria' => ['required', 'in:llantas,repuestos, total'],
            'marca' => ['nullable', 'max:100'],
            'clasificacion_asesor' => ['nullable', 'max:50'],
        ]);

        $data = [
            'codigo_asesor' => $this->codigo_asesor,
            'periodo' => $this->periodo,
            'tipo_presupuesto' => $this->tipo_presupuesto,
            'presupuesto' => (float)$this->presupuesto,
            'marca' => $this->marca ?: null,
            'categoria' => $this->categoria,
            'clasificacion_asesor' => $this->clasificacion_asesor ?: null,
        ];

        PresupuestoComercial::updateOrCreate(['id' => $this->presupuestoId], $data);

        $this->cerrarModal();
        session()->flash('success', 'Presupuesto guardado correctamente.');
    }

    public function confirmarEliminar(int $id)
    {
        $this->presupuestoId = $id;
        $this->confirmarBorrar = true;
    }

    public function eliminar()
    {
        PresupuestoComercial::findOrFail($this->presupuestoId)->delete();
        $this->confirmarBorrar = false;
        session()->flash('success', 'Presupuesto eliminado.');
    }

    public function abrirImport()
    {
        $this->resetValidation();
        $this->archivo = null;
        $this->statsImport = ['creados' => 0, 'actualizados' => 0];
        $this->erroresImport = [];
        $this->modalImport = true;
    }

    public function procesarImport()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,csv,txt|max:10240',
        ]);

        // Guardar en storage/app/imports
        $path = $this->archivo->store('imports', 'local');
        $absolutePath = Storage::disk('local')->path($path);

        // Detectar separador rápidamente leyendo la primera línea
        $fh = fopen($absolutePath, 'r');
        $first = $fh ? fgets($fh, 4096) : '';
        if ($fh) fclose($fh);

        $delimiter = (substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';

        // Instanciar el import con el separador detectado
        $import = new PresupuestosComercialesImport($delimiter);

        // Forzar formato CSV si la extensión es .csv; xlsx no lo necesita
        $format = str_ends_with(strtolower($absolutePath), '.csv') ? ExcelFormat::CSV : null;

        if ($format === ExcelFormat::CSV) {
            Excel::import($import, $absolutePath, null, $format); // respeta getCsvSettings()
        } else {
            Excel::import($import, $absolutePath);
        }

        // Mostrar resultados y errores
        $this->erroresImport = $import->errores;
        $this->modalImport = false;

        $msg = "Importación: {$import->creados} creados, {$import->actualizados} actualizados.";
        if (!empty($this->erroresImport)) {
            $msg .= " Con " . count($this->erroresImport) . " fila(s) con error.";
        }
        session()->flash('success', $msg);
    }

    public function render()
    {
        $q = PresupuestoComercial::query()
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($q) use ($s) {
                    $q->where('codigo_asesor', 'like', $s)
                        ->orWhere('marca', 'like', $s)
                        ->orWhere('clasificacion_asesor', 'like', $s)
                        ->orWhere('tipo_presupuesto', 'like', $s)
                        ->orWhere('periodo', 'like', $s);
                });
            })
            ->periodo($this->fPeriodo ?: null)
            ->asesor($this->fAsesor ?: null)
            ->categoria($this->fCategoria ?: null)
            ->when($this->fMarca, fn($q) => $q->where('marca', $this->fMarca))
            ->when($this->fTipo, fn($q) => $q->where('tipo_presupuesto', $this->fTipo))
            ->orderByDesc('periodo')
            ->orderBy('codigo_asesor');

        $presupuestos = $q->paginate(500);

        return view('livewire.admin.presupuestos-comerciales.index', compact('presupuestos'));
    }
}
