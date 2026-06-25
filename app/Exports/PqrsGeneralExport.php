<?php

namespace App\Exports;

use App\Models\Pqrs;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PqrsGeneralExport implements FromCollection, WithHeadings
{
    public function __construct(
        public ?string $q = null,
        public ?string $asesor = null,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
    ) {}

    public function collection()
    {
        return Pqrs::query()
            ->with(['orm.transportadora', 'productos'])
            ->when($this->q, function ($query) {
                $query->where(function ($q) {
                    $q->where('nit', 'like', "%{$this->q}%")
                      ->orWhere('razon_social', 'like', "%{$this->q}%");
                });
            })
            ->when($this->asesor, function ($query) {
                $query->where(function ($q) {
                    $q->where('cod_asesor', 'like', "%{$this->asesor}%")
                      ->orWhere('nombre_asesor', 'like', "%{$this->asesor}%");
                });
            })
            ->when($this->fechaInicio, fn ($q) => $q->whereDate('fecha_creacion', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn ($q) => $q->whereDate('fecha_creacion', '<=', $this->fechaFin))
            ->orderByDesc('fecha_creacion')
            ->get()
            ->map(function ($pqrs) {
                $inicio = $pqrs->fecha_creacion ? Carbon::parse($pqrs->fecha_creacion) : null;
                $fin = $pqrs->fecha_cierre ? Carbon::parse($pqrs->fecha_cierre) : now();

                return [
                    'ID' => $pqrs->id,
                    'NIT' => $pqrs->nit,
                    'Razón social' => $pqrs->razon_social,
                    'Ciudad' => $pqrs->ciudad,
                    'Dirección' => $pqrs->direccion,
                    'Asesor' => $pqrs->cod_asesor . ' - ' . $pqrs->nombre_asesor,
                    'Estado PQRS' => $pqrs->estado,
                    'Fecha creación' => optional($pqrs->fecha_creacion)->format('Y-m-d H:i'),
                    'Fecha revisado' => optional($pqrs->fecha_revisado)->format('Y-m-d H:i'),
                    'Fecha cierre' => optional($pqrs->fecha_cierre)->format('Y-m-d H:i'),
                    'Días transcurridos' => $inicio ? $inicio->diffInDays($fin) : '',
                    'Horas transcurridas' => $inicio ? $inicio->diffInHours($fin) : '',
                    'Tipo acuerdo' => $pqrs->tipo_acuerdo,
                    'Nota acuerdo' => $pqrs->nota_acuerdo,
                    'Valor acuerdo' => $pqrs->valor_acuerdo,
                    'Comentario cierre' => $pqrs->comentario_cierre,
                    'Número ORM' => $pqrs->numero_orm,
                    'Estado ORM' => $pqrs->orm?->estado,
                    'Transportadora' => $pqrs->orm?->transportadora?->nombre,
                    'Guía' => $pqrs->orm?->numero_guia,
                    'Fecha recogida programada' => optional($pqrs->orm?->fecha_recogida_programada)->format('Y-m-d'),
                    'Fecha recogida transportadora' => optional($pqrs->orm?->fecha_recogida_transportadora)->format('Y-m-d H:i'),
                    'Fecha llegada bodega' => optional($pqrs->orm?->fecha_llegada_bodega)->format('Y-m-d H:i'),
                    'Total productos' => $pqrs->productos->count(),
                    'Total valor neto' => $pqrs->productos->sum('valor_neto'),
                    'Enviado a otro usuario' => $pqrs->enviado_otro_usuario ? 'Sí' : 'No',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIT',
            'Razón social',
            'Ciudad',
            'Dirección',
            'Asesor',
            'Estado PQRS',
            'Fecha creación',
            'Fecha revisado',
            'Fecha cierre',
            'Días transcurridos',
            'Horas transcurridas',
            'Tipo acuerdo',
            'Nota acuerdo',
            'Valor acuerdo',
            'Comentario cierre',
            'Número ORM',
            'Estado ORM',
            'Transportadora',
            'Guía',
            'Fecha recogida programada',
            'Fecha recogida transportadora',
            'Fecha llegada bodega',
            'Total productos',
            'Total valor neto',
            'Enviado a otro usuario',
        ];
    }
}