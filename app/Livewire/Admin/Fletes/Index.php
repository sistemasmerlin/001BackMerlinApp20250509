<?php

namespace App\Livewire\Admin\Fletes;
use App\Models\FleteCiudad;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public $fletes;
    public $ciudades = [];
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
                continue;
            }

            if (count($row) < 10) {
                continue;
            }

            FleteCiudad::create([
                'depto'             => trim($row[0] ?? ''),
                'cod_depto'         => trim($row[1] ?? ''),
                'ciudad'            => trim($row[2] ?? ''),
                'cod_ciudad'        => trim($row[3] ?? ''),
                'menor'             => trim($row[4] ?? ''),
                'mayor'             => trim($row[5] ?? 0),
                'minimo'            => trim($row[6] ?? 0),
                'entrega'           => trim($row[7] ?? 0),
                'monto'             => trim($row[8] ?? 0),
                'monto_minimo'      => trim($row[9] ?? 0),
            ]);

            $index++;
        }

        fclose($handle);

        // ✅ Recargar ciudades en la vista (o la tabla que quieras mostrar)
        $this->ciudades = FleteCiudad::all();

        session()->flash('success', 'Detalles importados desde CSV correctamente.');
    }

    public function mount()
    {
        $this->fletes = FleteCiudad::where('estado','=', '1')->orderBy('depto','asc')->orderBy('ciudad','asc')->get();

    }

    public function render()
    {
        return view('livewire.admin.fletes.index');
    }
}
