<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumentoCredito;

class TipoDocumentoCreditoSeeder extends Seeder
{
    public function run(): void
    {
        $documentos = [
            [
                'nombre' => 'RUT',
                'cantidad_minima' => 1,
                'cantidad_maxima' => 1,
                'multiple' => false,
                'orden' => 1,
            ],
            [
                'nombre' => 'CAMARA DE COMERCIO',
                'cantidad_minima' => 1,
                'cantidad_maxima' => 1,
                'multiple' => false,
                'orden' => 2,
            ],
            [
                'nombre' => 'CEDULA',
                'cantidad_minima' => 1,
                'cantidad_maxima' => 1,
                'multiple' => false,
                'orden' => 3,
            ],
            [
                'nombre' => 'REFERENCIAS COMERCIALES POR ESCRITO',
                'cantidad_minima' => 1,
                'cantidad_maxima' => 5,
                'multiple' => true,
                'orden' => 4,
            ],
            [
                'nombre' => 'FACTURAS',
                'cantidad_minima' => 1,
                'cantidad_maxima' => 3,
                'multiple' => true,
                'orden' => 5,
            ],
            [
                'nombre' => 'DECLARACION DE RENTA',
                'descripcion' => 'Requerido para cupo sugerido superior a 25 millones.',
                'cantidad_minima' => 1,
                'cantidad_maxima' => 1,
                'multiple' => false,
                'orden' => 6,
            ],
            [
                'nombre' => 'ESTADO DE RESULTADOS',
                'descripcion' => 'De los dos últimos años. Requerido para cupo sugerido superior a 25 millones.',
                'cantidad_minima' => 1,
                'cantidad_maxima' => 2,
                'multiple' => true,
                'orden' => 7,
            ],
            [
                'nombre' => 'BALANCE GENERAL',
                'descripcion' => 'De los dos últimos años. Requerido para cupo sugerido superior a 25 millones.',
                'cantidad_minima' => 1,
                'cantidad_maxima' => 2,
                'multiple' => true,
                'orden' => 8,
            ],
        ];
        
        foreach ($documentos as $doc) {
            TipoDocumentoCredito::create($doc);
        }
    }
}
