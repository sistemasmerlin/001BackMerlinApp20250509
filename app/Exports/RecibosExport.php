<?php

namespace App\Exports;

use App\Exports\Sheets\CajaSheet;
use App\Exports\Sheets\CxcSheet;
use App\Exports\Sheets\OtrosIngresosSheet;
use App\Exports\Sheets\RetencionesSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RecibosExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        protected string $estado = 'APROBADO',
        protected ?array $reciboIds = null,
        protected ?string $usuarioAsignado = null,
    ) {
    }

    public function sheets(): array
    {
        return [
            new OtrosIngresosSheet(
                $this->estado,
                $this->reciboIds,
                $this->usuarioAsignado
            ),

            new CajaSheet(
                $this->estado,
                $this->reciboIds,
                $this->usuarioAsignado
            ),

            new CxcSheet(
                $this->estado,
                $this->reciboIds,
                $this->usuarioAsignado
            ),

            new RetencionesSheet(
                $this->estado,
                $this->reciboIds,
                $this->usuarioAsignado
            ),
        ];
    }
}