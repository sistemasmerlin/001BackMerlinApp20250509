<?php

namespace App\Livewire\Admin\Promociones;

use App\Models\Promocion;
use App\Models\User;
use App\Models\PromocionDetalle;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportPromociones;

class Detalle extends Component
{
    use WithFileUploads;

    public $promocion;
    public $detalles;

    public $archivoCsv;

    protected $rules = [
        'archivoCsv' => 'required|file|mimes:csv,txt',
    ];

    
    public function importarCsv()
    {
        $this->validate();
    
        $path = $this->archivoCsv->getRealPath();
        $handle = fopen($path, 'r');
    


        $index = 0;

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            if ($index === 0) {
                $index++;
                continue; // saltar encabezado
            }

            // Asegurar al menos 6 columnas
            if (count($row) < 6) {
                continue;
            }

            PromocionDetalle::create([
                'promocion_id' => $this->promocion->id,
                'tipo'         => $row[0] ?? '',
                'descripcion'  => $row[1] ?? '',
                'acumulado'    => $row[2] == '1' || strtolower($row[2]) == 'true',
                'modelo'       => $row[3] ?? '',
                'desde'        => $row[4] ?? 0,
                'hasta'        => $row[5] ?? 0,
                'descuento'    => $row[6] ?? 0,
                'estado'       => true,
                'eliminado'    => false,
                'creado_por'   => auth()->user()?->name ?? 'sistema',
            ]);

            $index++;
        }
        fclose($handle);
    
        session()->flash('success', 'Detalles importados desde CSV correctamente.');
        $this->detalles = $this->promocion->detalles()->get();
    }
    
    public function mount(Promocion $promocion)
    {
        $this->promocion = $promocion;
        $this->detalles = $promocion->detalles()->get();
    }

    public function importarExcel()
    {
        $this->validate();

        try {
            Excel::import(new PromocionDetalleImport($this->promocion->id, auth()->user()->name), $this->archivoExcel);
            session()->flash('success', 'Detalles importados correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al importar el archivo: ' . $e->getMessage());
        }

        $this->detalles = $this->promocion->detalles()->get();

        return redirect()->route('promociones.index');
    }

    public function render()
    {
        return view('livewire.admin.promociones.detalle');
    }
}
