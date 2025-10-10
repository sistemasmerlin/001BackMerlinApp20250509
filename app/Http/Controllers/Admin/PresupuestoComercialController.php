<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PresupuestoComercialController extends Controller
{
    public function plantilla()
    {
        $csv = implode("\n", [
            'periodo,codigo_asesor,tipo_presupuesto,presupuesto,marca,categoria,clasificacion_asesor',
            '202509,A001,valor,15000000,RINOVA,llantas,A',
            '202509,A001,unidades,120,RINOVA,llantas,A',
            '202509,A002,valor,8000000,,repuestos,B',
        ]);

        return response()->streamDownload(
            fn()=>print($csv),
            'plantilla_presupuestos.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
