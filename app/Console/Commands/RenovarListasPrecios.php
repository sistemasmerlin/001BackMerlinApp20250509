<?php

namespace App\Console\Commands;

use App\Jobs\GenerarListaPrecios;
use App\Jobs\LimpiarListasPreciosAnteriores;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class RenovarListasPrecios extends Command
{
    protected $signature = 'listas-precios:renovar {--usuario=1}';

    protected $description =
        'Genera las listas 001 y 202 con imágenes y limpia las anteriores';

    public function handle(): int
    {
        $usuario = User::find((int) $this->option('usuario'));

        if (
            !$usuario ||
            !$usuario->can('Administrar imagenes productos')
        ) {
            $this->error(
                'El usuario indicado no existe o no tiene permiso.'
            );

            return self::FAILURE;
        }

        // Evita que dos ejecuciones del comando creen la misma renovación.
        $lock = Cache::lock('listas-precios:programar-renovacion', 30);

        if (!$lock->get()) {
            $this->info('Otra ejecución está programando las listas.');

            return self::SUCCESS;
        }

        try {
            /*
             * No iniciar otra renovación mientras haya una lista
             * pendiente o procesándose, incluso si fue solicitada
             * manualmente desde la vista.
             */
            $ocupado = DB::table('listas_precios_exportaciones')
                ->whereIn('estado', ['pendiente', 'procesando'])
                ->exists();

            if ($ocupado) {
                $this->info(
                    'Hay una exportación pendiente o en proceso. '
                    . 'Se omite este horario.'
                );

                return self::SUCCESS;
            }

            $ids = DB::transaction(function () use ($usuario) {
                $ids = [];

                foreach (['001', '202'] as $lista) {
                    $ids[] = DB::table('listas_precios_exportaciones')
                        ->insertGetId([
                            'user_id' => $usuario->getKey(),
                            'lista' => $lista,
                            'imagenes' => true,
                            'estado' => 'pendiente',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                return $ids;
            });

            try {
                Bus::chain([
                    new GenerarListaPrecios($ids[0]),
                    new GenerarListaPrecios($ids[1]),
                    new LimpiarListasPreciosAnteriores($ids),
                ])
                    ->onConnection('exportaciones')
                    ->onQueue('listas-precios')
                    ->catch(function (Throwable $exception) use ($ids) {
                        /*
                         * Si falla la primera, la segunda no se ejecuta.
                         * Marcarla como error evita dejarla en cola
                         * indefinidamente.
                         */
                        DB::table('listas_precios_exportaciones')
                            ->whereIn('id', $ids)
                            ->whereIn(
                                'estado',
                                ['pendiente', 'procesando']
                            )
                            ->update([
                                'estado' => 'error',
                                'mensaje' =>
                                    'La renovación automática no se completó. '
                                    . 'Se conservaron las listas anteriores.',
                                'updated_at' => now(),
                            ]);

                        report($exception);
                    })
                    ->dispatch();
            } catch (Throwable $exception) {
                DB::table('listas_precios_exportaciones')
                    ->whereIn('id', $ids)
                    ->where('estado', 'pendiente')
                    ->update([
                        'estado' => 'error',
                        'mensaje' =>
                            'No se pudo iniciar la renovación automática.',
                        'updated_at' => now(),
                    ]);

                throw $exception;
            }

            $this->info(
                "Renovación encolada: 001 #{$ids[0]} y 202 #{$ids[1]}."
            );

            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}