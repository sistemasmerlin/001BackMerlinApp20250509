<?php

namespace App\Exports;

use App\Models\PqrsProducto;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PqrsProductosExport implements FromCollection, WithHeadings
{
    public function __construct(
        public ?string $q = null,
        public ?string $asesor = null,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
    ) {}

    public function collection()
    {
        return PqrsProducto::query()
            ->with([
                'pqrs',
                'causal',
                'responsable',
                'submotivo.motivo',
                'adjuntos',
            ])
            ->whereHas('pqrs', function ($query) {
                $query
                    ->when($this->q, function ($q) {
                        $q->where(function ($qq) {
                            $qq->where('nit', 'like', "%{$this->q}%")
                               ->orWhere('razon_social', 'like', "%{$this->q}%");
                        });
                    })
                    ->when($this->asesor, function ($q) {
                        $q->where(function ($qq) {
                            $qq->where('cod_asesor', 'like', "%{$this->asesor}%")
                               ->orWhere('nombre_asesor', 'like', "%{$this->asesor}%");
                        });
                    })
                    ->when($this->fechaInicio, fn ($q) => $q->whereDate('fecha_creacion', '>=', $this->fechaInicio))
                    ->when($this->fechaFin, fn ($q) => $q->whereDate('fecha_creacion', '<=', $this->fechaFin));
            })
            ->orderByDesc('id')
            ->get()
            ->map(function ($producto) {
                $inicio = $producto->pqrs?->fecha_creacion ? Carbon::parse($producto->pqrs->fecha_creacion) : null;
                $revision = $producto->fecha_revision ? Carbon::parse($producto->fecha_revision) : null;

                return [
                    'PQRS ID' => $producto->pqrs_id,
                    'Producto ID' => $producto->id,
                    'NIT' => $producto->pqrs?->nit,
                    'Razón social' => $producto->pqrs?->razon_social,
                    'Asesor' => $producto->pqrs?->cod_asesor . ' - ' . $producto->pqrs?->nombre_asesor,
                    'Estado PQRS' => $producto->pqrs?->estado,
                    'Referencia' => $producto->referencia,
                    'Descripción' => $producto->descripcion_ref,
                    'Tipo docto' => $producto->tipo_docto,
                    'Nro docto' => $producto->nro_docto,
                    'Fecha factura' => optional($producto->fecha)->format('Y-m-d'),
                    'Unidades solicitadas' => $producto->unidades_solicitadas,
                    'Precio unitario' => $producto->precio_unitario,
                    'Valor bruto' => $producto->valor_bruto,
                    'Valor impuesto' => $producto->valor_imp,
                    'Valor neto' => $producto->valor_neto,
                    'Motivo' => $producto->submotivo?->motivo?->nombre,
                    'Submotivo' => $producto->submotivo?->nombre,
                    'Causal' => $producto->causal?->nombre,
                    'Responsable' => $producto->responsable?->nombre,
                    'Estado producto' => $producto->estado,
                    'Revisado por' => $producto->revisado_por,
                    'Fecha revisión producto' => optional($producto->fecha_revision)->format('Y-m-d H:i'),
                    'Tiempo hasta revisión horas' => $inicio && $revision ? $inicio->diffInHours($revision) : '',
                    'Comentario revisión' => $producto->comentario_revision,
                    'Requiere recogida' => $producto->requiere_recogida ? 'Sí' : 'No',
                    'Solicitud recogida' => $producto->solicitud_recogida,
                    'Estado ORM producto' => $producto->estado_orm,
                    'ORM revisada por' => $producto->orm_revisada_por,
                    'ORM fecha revisión' => optional($producto->orm_fecha_revision)->format('Y-m-d H:i'),
                    'ORM comentario' => $producto->orm_comentario_revision,
                    'Notas' => $producto->notas,
                    'Cantidad adjuntos' => $producto->adjuntos->count(),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'PQRS ID',
            'Producto ID',
            'NIT',
            'Razón social',
            'Asesor',
            'Estado PQRS',
            'Referencia',
            'Descripción',
            'Tipo docto',
            'Nro docto',
            'Fecha factura',
            'Unidades solicitadas',
            'Precio unitario',
            'Valor bruto',
            'Valor impuesto',
            'Valor neto',
            'Motivo',
            'Submotivo',
            'Causal',
            'Responsable',
            'Estado producto',
            'Revisado por',
            'Fecha revisión producto',
            'Tiempo hasta revisión horas',
            'Comentario revisión',
            'Requiere recogida',
            'Solicitud recogida',
            'Estado ORM producto',
            'ORM revisada por',
            'ORM fecha revisión',
            'ORM comentario',
            'Notas',
            'Cantidad adjuntos',
        ];
    }
}