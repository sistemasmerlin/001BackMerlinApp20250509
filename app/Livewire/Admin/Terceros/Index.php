<?php
namespace App\Livewire\Admin\Terceros;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public $idTercero;
    public $clientes = [];
    public $ciudades = [];
    public $modalSucursales = false;
    public $sucursalesSeleccionadas = [];

    public function buscar()
    {
        $result = DB::connection('sqlsrv')->select(
            "SELECT t200.f200_rowid AS tercero_id,
                    t200.f200_razon_social,
                    t200.f200_nit,
                    t201.f201_id_sucursal,
                    t201.f201_descripcion_sucursal,
                    t201.f201_cupo_credito,
                    t201.f201_id_cond_pago,
                    t201.f201_id_lista_precio,
                    t206.f206_descripcion,
                    ult_factura.f461_ts as ultima_factura,
                    ISNULL(pedidos.Pedidos, 0) as pedidos,
                    ISNULL(cartera.Cartera, 0) as cartera,
                    ISNULL(pago.Dias_Prom_pago, 0) as pago
            FROM t200_mm_terceros t200
            JOIN t201_mm_clientes t201 ON t200.f200_rowid = t201.f201_rowid_tercero
            LEFT JOIN t206_mm_criterios_mayores t206 ON t206.f206_id = 'CAT'
            OUTER APPLY (
                SELECT TOP 1 f461_ts
                FROM t461_cm_docto_factura_venta f
                WHERE f.f461_rowid_tercero_fact = t200.f200_rowid
                ORDER BY f461_ts DESC
            ) ult_factura
            LEFT JOIN (
                SELECT f430_rowid_tercero_fact, SUM(v431_vlr_neto_pen_local) AS Pedidos
                FROM t430_cm_pv_docto t430
                INNER JOIN v431 ON v431_rowid_pv_docto = f430_rowid
                GROUP BY f430_rowid_tercero_fact
            ) pedidos ON pedidos.f430_rowid_tercero_fact = t200.f200_rowid
            LEFT JOIN (
                SELECT f353_rowid_tercero, SUM(f353_total_db - f353_total_cr) AS Cartera
                FROM t353_co_saldo_abierto
                GROUP BY f353_rowid_tercero
            ) cartera ON cartera.f353_rowid_tercero = t200.f200_rowid
            LEFT JOIN (
                SELECT f353_rowid_tercero, AVG(DATEDIFF(DAY, f353_fecha, GETDATE())) AS Dias_Prom_pago
                FROM t353_co_saldo_abierto
                GROUP BY f353_rowid_tercero
            ) pago ON pago.f353_rowid_tercero = t200.f200_rowid
            WHERE t201.f201_id_vendedor = ?",
            [$this->idTercero]
        );
        
        /*WHERE t201.f201_id_vendedor = ?",*/
            /*WHERE t200.f200_nit = ?",*/

        $clientes = [];
        foreach ($result as $row) {
            $id = $row->tercero_id;
            if (!isset($clientes[$id])) {
                $clientes[$id] = [
                    'tercero_id' => $id,
                    'razon_social' => $row->f200_razon_social,
                    'nit' => $row->f200_nit,
                    'ultima_factura' => $row->ultima_factura,
                    'estado_ultima_venta' => $row->ultima_factura ? now()->diffInDays($row->ultima_factura) > 180 ? 'Venta vencida' : 'Venta corriente' : 'Sin datos',
                    'sucursales' => []
                ];
            }
            $clientes[$id]['sucursales'][] = [
                'descripcion_sucursal' => $row->f201_descripcion_sucursal,
                'cartera' => $row->cartera,
                'pedidos' => $row->pedidos,
                'cond_pago' => $row->f201_id_cond_pago,
                'cupo_credito' => $row->f201_cupo_credito,
                'lista_precio' => $row->f201_id_lista_precio,
                'categoria' => $row->f206_descripcion,
            ];
        }

        $this->clientes = array_values($clientes);
    }


    
    public function verSucursales($terceroId)
    {
        // Filtrar el array de clientes
        $cliente = array_filter($this->clientes, function ($cliente) use ($terceroId) {
            return $cliente['tercero_id'] == $terceroId;
        });
        
        if (!empty($cliente)) {
            $cliente = reset($cliente); // Obtener el primer elemento
            $this->sucursalesSeleccionadas = $cliente['sucursales'];
        } else {
            $this->sucursalesSeleccionadas = [];
        }
        
        $this->modalSucursales = true;
        \Log::debug("Buscando sucursales para: $terceroId"); // Verifica en laravel.log

        \Log::debug("Sucursales encontradas: " . print_r($this->sucursalesSeleccionadas, true));
    }

    public function render()
    {
        return view('livewire.admin.terceros.index');
    }
}
