<?php

namespace App\Livewire\Admin\Pedidos;


use App\Mail\ConfirmacionCliente;
use App\Models\Pedido;

use Illuminate\Support\Facades\Mail;
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
    public $nota;
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
        } else {

            $this->pedidos = $query->limit(40)->get()->sortByDesc('id');
        }

        //dd($this->pedidos );
    }

    public function eliminarCotizacion($id)
    {

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


    public function editarNota($id)
    {
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

    public function abrirModalClienteCreado($pedidoId)
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
        // 1) Validación de entrada
        $this->validate([
            'pedidoIdParaNit'   => 'required|integer|exists:pedidos,id',
            'nuevoNit'          => 'required|regex:/^[0-9]{5,15}$/',
            'nuevaSucursal'     => 'required',
            'nuevaListaPrecios' => 'required',
            'nuevoPuntoEnvio'   => 'required'
        ], [
            'nuevoNit.required'          => 'El NIT es obligatorio',
            'nuevoNit.regex'             => 'El NIT debe ser numérico (5 a 15 dígitos)',
            'nuevaSucursal.required'     => 'Selecciona la sucursal',
            'nuevaListaPrecios.required' => 'Selecciona la lista de precios',
            'nuevoPuntoEnvio.required'   => 'Selecciona el punto de envío',
        ]);

        try {
            // 2) Consulta segura en SQL Server (TOP 1) con parámetros enlazados
            $rows = DB::connection('sqlsrv')->select(
                "SELECT TOP 1
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
                    ON t215.f215_rowid_tercero = t201.f201_rowid_tercero
                AND t215.f215_id_sucursal   = t201.f201_id_sucursal
                LEFT JOIN t015_mm_contactos t015
                    ON t015.f015_rowid = t215.f215_rowid_contacto
                LEFT JOIN t012_mm_deptos t012
                    ON t012.f012_id = t015.f015_id_depto AND t012.f012_id_pais = 169
                LEFT JOIN t013_mm_ciudades t013
                    ON t013.f013_id = t015.f015_id_ciudad
                AND t013.f013_id_depto = t015.f015_id_depto
                AND t013.f013_id_pais  = 169
                LEFT JOIN t207_mm_criterios_clientes t207
                    ON t207.f207_rowid_tercero = t201.f201_rowid_tercero
                AND t207.f207_id_sucursal   = t201.f201_id_sucursal
                AND t207.f207_id_cia        = t201.f201_id_cia
                AND t207.f207_id_plan_criterios = '005'
                LEFT JOIN t206_mm_criterios_mayores t206 
                    ON t206.f206_id_plan = t207.f207_id_plan_criterios
                AND t206.f206_id_cia  = t207.f207_id_cia 
                AND t206.f206_id      = t207.f207_id_criterio_mayor
                WHERE t200.f200_ind_cliente = 1
                AND t200.f200_ind_estado = 1
                AND t200.f200_id_cia     = 3
                AND t201.f201_ind_estado_activo = 1
                AND t201.f201_id_cia     = 3
                AND t215.f215_ind_estado = 1
                AND t215.f215_id_cia     = 3
                AND t200.f200_nit              = ?
                AND t201.f201_id_sucursal      = ?
                AND t201.f201_id_lista_precio  = ?
                AND t215.f215_id               = ?
                GROUP BY
                    t200.f200_rowid,
                    t200.f200_nit,
                    t200.f200_dv_nit,
                    t201.f201_id_sucursal,
                    t215.f215_id",
                [
                    $this->nuevoNit,
                    (int) $this->nuevaSucursal,
                    (string) $this->nuevaListaPrecios,
                    (int) $this->nuevoPuntoEnvio,
                ]
            );

            if (count($rows) === 0) {
                // No se encontró combinación válida
                session()->flash('warning', 'No se encontró el cliente con los criterios seleccionados (NIT, sucursal, lista de precios y punto de envío). Verifica la información.');
                return;
            }

            $row = $rows[0];

            // 3) Actualización en BD principal dentro de transacción
            DB::transaction(function () use ($row) {
                /** @var \App\Models\Pedido $objPedido */
                $objPedido = Pedido::with('direccionEnvio')->findOrFail($this->pedidoIdParaNit);

                // Encabezado del pedido
                $objPedido->nit            = $row->f200_nit ?? $objPedido->nit;
                $objPedido->razon_social   = $row->f200_razon_social ?? $objPedido->razon_social;
                $objPedido->id_sucursal    = $row->f201_id_sucursal ?? $objPedido->id_sucursal;
                $objPedido->lista_precio   = $row->f201_id_lista_precio ?? $objPedido->lista_precio;
                $objPedido->condicion_pago = $row->f201_id_cond_pago ?? $objPedido->condicion_pago;

                // Dirección de envío (crear o actualizar)
                $direccionData = [
                    'id_punto_envio'     => $row->punto_envio_id ?? null,
                    'direccion'          => $row->f015_direccion1 ?? '',
                    'ciudad'             => $row->f013_descripcion ?? '',
                    'departamento'       => $row->f012_descripcion ?? '',
                    'codigo_ciudad'      => $row->f015_id_ciudad ?? '',
                    'codigo_departamento' => $row->f015_id_depto ?? '',
                ];

                if ($objPedido->relationLoaded('direccionEnvio') && $objPedido->direccionEnvio) {
                    $objPedido->direccionEnvio->fill($direccionData)->save();
                } else {
                    // Asumiendo relación: $objPedido->direccionEnvio()
                    $objPedido->direccionEnvio()->create($direccionData);
                }

                $objPedido->save();
            });

            // 4) Éxito
            session()->flash('success', 'Cliente actualizado en el pedido correctamente.');
            // Cierra el modal en Livewire
            $this->dispatch('cerrarModal');
            // Si necesitas refrescar una tabla/lista:
            // $this->dispatch('refrescarTabla');

        } catch (\Throwable $e) {
            report($e);
            session()->flash('warning', 'No se pudo validar/actualizar el cliente. Verifica la conexión con SQL Server y vuelve a intentar.');
        }
    }

    public function guardarNota()
    {

        $pedido = Pedido::find($this->notaId);

        if ($pedido) {
            $pedido->observaciones = $this->observacion;
            $pedido->save();

            session()->flash('success', 'Nota actualizada correctamente.');
            $this->dispatch('cerrarModal');
        }

        return redirect()->route('pedidos.index');
    }
    public function editarEstado($id)
    {

        $pedido = Pedido::find($id);

        if (! $pedido) {
            session()->flash('error', 'Pedido no encontrado.');
            return;
        }

        $this->notaId = $pedido->id;
        $this->nota = $pedido->nota;
    }

    public function guardarEstado()
    {

        $pedido = Pedido::find($this->notaId);

        if ($pedido) {
            $pedido->nota = $this->nota;
            $pedido->save();

            session()->flash('success', 'Estado actualizada correctamente.');
            $this->dispatch('cerrarModal');
        }

        return redirect()->route('pedidos.index');
    }

    public function confirmarClienteCreado()
    {
        // 1) Validación de entrada
        $this->validate([
            'pedidoIdParaNit'   => 'required|integer|exists:pedidos,id',
            'nuevoNit'          => 'required|regex:/^[0-9]{5,15}$/',
            'nuevaSucursal'     => 'required',
            'nuevaListaPrecios' => 'required',
            'nuevoPuntoEnvio'   => 'required'
        ], [
            'nuevoNit.required'          => 'El NIT es obligatorio',
            'nuevoNit.regex'             => 'El NIT debe ser numérico (5 a 15 dígitos)',
            'nuevaSucursal.required'     => 'Selecciona la sucursal',
            'nuevaListaPrecios.required' => 'Selecciona la lista de precios',
            'nuevoPuntoEnvio.required'   => 'Selecciona el punto de envío',
        ]);

        try {
            // 2) Consulta segura en SQL Server (TOP 1) con parámetros enlazados
            $rows = DB::connection('sqlsrv')->select(
                "SELECT TOP 1
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
                ON t215.f215_rowid_tercero = t201.f201_rowid_tercero
               AND t215.f215_id_sucursal   = t201.f201_id_sucursal
            LEFT JOIN t015_mm_contactos t015
                ON t015.f015_rowid = t215.f215_rowid_contacto
            LEFT JOIN t012_mm_deptos t012
                ON t012.f012_id = t015.f015_id_depto AND t012.f012_id_pais = 169
            LEFT JOIN t013_mm_ciudades t013
                ON t013.f013_id = t015.f015_id_ciudad
               AND t013.f013_id_depto = t015.f015_id_depto
               AND t013.f013_id_pais  = 169
            LEFT JOIN t207_mm_criterios_clientes t207
                ON t207.f207_rowid_tercero = t201.f201_rowid_tercero
               AND t207.f207_id_sucursal   = t201.f201_id_sucursal
               AND t207.f207_id_cia        = t201.f201_id_cia
               AND t207.f207_id_plan_criterios = '005'
            LEFT JOIN t206_mm_criterios_mayores t206 
                ON t206.f206_id_plan = t207.f207_id_plan_criterios
               AND t206.f206_id_cia  = t207.f207_id_cia 
               AND t206.f206_id      = t207.f207_id_criterio_mayor
            WHERE t200.f200_ind_cliente = 1
              AND t200.f200_ind_estado = 1
              AND t200.f200_id_cia     = 3
              AND t201.f201_ind_estado_activo = 1
              AND t201.f201_id_cia     = 3
              AND t215.f215_ind_estado = 1
              AND t215.f215_id_cia     = 3
              AND t200.f200_nit              = ?
              AND t201.f201_id_sucursal      = ?
              AND t201.f201_id_lista_precio  = ?
              AND t215.f215_id               = ?
            GROUP BY
                t200.f200_rowid,
                t200.f200_nit,
                t200.f200_dv_nit,
                t201.f201_id_sucursal,
                t215.f215_id",
                [
                    $this->nuevoNit,
                    (int) $this->nuevaSucursal,
                    (string) $this->nuevaListaPrecios,
                    (int) $this->nuevoPuntoEnvio,
                ]
            );

            if (count($rows) === 0) {
                // No se encontró combinación válida
                session()->flash('error', 'No se encontró el cliente con los criterios seleccionados (NIT, sucursal, lista de precios y punto de envío). Verifica la información.');
                return;
            }

            $row = $rows[0];

            
                    $payload = [
            'idCotizacion'          => $this->pedidoIdParaNit,
            'nuevoNit'          => $this->nuevoNit,
            'nuevaSucursal'     => (string) $this->nuevaSucursal,
            'nuevaListaPrecios' => (string) $this->nuevaListaPrecios,
            'nuevoPuntoEnvio'   => (string) $this->nuevoPuntoEnvio,
        ];

        // Varios destinatarios
        $destinatarios = [
            'sistemas@merlinrod.com',
            'auxsistemas@merlinrod.com',
            'auxcartera2@merlinrod.com',
            'auxcartera3@merlinrod.com',
            'auxcomercial@merlinrod.com',
            // agrega más si necesitas
        ];

        // Enviar (usa ->queue() si tienes colas)
        Mail::to($destinatarios)->send(new ConfirmacionCliente($payload));

            // 4) Éxito
            session()->flash('success', 'Confirmación enviada correctamente.');
            // Cierra el modal en Livewire
            $this->dispatch('cerrarModal');
            // Si necesitas refrescar una tabla/lista:
            // $this->dispatch('refrescarTabla');

        } catch (\Throwable $e) {
            report($e);
            session()->flash('warning', 'No se pudo validar/actualizar el cliente. Verifica la conexión con SQL Server y vuelve a intentar.');
        }
    }
}
