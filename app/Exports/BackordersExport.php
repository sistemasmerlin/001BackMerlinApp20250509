<?php

namespace App\Exports;

use App\Models\Backorder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class BackordersExport implements FromCollection, WithHeadings
{
    public $inicio;
    public $fin;

    public function __construct($inicio = null, $fin = null)
    {
        $this->inicio = $inicio;
        $this->fin = $fin;
    }

    public function collection()
    {
        $query = Backorder::with('pedido', 'detalles');

        if ($this->inicio) {
            $query->whereDate('created_at', '>=', $this->inicio);
        }

        if ($this->fin) {
            $query->whereDate('created_at', '<=', $this->fin);
        }

        $backorders = $query->get();

        $resultado = collect();

        foreach ($backorders as $b) {

            // Fila de encabezado
            $resultado->push([
                'ID' => $b->id,
                'NIT' => $b->pedido->nit ?? '',
                'Razón Social' => $b->pedido->razon_social ?? '',
                'Fecha' => $b->created_at->format('Y-m-d'),
                'Estado' => $b->estado_backorder,
                'Referencia' => '',
                'Descripción' => '',
                'Cantidad' => '',
                'Descuento (%)' => '',
                'Precio Unitario' => '',
                'Subtotal' => ''
            ]);

            // Fila por cada detalle
            foreach ($b->detalles as $detalle) {
                $resultado->push([
                    'ID' => '',
                    'NIT' => '',
                    'Razón Social' => '',
                    'Fecha' => '',
                    'Estado' => '',
                    'Referencia' => $detalle->referencia,
                    'Descripción' => $detalle->descripcion,
                    'Cantidad' => $detalle->cantidad,
                    'Descuento (%)' => $detalle->descuento,
                    'Precio Unitario' => number_format($detalle->precio_unitario, 0, ',', '.'),
                    'Subtotal' => number_format($detalle->subtotal, 0, ',', '.')
                ]);
            }
        }

        return $resultado;
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIT',
            'Razón Social',
            'Fecha',
            'Estado',
            'Referencia',
            'Descripción',
            'Cantidad',
            'Descuento (%)',
            'Precio Unitario',
            'Subtotal'
        ];
    }
}
