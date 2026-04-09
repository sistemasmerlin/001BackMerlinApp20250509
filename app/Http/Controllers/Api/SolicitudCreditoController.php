<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolicitudCredito;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Fpdi;

class SolicitudCreditoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'cod_depto' => ['nullable', 'string', 'max:10'],
            'depto' => ['nullable', 'string', 'max:120'],
            'cod_ciudad' => ['nullable', 'string', 'max:10'],
            'ciudad' => ['required', 'string', 'max:120'],
            'fecha_solicitud' => ['required', 'date'],

            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'nit_cc' => ['required', 'string', 'max:30'],
            'representante_legal' => ['nullable', 'string', 'max:255'],
            'identificacion_representante' => ['nullable', 'string', 'max:30'],
            'direccion_negocio' => ['nullable', 'string', 'max:255'],
            'barrio' => ['nullable', 'string', 'max:255'],
            'telefono_fijo' => ['nullable', 'string', 'max:30'],
            'celular' => ['required', 'string', 'max:30'],
            'correo_electronico' => ['required', 'email', 'max:255'],

            'contacto_compras' => ['nullable', 'string', 'max:255'],
            'telefono_compras' => ['nullable', 'string', 'max:30'],
            'correo_compras' => ['nullable', 'email', 'max:255'],

            'contacto_tesoreria' => ['nullable', 'string', 'max:255'],
            'telefono_tesoreria' => ['nullable', 'string', 'max:30'],
            'correo_tesoreria' => ['nullable', 'email', 'max:255'],

            'contacto_factura_electronica' => ['nullable', 'string', 'max:255'],
            'telefono_factura_electronica' => ['nullable', 'string', 'max:30'],
            'correo_factura_electronica' => ['nullable', 'email', 'max:255'],

            'rte_fuente' => ['nullable'],
            'rte_iva' => ['nullable'],
            'rte_ica' => ['nullable'],

            'antiguedad_comercial' => ['nullable', 'string', 'max:100'],
            'tiempo_antiguedad' => ['nullable', 'string', 'max:255'],
            'tipo_negocio' => 'nullable|array',
            'tipo_negocio.*' => 'string',
            'puntos_venta' => ['nullable', 'string', 'max:255'],
            'canal_tradicional' => ['nullable', 'string', 'max:255'],
            'canal_corporativo' => ['nullable', 'string', 'max:255'],
            'numero_empleados' => ['nullable', 'string', 'max:255'],

            'ventas_proyectadas_mes' => ['nullable', 'numeric', 'min:0'],
            'cupo_sugerido' => ['nullable', 'numeric', 'min:0'],

            'autorizacion_cod_depto' => ['nullable', 'string', 'max:10'],
            'autorizacion_depto' => ['nullable', 'string', 'max:120'],
            'autorizacion_cod_ciudad' => ['nullable', 'string', 'max:10'],
            'autorizacion_ciudad' => ['nullable', 'string', 'max:120'],
            'autorizacion_fecha' => ['nullable', 'date'],
            'autorizacion_nombre_1' => ['nullable', 'string', 'max:255'],
            'autorizacion_documento_1' => ['nullable', 'string', 'max:30'],
            'autorizacion_lugar_expedicion_1' => ['nullable', 'string', 'max:255'],
            'autorizacion_razon_social' => ['nullable', 'string', 'max:255'],
            'autorizacion_nit_cc' => ['nullable', 'string', 'max:30'],
            'autorizacion_nombre_2' => ['nullable', 'string', 'max:255'],
            'autorizacion_documento_2' => ['nullable', 'string', 'max:30'],
            'autorizacion_lugar_expedicion_2' => ['nullable', 'string', 'max:255'],
            'autorizacion_telefono_fijo' => ['nullable', 'string', 'max:30'],
            'autorizacion_celular' => ['nullable', 'string', 'max:30'],
            'autorizacion_correo' => ['nullable', 'email', 'max:255'],
            'autorizacion_direccion' => ['nullable', 'string', 'max:255'],

            'estado' => ['nullable', 'string', 'max:50'],

            'referencias_comerciales' => ['nullable', 'array', 'max:6'],
            'referencias_comerciales.*.empresa' => ['nullable', 'string', 'max:255'],
            'referencias_comerciales.*.nit' => ['nullable', 'string', 'max:30'],
            'referencias_comerciales.*.cod_depto' => ['nullable', 'string', 'max:10'],
            'referencias_comerciales.*.depto' => ['nullable', 'string', 'max:120'],
            'referencias_comerciales.*.cod_ciudad' => ['nullable', 'string', 'max:10'],
            'referencias_comerciales.*.ciudad' => ['nullable', 'string', 'max:120'],
            'referencias_comerciales.*.telefono' => ['nullable', 'string', 'max:30'],
            'referencias_comerciales.*.cupo_credito' => ['nullable', 'numeric', 'min:0'],

            'direcciones_adicionales' => ['nullable', 'array', 'max:3'],
            'direcciones_adicionales.*.contacto' => ['nullable', 'string', 'max:255'],
            'direcciones_adicionales.*.direccion' => ['nullable', 'string', 'max:255'],
            'direcciones_adicionales.*.cod_depto' => ['nullable', 'string', 'max:10'],
            'direcciones_adicionales.*.depto' => ['nullable', 'string', 'max:120'],
            'direcciones_adicionales.*.cod_ciudad' => ['nullable', 'string', 'max:10'],
            'direcciones_adicionales.*.ciudad' => ['nullable', 'string', 'max:120'],
            'direcciones_adicionales.*.telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $data['rte_fuente'] = $this->toBool($request->input('rte_fuente'));
        $data['rte_iva']    = $this->toBool($request->input('rte_iva'));
        $data['rte_ica']    = $this->toBool($request->input('rte_ica'));
        $data['estado']     = $data['estado'] ?? 'radicada';

        $referencias = collect($request->input('referencias_comerciales', []))
            ->map(fn ($item) => is_array($item) ? $item : [])
            ->filter(fn ($ref) => $this->referenciaCompleta($ref))
            ->values();

        if ($referencias->count() < 3) {
            throw ValidationException::withMessages([
                'referencias_comerciales' => ['Debes enviar mínimo 3 referencias comerciales completas.'],
            ]);
        }

        $direcciones = collect($request->input('direcciones_adicionales', []))
            ->map(fn ($item) => is_array($item) ? $item : [])
            ->filter(function ($dir) {
                return filled($dir['contacto'] ?? null)
                    || filled($dir['direccion'] ?? null)
                    || filled($dir['cod_depto'] ?? null)
                    || filled($dir['cod_ciudad'] ?? null)
                    || filled($dir['telefono'] ?? null);
            })
            ->values();

        $solicitud = DB::transaction(function () use ($data, $referencias, $direcciones) {
            unset($data['referencias_comerciales'], $data['direcciones_adicionales']);

            $solicitud = SolicitudCredito::create([
                ...$data,
                'user_id' => Auth::id(),
            ]);

            foreach ($referencias as $referencia) {
                $solicitud->referencias()->create([
                    'empresa' => $referencia['empresa'] ?? null,
                    'nit' => $referencia['nit'] ?? null,
                    'cod_depto' => $referencia['cod_depto'] ?? null,
                    'depto' => $referencia['depto'] ?? null,
                    'cod_ciudad' => $referencia['cod_ciudad'] ?? null,
                    'ciudad' => $referencia['ciudad'] ?? null,
                    'telefono' => $referencia['telefono'] ?? null,
                    'cupo_credito' => $referencia['cupo_credito'] ?? null,
                ]);
            }

            foreach ($direcciones as $direccion) {
                $solicitud->direcciones()->create([
                    'contacto' => $direccion['contacto'] ?? null,
                    'direccion' => $direccion['direccion'] ?? null,
                    'cod_depto' => $direccion['cod_depto'] ?? null,
                    'depto' => $direccion['depto'] ?? null,
                    'cod_ciudad' => $direccion['cod_ciudad'] ?? null,
                    'ciudad' => $direccion['ciudad'] ?? null,
                    'telefono' => $direccion['telefono'] ?? null,
                ]);
            }

            return $solicitud->load(['referencias', 'direcciones']);
        });

        // 1. generar PDFs individuales
        $pdfSolicitud = Pdf::loadView('admin.solicitudes_credito.pdf.solicitud', [
            'solicitud' => $solicitud,
        ])->setPaper('letter', 'portrait')->output();

        $pdfTratamiento = Pdf::loadView('admin.solicitudes_credito.pdf.tratamiento-datos', [
            'solicitud' => $solicitud,
        ])->setPaper('letter', 'portrait')->output();

        // 2. unir ambos en un solo PDF
        $pdfUnificado = $this->unirPdfs([$pdfSolicitud, $pdfTratamiento]);

        // 3. guardar en storage
        $disk = 'public';
        $carpeta = 'solicitudes_credito/' . $solicitud->id;
        $nombreArchivo = 'solicitud_credito_' . $solicitud->id . '_unificado.pdf';
        $ruta = $carpeta . '/' . $nombreArchivo;

        Storage::disk($disk)->put($ruta, $pdfUnificado);

        // 4. enviar a firmar
        $nombreFirmante = $solicitud->representante_legal ?: $solicitud->autorizacion_nombre_1;
        $emailFirmante = $solicitud->correo_electronico ?: $solicitud->autorizacion_correo;

        if (!$nombreFirmante || !$emailFirmante) {
            $solicitud->update([
                'pdf_unificado_disk' => $disk,
                'pdf_unificado_path' => $ruta,
                'pdf_unificado_nombre' => $nombreArchivo,
                'auco_status' => 'pendiente_datos_firma',
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Solicitud guardada y PDF generado, pero no fue posible enviarlo a firma porque faltan datos del firmante.',
                'data' => $solicitud->fresh(['referencias', 'direcciones']),
            ], 201);
        }

        $payloadAuco = [
            'name' => 'Solicitud de crédito - ' . ($solicitud->razon_social ?: 'Cliente'),
            'subject' => 'Firma solicitud de crédito',
            'message' => 'Por favor revisa y firma el documento adjunto.',
            'remember' => 3,
            'email' => $emailFirmante,
            'signProfile' => [
                [
                    'name' => $nombreFirmante,
                    'email' => $emailFirmante,
                    'label' => true,
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
            ])->post(rtrim(config('services.auco.base_url'), '/') . '/document/upload', $payloadAuco);
if (!$response->successful()) {
    $errorAuco = [
        'status' => $response->status(),
        'body' => $response->json() ?: $response->body(),
    ];

    $solicitud->update([
        'pdf_unificado_disk' => $disk,
        'pdf_unificado_path' => $ruta,
        'pdf_unificado_nombre' => $nombreArchivo,
        'auco_status' => 'error_envio',
        'auco_response' => $errorAuco,
    ]);

    return response()->json([
        'ok' => false,
        'message' => 'Solicitud guardada y PDF generado, pero falló el envío a firma.',
        'error_auco' => $errorAuco,
        'data' => $solicitud->fresh(['referencias', 'direcciones']),
    ], 201);
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

        } catch (\Throwable $e) {
            $solicitud->update([
                'pdf_unificado_disk' => $disk,
                'pdf_unificado_path' => $ruta,
                'pdf_unificado_nombre' => $nombreArchivo,
                'auco_status' => 'error_envio',
                'auco_response' => [
                    'exception' => $e->getMessage(),
                ],
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Solicitud guardada y PDF generado, pero ocurrió un error al enviarlo a firma.',
                'error' => $e->getMessage(),
                'data' => $solicitud->fresh(['referencias', 'direcciones']),
            ], 201);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Solicitud guardada, PDF generado, almacenado y enviado a firma correctamente.',
            'data' => $solicitud->fresh(['referencias', 'direcciones']),
        ], 201);
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

            $pdf = new Fpdi();

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

    private function referenciaCompleta(array $ref): bool
    {
        return filled($ref['empresa'] ?? null)
            && filled($ref['nit'] ?? null)
            && filled($ref['cod_depto'] ?? null)
            && filled($ref['depto'] ?? null)
            && filled($ref['cod_ciudad'] ?? null)
            && filled($ref['ciudad'] ?? null)
            && filled($ref['telefono'] ?? null)
            && array_key_exists('cupo_credito', $ref)
            && $ref['cupo_credito'] !== null
            && $ref['cupo_credito'] !== '';
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtoupper(trim((string) $value));

        return in_array($value, ['1', 'SI', 'SÍ', 'TRUE', 'YES'], true);
    }
}