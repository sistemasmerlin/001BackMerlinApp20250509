<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SolicitudCredito;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
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

    // 1. Generar PDFs individuales
    $pdfSolicitud = Pdf::loadView('admin.solicitudes_credito.pdf.solicitud', [
        'solicitud' => $solicitud,
    ])->setPaper('letter', 'portrait')->output();

    $pdfTratamiento = Pdf::loadView('admin.solicitudes_credito.pdf.tratamiento-datos', [
        'solicitud' => $solicitud,
    ])->setPaper('letter', 'portrait')->output();

    // 2. Unir ambos PDFs
    $pdfUnificado = $this->unirPdfs([$pdfSolicitud, $pdfTratamiento]);

    // 3. Guardar PDF unificado
    $disk = 'public';
    $carpeta = 'solicitudes_credito/' . $solicitud->id;
    $nombreArchivo = 'solicitud_credito_' . $solicitud->id . '_unificado.pdf';
    $ruta = $carpeta . '/' . $nombreArchivo;

    Storage::disk($disk)->put($ruta, $pdfUnificado);

    // 4. Datos del firmante
    $nombreFirmante = $solicitud->representante_legal ?: $solicitud->autorizacion_nombre_1;
    $emailFirmante = $solicitud->correo_electronico ?: $solicitud->autorizacion_correo;
    $nitFirmante = $solicitud->nit_cc;

    if (!$nombreFirmante || !$emailFirmante) {
        $solicitud->update([
            'pdf_unificado_disk' => $disk,
            'pdf_unificado_path' => $ruta,
            'pdf_unificado_nombre' => $nombreArchivo,
            'auco_status' => 'pendiente_datos_firma',
        ]);

        return back()->with('error', 'Faltan datos del firmante.')
            ->with('auco_debug', [
                'nombreFirmante' => $nombreFirmante,
                'emailFirmante' => $emailFirmante,
            ]);
    }

    // 5. Payload igual al de la API
    $payload = [
        'name' => 'Solicitud de crédito - ' . ($solicitud->razon_social ?: 'Cliente'),
        'subject' => 'Firma solicitud de crédito',
        'message' => 'Por favor revisa y firma el documento adjunto.',
        'remember' => 3,
        'email' => config('services.auco.owner_email'),
        'signProfile' => [
            [
                'name' => $nombreFirmante,
                'email' => $emailFirmante,
                'label' => true,
                'camera' => true,
                'otpCode' => true,
                'identification' => $nitFirmante,
                'identificationType' => 'CC',
                'country' => 'CO',
                'options' => [
                    'camera' => 'identification',
                    'video' => true,
                    'whatsapp' => true,
                    'otpCode' => 'email',
                ],
            ]
        ],
        'readers' => [
            [
                'email' => $emailFirmante,
                'name' => $nombreFirmante,
            ]
        ],
        'file' => base64_encode($pdfUnificado),
    ];

    try {
        $response = Http::withHeaders([
            'Authorization' => config('services.auco.private_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(rtrim(config('services.auco.base_url'), '/') . '/document/upload', $payload);

        if (!$response->successful()) {
            $errorAuco = [
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body(),
                'payload' => [
                    'name' => $payload['name'],
                    'subject' => $payload['subject'],
                    'email' => $payload['email'],
                    'signProfile' => $payload['signProfile'],
                    'readers' => $payload['readers'],
                    'file' => '[base64 oculto]',
                ],
            ];

            $solicitud->update([
                'pdf_unificado_disk' => $disk,
                'pdf_unificado_path' => $ruta,
                'pdf_unificado_nombre' => $nombreArchivo,
                'auco_status' => 'error_envio',
                'auco_response' => $errorAuco,
            ]);

            return back()
                ->with('error', 'Falló el envío a firma.')
                ->with('auco_debug', $errorAuco);
        }

        $aucoData = $response->json();

        $solicitud->update([
            'pdf_unificado_disk' => $disk,
            'pdf_unificado_path' => $ruta,
            'pdf_unificado_nombre' => $nombreArchivo,
            'auco_code' => $aucoData['code'] ?? null,
            'auco_package' => $aucoData['package'] ?? null,
            'auco_status' => 'enviado_a_firma',
            'auco_response' => $aucoData,
            'estado' => 'en_firma',
        ]);

        return back()
            ->with('success', 'Solicitud enviada a firma correctamente.')
            ->with('auco_debug', [
                'status' => $response->status(),
                'body' => $aucoData,
            ]);

    } catch (\Throwable $e) {
        $error = [
            'exception' => $e->getMessage(),
            'payload' => [
                'name' => $payload['name'] ?? null,
                'subject' => $payload['subject'] ?? null,
                'email' => $payload['email'] ?? null,
                'signProfile' => $payload['signProfile'] ?? [],
                'readers' => $payload['readers'] ?? [],
                'file' => '[base64 oculto]',
            ],
        ];

        $solicitud->update([
            'pdf_unificado_disk' => $disk,
            'pdf_unificado_path' => $ruta,
            'pdf_unificado_nombre' => $nombreArchivo,
            'auco_status' => 'error_envio',
            'auco_response' => $error,
        ]);

        return back()
            ->with('error', 'Ocurrió un error al enviar a firma.')
            ->with('auco_debug', $error);
    }
}

private function unirPdfs(array $pdfBinaries): string
{
    $tempFiles = [];

    try {
        foreach ($pdfBinaries as $index => $binary) {
            $tmp = storage_path('app/temp_pdf_' . uniqid() . '_' . $index . '.pdf');
            file_put_contents($tmp, $binary);
            $tempFiles[] = $tmp;
        }

        $pdf = new \setasign\Fpdi\Fpdi();

        foreach ($tempFiles as $file) {
            $pageCount = $pdf->setSourceFile($file);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }

        return $pdf->Output('S');
    } finally {
        foreach ($tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }
}
}
