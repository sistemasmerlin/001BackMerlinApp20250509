<?php

namespace App\Livewire\Admin\Fletes;
use App\Models\FleteCiudad;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportFletes;

class Index extends Component
{
    use WithFileUploads;

    public $fletes;
    public $ciudades = [];
    public $archivoCsv;
    public $excel_fletes;

    public $modalEditar = false;
    public $fleteId;
    public $depto, $cod_depto, $ciudad, $cod_ciudad, $menor, $mayor, $minimo, $entrega, $monto, $monto_minimo;


    /*protected $rules = [
        'archivoCsv' => 'required|file|mimes:csv,txt',
    ];*/
    
    protected $rules = [
        'excel_fletes' => 'required|file|mimes:xls,xlsx|max:2048',
    ];

    public function importarCsv()
    {
        $this->validate();

        FleteCiudad::truncate();
        
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

    public function importarFlete(){

        $this->validate();

        FleteCiudad::truncate();

        Excel::import(new ImportFletes, $this->excel_fletes->getRealPath());

        session()->flash('success', 'Archivo importado correctamente.');
        $this->reset('excel_fletes'); // limpia el input

    }

    public function eliminarFlete($id)
    {
        try {
            $flete = FleteCiudad::findOrFail($id);
            $flete->delete();
    
            session()->flash('success', 'Flete eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Hubo un problema al eliminar el flete.');
        }

        return redirect(request()->header('Referer'));
    }

    public function editarFlete($id){
        $flete = FleteCiudad::findOrFail($id);

        $this->fleteId = $flete->id;
        $this->depto = $flete->depto;
        $this->cod_depto = $flete->cod_depto;
        $this->ciudad = $flete->ciudad;
        $this->cod_ciudad = $flete->cod_ciudad;
        $this->menor = $flete->menor;
        $this->mayor = $flete->mayor;
        $this->minimo = $flete->minimo;
        $this->entrega = $flete->entrega;
        $this->monto = $flete->monto;
        $this->monto_minimo = $flete->monto_minimo;

        $this->modalEditar = true;
    }

    public function actualizarFlete(){

            //dd($this->fleteId, $this->depto, $this->monto);

                $this->validate([
                    'depto'        => 'required|string',
                    'cod_depto'    => 'required|string',
                    'ciudad'       => 'required|string',
                    'cod_ciudad'   => 'required|string',
                    'menor'        => 'required|numeric',   
                    'mayor'        => 'required|numeric',   
                    'minimo'       => 'required|integer',   
                    'entrega'      => 'required|integer',   
                    'monto'        => 'required|integer',   
                    'monto_minimo' => 'required|integer',   
                ]);

            $flete = FleteCiudad::findOrFail($this->fleteId);

            $flete->update([
                'depto' => $this->depto,
                'cod_depto' => $this->cod_depto,
                'ciudad' => $this->ciudad,
                'cod_ciudad' => $this->cod_ciudad,
                'menor' => $this->menor,
                'mayor' => $this->mayor,
                'minimo' => $this->minimo,
                'entrega' => $this->entrega,
                'monto' => $this->monto,
                'monto_minimo' => $this->monto_minimo,
            ]);

            $this->modalEditar = false;
            session()->flash('success', 'Flete actualizado correctamente.');

            return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.admin.fletes.index');
    }
}
