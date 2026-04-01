<?php

namespace App\Livewire\Admin\SolicitudesCredito;

use App\Models\SolicitudCredito;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $modal = false;

    public $solicitud = [
        'cod_depto' => '',
        'depto' => '',
        'cod_ciudad' => '',
        'ciudad' => '',
        'fecha_solicitud' => '',
        'razon_social' => '',
        'nombre_comercial' => '',
        'nit_cc' => '',
        'representante_legal' => '',
        'identificacion_representante' => '',
        'direccion_negocio' => '',
        'barrio' => '',
        'telefono_fijo' => '',
        'celular' => '',
        'correo_electronico' => '',
        'contacto_compras' => '',
        'telefono_compras' => '',
        'correo_compras' => '',
        'contacto_tesoreria' => '',
        'telefono_tesoreria' => '',
        'correo_tesoreria' => '',
        'contacto_factura_electronica' => '',
        'telefono_factura_electronica' => '',
        'correo_factura_electronica' => '',
        'rte_fuente' => false,
        'rte_iva' => false,
        'rte_ica' => false,
        'antiguedad_comercial' => '',
        'tiempo_antiguedad' => '',
        'tipo_negocio' => '',
        'puntos_venta' => '',
        'canal_tradicional' => '',
        'canal_corporativo' => '',
        'numero_empleados' => '',
        'ventas_proyectadas_mes' => '',
        'cupo_sugerido' => '',
        'autorizacion_cod_depto' => '',
        'autorizacion_depto' => '',
        'autorizacion_cod_ciudad' => '',
        'autorizacion_ciudad' => '',
        'autorizacion_fecha' => '',
        'autorizacion_nombre_1' => '',
        'autorizacion_documento_1' => '',
        'autorizacion_lugar_expedicion_1' => '',
        'autorizacion_razon_social' => '',
        'autorizacion_nit_cc' => '',
        'autorizacion_nombre_2' => '',
        'autorizacion_documento_2' => '',
        'autorizacion_lugar_expedicion_2' => '',
        'autorizacion_telefono_fijo' => '',
        'autorizacion_celular' => '',
        'autorizacion_correo' => '',
        'autorizacion_direccion' => '',
        'estado' => 'borrador',
    ];

    public $referencias = [];
    public $direcciones = [];

    protected function rules()
    {
        return [
            'solicitud.fecha_solicitud' => 'required|date',
            'solicitud.razon_social' => 'required|string|max:255',
            'solicitud.nit_cc' => 'required|string|max:30',
            'solicitud.celular' => 'required|string|max:30',
            'solicitud.correo_electronico' => 'required|email|max:255',
            'solicitud.ciudad' => 'required|string|max:120',
            'solicitud.depto' => 'required|string|max:120',
            'solicitud.autorizacion_fecha' => 'nullable|date',
        ];
    }

    public function mount()
    {
        $this->agregarReferencia();
    }

    public function abrirModal()
    {
        $this->resetFormulario();
        $this->modal = true;
    }

    public function cerrarModal()
    {
        $this->modal = false;
    }

    public function resetFormulario()
    {
        $this->reset(['solicitud', 'referencias', 'direcciones']);

        $this->solicitud = [
            'cod_depto' => '',
            'depto' => '',
            'cod_ciudad' => '',
            'ciudad' => '',
            'fecha_solicitud' => now()->format('Y-m-d'),
            'razon_social' => '',
            'nombre_comercial' => '',
            'nit_cc' => '',
            'representante_legal' => '',
            'identificacion_representante' => '',
            'direccion_negocio' => '',
            'barrio' => '',
            'telefono_fijo' => '',
            'celular' => '',
            'correo_electronico' => '',
            'contacto_compras' => '',
            'telefono_compras' => '',
            'correo_compras' => '',
            'contacto_tesoreria' => '',
            'telefono_tesoreria' => '',
            'correo_tesoreria' => '',
            'contacto_factura_electronica' => '',
            'telefono_factura_electronica' => '',
            'correo_factura_electronica' => '',
            'rte_fuente' => false,
            'rte_iva' => false,
            'rte_ica' => false,
            'antiguedad_comercial' => '',
            'tiempo_antiguedad' => '',
            'tipo_negocio' => '',
            'puntos_venta' => '',
            'canal_tradicional' => '',
            'canal_corporativo' => '',
            'numero_empleados' => '',
            'ventas_proyectadas_mes' => '',
            'cupo_sugerido' => '',
            'autorizacion_cod_depto' => '',
            'autorizacion_depto' => '',
            'autorizacion_cod_ciudad' => '',
            'autorizacion_ciudad' => '',
            'autorizacion_fecha' => now()->format('Y-m-d'),
            'autorizacion_nombre_1' => '',
            'autorizacion_documento_1' => '',
            'autorizacion_lugar_expedicion_1' => '',
            'autorizacion_razon_social' => '',
            'autorizacion_nit_cc' => '',
            'autorizacion_nombre_2' => '',
            'autorizacion_documento_2' => '',
            'autorizacion_lugar_expedicion_2' => '',
            'autorizacion_telefono_fijo' => '',
            'autorizacion_celular' => '',
            'autorizacion_correo' => '',
            'autorizacion_direccion' => '',
            'estado' => 'borrador',
        ];

        $this->referencias = [];
        $this->direcciones = [];

        $this->agregarReferencia();
    }

    public function agregarReferencia()
    {
        if (count($this->referencias) >= 6) {
            return;
        }

        $this->referencias[] = [
            'empresa' => '',
            'nit' => '',
            'cod_depto' => '',
            'depto' => '',
            'cod_ciudad' => '',
            'ciudad' => '',
            'telefono' => '',
            'cupo_credito' => '',
        ];
    }

    public function eliminarReferencia($index)
    {
        unset($this->referencias[$index]);
        $this->referencias = array_values($this->referencias);
    }

    public function agregarDireccion()
    {
        if (count($this->direcciones) >= 3) {
            return;
        }

        $this->direcciones[] = [
            'contacto' => '',
            'direccion' => '',
            'cod_depto' => '',
            'depto' => '',
            'cod_ciudad' => '',
            'ciudad' => '',
            'telefono' => '',
        ];
    }

    public function eliminarDireccion($index)
    {
        unset($this->direcciones[$index]);
        $this->direcciones = array_values($this->direcciones);
    }

    public function referenciaCompleta($ref): bool
    {
        return filled($ref['empresa'])
            && filled($ref['nit'])
            && filled($ref['depto'])
            && filled($ref['ciudad'])
            && filled($ref['telefono'])
            && filled($ref['cupo_credito']);
    }

    public function guardar()
    {
        $this->validate();

        $referenciasCompletas = collect($this->referencias)
            ->filter(fn ($ref) => $this->referenciaCompleta($ref))
            ->values();

        if ($referenciasCompletas->count() < 3) {
            $this->addError('referencias', 'Debes registrar mínimo 3 referencias comerciales completas.');
            return;
        }

        DB::transaction(function () use ($referenciasCompletas) {
            $solicitud = SolicitudCredito::create([
                ...$this->solicitud,
                'user_id' => Auth::id(),
            ]);

            foreach ($referenciasCompletas as $referencia) {
                $solicitud->referencias()->create($referencia);
            }

            foreach ($this->direcciones as $direccion) {
                if (
                    filled($direccion['contacto']) ||
                    filled($direccion['direccion']) ||
                    filled($direccion['ciudad']) ||
                    filled($direccion['telefono'])
                ) {
                    $solicitud->direcciones()->create($direccion);
                }
            }
        });

        session()->flash('success', 'Solicitud de crédito creada correctamente.');

        $this->cerrarModal();
        $this->resetFormulario();
    }

    public function render()
    {
        $solicitudes = SolicitudCredito::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('razon_social', 'like', '%' . $this->search . '%')
                      ->orWhere('nit_cc', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre_comercial', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.solicitudes-credito.index', compact('solicitudes'));
    }
}