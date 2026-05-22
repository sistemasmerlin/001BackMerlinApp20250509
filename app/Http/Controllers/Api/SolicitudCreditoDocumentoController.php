<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolicitudCredito;
use App\Models\SolicitudCreditoDocumento;
use App\Models\TipoDocumentoCredito;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Storage;

class SolicitudCreditoDocumentoController extends Controller
{
    public function index($solicitudId)
    {

        $solicitud = SolicitudCredito::with(['comentarios.usuario'])
        ->findOrFail($solicitudId);

        $cupoMayor25 = (float) $solicitud->cupo_sugerido > 25000000;

        $documentosFinancieros = [
            'DECLARACION DE RENTA',
            'ESTADO DE RESULTADOS',
            'BALANCE GENERAL',
        ];

        $tipos = TipoDocumentoCredito::where('estado', true)
            ->when(!$cupoMayor25, function ($query) use ($documentosFinancieros) {
                $query->whereNotIn('nombre', $documentosFinancieros);
            })
            ->orderBy('orden')
            ->get()
            ->map(function ($tipo) use ($solicitudId) {
                $documentos = SolicitudCreditoDocumento::where('solicitud_credito_id', $solicitudId)
                    ->where('tipo_documento_credito_id', $tipo->id)
                    ->latest()
                    ->get()
                    ->map(function ($doc) {
                        return [
                            'id' => $doc->id,
                            'nombre_original' => $doc->nombre_original,
                            'archivo' => $doc->archivo,
                            'estado' => $doc->estado,
                            'observacion' => $doc->observacion,
                            'created_at' => $doc->created_at,
                            'url' => Storage::disk($doc->disk)->url($doc->archivo),
                        ];
                    });

                return [
                    'id' => $tipo->id,
                    'nombre' => $tipo->nombre,
                    'descripcion' => $tipo->descripcion,
                    'cantidad_minima' => $tipo->cantidad_minima,
                    'cantidad_maxima' => $tipo->cantidad_maxima,
                    'multiple' => $tipo->multiple,
                    'obligatorio' => $tipo->obligatorio,
                    'documentos' => $documentos,
                ];
            });

        return response()->json([
            'success' => true,
            'solicitud' => $solicitud,
            'tipos_documentos' => $tipos,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'solicitud_credito_id' => 'required|exists:solicitudes_credito,id',
            'tipo_documento_credito_id' => 'required|exists:tipos_documentos_credito,id',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $tipo = TipoDocumentoCredito::findOrFail($request->tipo_documento_credito_id);

        $cantidadActual = SolicitudCreditoDocumento::where('solicitud_credito_id', $request->solicitud_credito_id)
            ->where('tipo_documento_credito_id', $request->tipo_documento_credito_id)
            ->where('estado', 'pendiente')
            ->count();

        if ($cantidadActual >= $tipo->cantidad_maxima) {
            return response()->json([
                'success' => false,
                'message' => 'Ya alcanzaste la cantidad máxima permitida para este documento.',
            ], 422);
        }

        $file = $request->file('archivo');

        $path = $file->store(
            'solicitudes_credito/documentos/' . $request->solicitud_credito_id,
            'public'
        );

        $documento = SolicitudCreditoDocumento::create([
            'solicitud_credito_id' => $request->solicitud_credito_id,
            'tipo_documento_credito_id' => $request->tipo_documento_credito_id,
            'nombre_original' => $file->getClientOriginalName(),
            'archivo' => $path,
            'disk' => 'public',
            'mime_type' => $file->getClientMimeType(),
            'peso' => $file->getSize(),
            'estado' => 'pendiente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Documento subido correctamente.',
            'documento' => $documento,
        ]);
    }

    public function destroy($documentoId)
    {
        $documento = SolicitudCreditoDocumento::findOrFail($documentoId);

        if ($documento->estado !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar documentos en estado pendiente.',
            ], 422);
        }

        if ($documento->archivo && Storage::disk($documento->disk)->exists($documento->archivo)) {
            Storage::disk($documento->disk)->delete($documento->archivo);
        }

        $documento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Documento eliminado correctamente.',
        ]);
    }
}