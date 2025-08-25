<?php

namespace App\Livewire\Admin\Pedidos;


use App\Models\Pedido;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Index extends Component
{
    public $pedidos;

    public $notaId;
    public $observacion;
    public $pedidoIdParaNit = null;
    public $nuevoNit = '';
    public $nuevaSucursal = '';
    public $nuevaListaPrecios = '';
    public $nuevoPuntoEnvio = '';
    public $mensajeCliente = '';
    public $fecha_inicio;
    public $fecha_final;

    public function mount()
    {
        /* $this->pedidos = Pedido::with('direccionEnvio')->orderBy('id', 'desc')->get(); */
        
        $this->fecha_inicio = request()->query('fecha_inicio');
        $this->fecha_final = request()->query('fecha_final');

        $query = Pedido::with('direccionEnvio')->orderBy('id', 'desc');

        if ($this->fecha_inicio && $this->fecha_final) {
            $fechaInicio = Carbon::parse($this->fecha_inicio)->startOfDay();
            $fechaFinal = Carbon::parse($this->fecha_final)->endOfDay();

            $query->whereBetween('fecha_pedido', [$fechaInicio, $fechaFinal]);
        
            $this->pedidos = $query->get();

        }else{
            
            $this->pedidos = $query->limit(40)->get()->sortByDesc('id');
        }

            //dd($this->pedidos );
        }

    public function eliminarCotizacion($id){

        $cotizacion = Pedido::find($id);

        if (! $cotizacion) {
            session()->flash('error', 'Promoción no encontrada');
            return;
        }

        $cotizacion->delete();

        session()->flash('success', 'Cotizacion eliminada correctamente');

        return redirect()->route('pedidos.index');

    }

    public function render()
    {
      return view('livewire.admin.pedidos.index', ['pedidos' => $this->pedidos]);

    }


    public function editarNota($id){
            $pedido = Pedido::find($id);

            if (! $pedido) {
                session()->flash('error', 'Pedido no encontrado.');
                return;
            }

            $this->notaId = $pedido->id;
            $this->observacion = $pedido->observaciones;
        }


    public function abrirModalNit($pedidoId)
    {
        $pedido = Pedido::find($pedidoId);
        if (!$pedido) {
            session()->flash('error', 'Pedido no encontrado.');
            return;
        }

        $this->pedidoIdParaNit = $pedido->id;
        $this->nuevoNit = ''; // Limpia el campo
        $this->mensajeCliente = '';
        // El modal se abre con el <flux:modal.trigger> en la vista
    }

    public function guardarNuevoNit()
    {




    // Valida campos (y fuerza los valores permitidos en los selects)
    $this->validate([
        'pedidoIdParaNit'  => 'required|integer|exists:pedidos,id',
        'nuevoNit'         => 'required|regex:/^[0-9]{5,15}$/',
        'nuevaSucursal'    => 'required',
        'nuevaListaPrecios'=> 'required',
        'nuevoPuntoEnvio'  => 'required'
    ], [
        'nuevoNit.required'          => 'El NIT es obligatorio',
        'nuevoNit.regex'             => 'El NIT debe ser numérico (5 a 15 dígitos)',
        'nuevaSucursal.required'     => 'Selecciona la sucursal',
        'nuevaListaPrecios.required' => 'Selecciona la lista de precios',
        'nuevoPuntoEnvio.required'   => 'Selecciona el punto de envío',
    ]);



        $rows = DB::connection('sqlsrv')->select("SELECT TOP 1
            t200.f200_rowid AS tercero_id,
            t200.f200_nit,
            RTRIM(t200.f200_dv_nit) AS f200_dv_nit,
            t201.f201_id_sucursal,
            t215.f215_id AS punto_envio_id,
            MAX(t200.f200_razon_social) AS f200_razon_social,
            MAX(t200.f200_id_tipo_ident) AS f200_id_tipo_ident,
            MAX(t200.f200_ind_tipo_tercero) AS f200_ind_tipo_tercero,
            MAX(t200.f200_apellido1) AS f200_apellido1,
            MAX(t200.f200_apellido2) AS f200_apellido2,
            MAX(t200.f200_nombres) AS f200_nombres,
            MAX(t200.f200_nombre_est) AS f200_nombre_est,
            MAX(RTRIM(t201.f201_id_vendedor)) AS f201_id_vendedor,
            MAX(t201.f201_descripcion_sucursal) AS f201_descripcion_sucursal,
            MAX(t015.f015_id_pais) AS f015_id_pais,
            MAX(t015.f015_id_depto) AS f015_id_depto,
            MAX(t012.f012_descripcion) AS f012_descripcion,
            MAX(t015.f015_id_ciudad) AS f015_id_ciudad,
            MAX(t013.f013_descripcion) AS f013_descripcion,
            MAX(t015.f015_direccion1) AS f015_direccion1,
            MAX(t015.f015_email) AS f015_email,
            MAX(t015.f015_contacto) AS f015_contacto,
            MAX(t015.f015_telefono) AS f015_telefono,
            MAX(t015.f015_celular) AS f015_celular,
            MAX(t201.f201_id_cond_pago) AS f201_id_cond_pago,
            MAX(t201.f201_cupo_credito) AS f201_cupo_credito,
            MAX(t201.f201_id_lista_precio) AS f201_id_lista_precio,
            MAX(t206.f206_descripcion) AS f206_descripcion,
            MAX(t215.f215_descripcion) AS descripcion_punto_envio,
            MAX(t015.f015_rowid) AS contacto_id
        FROM t200_mm_terceros t200
        JOIN t201_mm_clientes t201
            ON t200.f200_rowid = t201.f201_rowid_tercero
        JOIN t215_mm_puntos_envio_cliente t215
            ON t215.f215_rowid_tercero = t201.f201_rowid_tercero AND t215.f215_id_sucursal = t201.f201_id_sucursal
        LEFT JOIN t015_mm_contactos t015
            ON t015.f015_rowid = t215.f215_rowid_contacto
        LEFT JOIN t012_mm_deptos t012 ON t012.f012_id = t015.f015_id_depto AND t012.f012_id_pais = 169
        LEFT JOIN t013_mm_ciudades t013 ON t013.f013_id = t015.f015_id_ciudad
            AND t013.f013_id_depto = t015.f015_id_depto AND t013.f013_id_pais = 169
        LEFT JOIN t207_mm_criterios_clientes t207 ON t207.f207_rowid_tercero = t201.f201_rowid_tercero
            AND t207.f207_id_sucursal = t201.f201_id_sucursal
            AND t207.f207_id_cia = t201.f201_id_cia
            AND t207.f207_id_plan_criterios = '005'
        LEFT JOIN t206_mm_criterios_mayores t206 
            ON t206.f206_id_plan = t207.f207_id_plan_criterios
            AND t206.f206_id_cia = t207.f207_id_cia 
            AND t206.f206_id = t207.f207_id_criterio_mayor
        WHERE t200.f200_ind_cliente = 1
            AND t200.f200_ind_estado = 1
            AND t200.f200_id_cia = 3
            AND t201.f201_ind_estado_activo = 1
            AND t201.f201_id_cia = 3
            AND t215.f215_ind_estado = 1
            AND t215.f215_id_cia = 3
            AND t200.f200_nit = '$this->nuevoNit'
            AND t201.f201_id_sucursal = $this->nuevaSucursal
            AND t201.f201_id_lista_precio = $this->nuevaListaPrecios
            AND t215.f215_id = $this->nuevoPuntoEnvio
        GROUP BY
            t200.f200_rowid,
            t200.f200_nit,
            t200.f200_dv_nit,
            t201.f201_id_sucursal,
            t215.f215_id");




            $nitValidado = '';
            $razonSocialValidada = '';
            $nuevaSucursalValidada = '';
            $nuevaCondicionPagoValidada = '';
            $nuevaListaPreciosValidada = '';
            $nuevoPuntoEnvioValidado = '';
            $idPuntoEnvioValidado = '';
            $direccionValidada = '';
            $ciudadValidada = '';
            $codigoCiudadValidada = '';
            $departamentoValidado = '';
            $codigoDepartamentoValidado = '';



            if(count($rows) > 0){
                foreach($rows as $row){
                    $nitValidado = $row->f200_nit;
                    $nuevaSucursalValidada = $row->f201_id_sucursal;
                    $razonSocialValidada = $row->f200_razon_social;
                    $nuevaListaPreciosValidada = $row->f201_id_lista_precio;
                    $nuevoPuntoEnvioValidado = $row->punto_envio_id;
                    $nuevaCondicionPagoValidada = $row->f201_id_cond_pago;
                    $direccionValidada = $row->f015_direccion1;
                    $ciudadValidada = $row->f013_descripcion;
                    $codigoCiudadValidada = $row->f015_id_ciudad;
                    $departamentoValidado = $row->f012_descripcion;
                    $codigoDepartamentoValidado = $row->f015_id_depto;
                }

                $objPedido = Pedido::with('direccionEnvio')->findOrFail($this->pedidoIdParaNit);
                $objPedido->nit = $nitValidado;
                $objPedido->razon_social = $razonSocialValidada;
                $objPedido->id_sucursal = $nuevaSucursalValidada;
                $objPedido->lista_precio = $nuevaListaPreciosValidada;
                $objPedido->condicion_pago = $nuevaCondicionPagoValidada;

                if ($objPedido->direccionEnvio) {
                    $objPedido->direccionEnvio->id_punto_envio = $nuevoPuntoEnvioValidado;
                    $objPedido->direccionEnvio->direccion = $direccionValidada;
                    $objPedido->direccionEnvio->ciudad = $ciudadValidada;
                    $objPedido->direccionEnvio->departamento = $departamentoValidado;
                    $objPedido->direccionEnvio->codigo_ciudad = $codigoCiudadValidada;
                    $objPedido->direccionEnvio->codigo_departamento = $codigoDepartamentoValidado;
                    $objPedido->direccionEnvio->save();
                }

                $objPedido->save();

            }else{

            }


                        dd(
                [
                    '$rows' => $rows,
                    '$count = count($rows);' => $count = count($rows),
                    '$this->nuevoNit' => $this->nuevoNit,
                    '$this->nuevaSucursal' => $this->nuevaSucursal,
                    '$this->nuevaListaPrecios' => $this->nuevaListaPrecios,
                    '$this->nuevoPuntoEnvio' => $this->nuevoPuntoEnvio,
                    '$objPedido' => $objPedido
                ]
            );

            try {
/*

        $cli = $resp->json('cliente');

        // === Actualiza el pedido con los datos del cliente ===
        $pedido->nit            = $cli['nit']            ?? $pedido->nit;
        $pedido->razon_social   = $cli['razon_social']   ?? $pedido->razon_social;
        $pedido->correo_cliente = $cli['correo']         ?? $pedido->correo_cliente;
        $pedido->id_sucursal    = $cli['id_sucursal']    ?? $pedido->id_sucursal;
        $pedido->condicion_pago = $cli['cond_pago']      ?? $pedido->condicion_pago;
        $pedido->lista_precio   = $cli['lista_precio']   ?? $pedido->lista_precio;

        // Si quieres guardar punto de envío a nivel de dirección (relación 1:1)
        if ($pedido->direccionEnvio) {
            $dir = $pedido->direccionEnvio;
            $dir->punto_envio = $cli['punto_envio'] ?? $dir->punto_envio;
            $dir->direccion   = $cli['direccion']   ?? $dir->direccion;
            $dir->cod_depto   = $cli['depto']       ?? $dir->cod_depto;
            $dir->cod_ciudad  = $cli['ciudad']      ?? $dir->cod_ciudad;
            $dir->save();
        }

        $pedido->save();

        // Refresca solo la fila en memoria
        $this->refrescarFilaPedido($pedido->id); */

        session()->flash('success', 'Cliente actualizado en el pedido correctamente.');
        $this->dispatch('cerrarModal');

    } catch (\Throwable $e) {
        report($e);
        session()->flash('warning', 'No se pudo validar el cliente. Verifica conexión con SQL Server/API.');
    }
}


    public function guardarNota(){

            $pedido = Pedido::find($this->notaId);

            if ($pedido) {
                $pedido->observaciones = $this->observacion;
                $pedido->save();

                session()->flash('success', 'Nota actualizada correctamente.');
                $this->dispatch('cerrarModal');

            }

            return redirect()->route('pedidos.index');
        }

       
}
