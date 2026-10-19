<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LimpiarListasPreciosAnteriores implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;
    public int $tries = 1;
    public bool $failOnTimeout = true;

    public function __construct(public array $nuevasIds)
    {
        $this->onConnection('exportaciones');
        $this->onQueue('listas-precios');
    }

    public function handle(): void
    {
        $nuevas = DB::table('listas_precios_exportaciones')
            ->whereIn('id', $this->nuevasIds)
            ->get();

        if (
            $nuevas->count() !== 2 ||
            $nuevas->pluck('lista')->sort()->values()->all()
                !== ['001', '202']
        ) {
            throw new RuntimeException(
                'No se encontraron las dos listas de la renovación.'
            );
        }

        foreach ($nuevas as $exportacion) {
            if (
                $exportacion->estado !== 'listo' ||
                !$exportacion->imagenes ||
                !$exportacion->ruta ||
                !Storage::disk('local')->exists($exportacion->ruta)
            ) {
                throw new RuntimeException(
                    'Las dos listas nuevas deben estar listas. '
                    . 'No se eliminaron las anteriores.'
                );
            }
        }

        /*
         * Solo eliminar registros anteriores a esta renovación.
         * No tocar solicitudes nuevas ni trabajos pendientes.
         */
        $limite = min($this->nuevasIds);

        DB::table('listas_precios_exportaciones')
            ->where('id', '<', $limite)
            ->whereIn('lista', ['001', '202'])
            ->whereIn('estado', ['listo', 'error'])
            ->orderBy('id')
            ->chunkById(100, function ($anteriores) use ($nuevas) {
                foreach ($anteriores as $anterior) {
                    if ($anterior->ruta) {
                        // Solo gestionar archivos de esta exportación.
                        if (!str_starts_with(
                            $anterior->ruta,
                            'listas-precios/'
                        )) {
                            continue;
                        }

                        // No borrar una ruta utilizada por las listas nuevas.
                        if ($nuevas->contains('ruta', $anterior->ruta)) {
                            continue;
                        }

                        $disco = Storage::disk('local');

                        if (
                            $disco->exists($anterior->ruta) &&
                            !$disco->delete($anterior->ruta)
                        ) {
                            throw new RuntimeException(
                                'No se pudo eliminar el archivo anterior #'
                                . $anterior->id
                            );
                        }
                    }

                    DB::table('listas_precios_exportaciones')
                        ->where('id', $anterior->id)
                        ->whereIn('estado', ['listo', 'error'])
                        ->delete();
                }
            });
    }
}