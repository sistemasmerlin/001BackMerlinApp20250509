<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SolicitudCredito;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
class SolicitudCreditoController extends Controller
{
    public function verPdfUnificado(SolicitudCredito $solicitud)
{
    if (!$solicitud->pdf_unificado_disk || !$solicitud->pdf_unificado_path) {
        abort(404, 'La solicitud no tiene PDF unificado generado.');
    }

    if (!Storage::disk($solicitud->pdf_unificado_disk)->exists($solicitud->pdf_unificado_path)) {
        abort(404, 'El archivo PDF no existe en el servidor.');
    }

    $absolutePath = Storage::disk($solicitud->pdf_unificado_disk)->path($solicitud->pdf_unificado_path);

    return response()->file($absolutePath);
}

public function descargarPdfUnificado(SolicitudCredito $solicitud)
{
    if (!$solicitud->pdf_unificado_disk || !$solicitud->pdf_unificado_path) {
        abort(404, 'La solicitud no tiene PDF unificado generado.');
    }

    if (!Storage::disk($solicitud->pdf_unificado_disk)->exists($solicitud->pdf_unificado_path)) {
        abort(404, 'El archivo PDF no existe en el servidor.');
    }

    return Storage::disk($solicitud->pdf_unificado_disk)->download(
        $solicitud->pdf_unificado_path,
        $solicitud->pdf_unificado_nombre ?: ('solicitud_credito_' . $solicitud->id . '.pdf')
    );
}
    public function pdfSolicitud(SolicitudCredito $solicitud)
    {
        $solicitud->load(['referencias', 'direcciones']);

        $pdf = Pdf::loadView('admin.solicitudes_credito.pdf.solicitud', [
            'solicitud' => $solicitud,
        ])->setPaper('letter', 'portrait');

        $nombre = 'solicitud_credito_' . ($solicitud->id) . '.pdf';

        return $pdf->stream($nombre);
    }

    public function pdfTratamientoDatos(SolicitudCredito $solicitud)
    {
        $solicitud->load(['referencias', 'direcciones']);

        $pdf = Pdf::loadView('admin.solicitudes_credito.pdf.tratamiento-datos', [
            'solicitud' => $solicitud,
        ])->setPaper('letter', 'portrait');

        $nombre = 'tratamiento_datos_' . ($solicitud->id) . '.pdf';

        return $pdf->stream($nombre);
    }


    private function normalizarTelefono(?string $telefono): ?string
{
    if (!$telefono) return null;

    $telefono = trim($telefono);

    if (Str::startsWith($telefono, '+')) {
        return $telefono;
    }

    $digits = preg_replace('/\D+/', '', $telefono);

    return $digits ? '+57' . $digits : null;
}

public function enviarSolicitudAFirma(SolicitudCredito $solicitud)
{
    $solicitud->load(['referencias', 'direcciones']);

    // 1. Generar PDF
    $pdf = Pdf::loadView('admin.solicitudes_credito.pdf.solicitud', [
        'solicitud' => $solicitud,
    ])->setPaper('letter', 'portrait')->output();

    $base64 = base64_encode($pdf);

    // 2. Datos del firmante
    $nombre = $solicitud->representante_legal ?: $solicitud->autorizacion_nombre_1;
    $email = $solicitud->correo_electronico ?: $solicitud->autorizacion_correo;
    $telefono = $this->normalizarTelefono($solicitud->celular);

    if (!$nombre || !$email) {
        return back()->with('error', 'Faltan datos del firmante.');
    }

    // 3. Payload AUCO

    
    $payload = [
    "name" => "Solicitud de crédito - " . $solicitud->razon_social,
    "subject" => "Firma solicitud de crédito",
    "message" => "Por favor revisa y firma la solicitud.",
    "remember" => 3,
    "email" => $email,
    "signProfile" => [
        [
            "name" => $nombre,
            "email" => $email,
            "label" => true,
            "camera" => true
        ]
    ],
    "readers" => [
        [
            "email" => $email,
            "name" => "Seguimiento"
        ]
    ],
    "file" => $base64
];

    try {
        $response = Http::withHeaders([
            'Authorization' => config('services.auco.private_key'),
            'Content-Type' => 'application/json',
        ])->post(config('services.auco.base_url') . '/document/upload', $payload);

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        $data = $response->json();

        // Opcional guardar trazabilidad
        // $solicitud->update([
        //     'estado' => 'en_firma',
        //     'auco_code' => $data['code'] ?? null,
        // ]);

        return back()->with('success', 'Solicitud enviada a firma correctamente');

    } catch (\Throwable $e) {
        return back()->with('error', $e->getMessage());
    }
}
}
