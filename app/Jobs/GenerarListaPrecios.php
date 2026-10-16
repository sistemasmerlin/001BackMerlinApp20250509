<?php

namespace App\Jobs;

use App\Exports\ListaPreciosExport;
use App\Http\Controllers\Admin\ProductosController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class GenerarListaPrecios implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;
    public int $tries = 1;
    public bool $failOnTimeout = true;

    public function __construct(public int $exportacionId)
    {
        $this->onConnection('exportaciones');
        $this->onQueue('listas-precios');
    }

    public function handle(): void
    {
        $exportacion = DB::table('listas_precios_exportaciones')
            ->where('id', $this->exportacionId)
            ->first();

        if (!$exportacion || $exportacion->estado !== 'pendiente') {
            return;
        }

        DB::table('listas_precios_exportaciones')
            ->where('id', $this->exportacionId)
            ->update([
                'estado' => 'procesando',
                'mensaje' => null,
                'updated_at' => now(),
            ]);

        /*
         * No llamamos exportarListaPrecios(), porque requiere
         * usuario autenticado. Solo reutilizamos index(),
         * que contiene la consulta SQL que usted compartió.
         */
        $respuesta = app(ProductosController::class)->index(
            Request::create('/productos', 'GET')
        );

        $contenido = $respuesta->getData(true);

        $sufijo = $exportacion->lista === '001' ? '1' : '202';
        $campoPrecio = "precio_{$sufijo}";
        $campoTotal = "precio_{$sufijo}_mas_iva";

        $productos = collect($contenido['productos'] ?? [])
            ->filter(function (array $producto) use (
                $campoPrecio,
                $campoTotal
            ) {
                return is_numeric($producto['disponible'] ?? null)
                    && (float) $producto['disponible'] > 0
                    && is_numeric($producto[$campoPrecio] ?? null)
                    && is_numeric($producto[$campoTotal] ?? null)
                    && (float) $producto[$campoPrecio] >= 0
                    && (float) $producto[$campoTotal]
                        >= (float) $producto[$campoPrecio];
            })
            ->map(function (array $producto) use (
                $campoPrecio,
                $campoTotal
            ) {
                return [
                    'referencia' => trim(
                        (string) ($producto['referencia'] ?? '')
                    ),
                    'descripcion' => trim(
                        (string) ($producto['descripcion'] ?? '')
                    ),
                    'categoria' => trim(
                        (string) ($producto['categoria_excel'] ?? '')
                    ) ?: 'SIN CATEGORÍA',
                    'marca' => trim(
                        (string) ($producto['marca_excel'] ?? '')
                    ) ?: 'SIN MARCA',
                    'precio' => (float) $producto[$campoPrecio],
                    'precio_iva' => (float) $producto[$campoTotal],
                ];
            })
            ->filter(fn (array $producto) =>
                $producto['referencia'] !== ''
            )
            ->values()
            ->all();

        if (!$productos) {
            throw new \RuntimeException(
                'No hay productos disponibles con precios válidos.'
            );
        }

        $ruta = 'listas-precios/'
            . $exportacion->user_id . '/'
            . $exportacion->id . '.xlsx';

        $guardado = Excel::store(
            new ListaPreciosExport(
                $productos,
                $exportacion->lista,
                (bool) $exportacion->imagenes
            ),
            $ruta,
            'local',
            \Maatwebsite\Excel\Excel::XLSX
        );

        if (!$guardado || !Storage::disk('local')->exists($ruta)) {
            throw new \RuntimeException(
                'No se pudo guardar el archivo generado.'
            );
        }

        DB::table('listas_precios_exportaciones')
            ->where('id', $this->exportacionId)
            ->update([
                'estado' => 'listo',
                'ruta' => $ruta,
                'mensaje' => null,
                'updated_at' => now(),
            ]);
    }

    public function failed(?Throwable $exception): void
    {
        DB::table('listas_precios_exportaciones')
            ->where('id', $this->exportacionId)
            ->update([
                'estado' => 'error',
                'mensaje' =>
                    'No se pudo generar la lista. Revise el log del servidor.',
                'updated_at' => now(),
            ]);

        if ($exception) {
            report($exception);
        }
    }
}