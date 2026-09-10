<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeliEvento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class MeliEventoController extends Controller
{
public function callback(Request $request): Response
    {
        try {
            $payload = $request->all();

            MeliEvento::create([
                'tipo'           => 'CALLBACK',
                'metodo'         => $request->method(),
                'url'            => $request->fullUrl(),
                'topic'          => null,
                'resource'       => null,
                'user_id'        => null,
                'application_id' => null,
                'query_params'   => $request->query(),
                'payload'        => $payload,
                'raw_body'       => $request->getContent(),
                'headers'        => $this->headersSeguros($request),
                'ip'             => $request->ip(),
                'estado'         => 'RECIBIDO',
            ]);

            return response(
                '<h2>Autorización recibida correctamente.</h2>
                <p>Ya puede cerrar esta ventana.</p>',
                200
            )->header('Content-Type', 'text/html; charset=UTF-8');

        } catch (Throwable $e) {
            Log::error('Error guardando callback de Mercado Libre', [
                'error' => $e->getMessage(),
            ]);

            return response(
                '<h2>No fue posible registrar la autorización.</h2>',
                500
            )->header('Content-Type', 'text/html; charset=UTF-8');
        }
    }

    /**
     * Recibe las notificaciones enviadas por Mercado Libre.
     *
     * URL:
     * POST /meli/webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            MeliEvento::create([
                'tipo'           => 'WEBHOOK',
                'metodo'         => $request->method(),
                'url'            => $request->fullUrl(),
                'topic'          => data_get($payload, 'topic'),
                'resource'       => data_get($payload, 'resource'),
                'user_id'        => data_get($payload, 'user_id'),
                'application_id' => data_get($payload, 'application_id'),
                'query_params'   => $request->query(),
                'payload'        => $payload,
                'raw_body'       => $request->getContent(),
                'headers'        => $this->headersSeguros($request),
                'ip'             => $request->ip(),
                'estado'         => 'RECIBIDO',
            ]);

            /*
             * Mercado Libre debe recibir una respuesta exitosa rápidamente.
             */
            return response()->json([
                'ok' => true,
            ], 200);

        } catch (Throwable $e) {
            Log::error('Error guardando webhook de Mercado Libre', [
                'error'   => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            /*
             * Respondemos 500 para permitir que Mercado Libre vuelva a intentar
             * enviar la notificación.
             */
            return response()->json([
                'ok'      => false,
                'message' => 'No fue posible guardar la notificación.',
            ], 500);
        }
    }

    /**
     * Evita almacenar cookies, sesiones o credenciales sensibles.
     */
    private function headersSeguros(Request $request): array
    {
        $headers = $request->headers->all();

        unset(
            $headers['authorization'],
            $headers['cookie'],
            $headers['x-csrf-token'],
            $headers['x-xsrf-token']
        );

        return $headers;
    }
}
