<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FacturasController extends Controller
{
    public function descargar($prefijo, $consecutivo)
    {
        //dd("Descargando factura $prefijo $consecutivo");
        $data = $this->consultarFacturaNumrot($prefijo, $consecutivo);

        // Busca la URL real del PDF
        $urlPdf = $this->extraerUrlPdf($data);

        if ($urlPdf) {
            return redirect()->away($urlPdf);
        }

        // Si no hay URL, intenta abrir base64
        $base64 = $this->extraerBase64($data);

        if ($base64) {
            $binary = base64_decode($base64, true);

            if ($binary === false) {
                abort(500, 'El PDF llegó en base64 inválido.');
            }

            return response($binary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$prefijo.$consecutivo.'.pdf"',
            ]);
        }

        // Temporal: para inspeccionar la respuesta real si no encuentra nada
        //dd($data);
    }

    private function consultarFacturaNumrot($prefijo, $consecutivo): array
{
    if ($prefijo === 'CNC') {

        $body = [
            "Key" => "12177dc3ec45485eada8014a6d1d32ca",
            "Secret" => "0abcfb0be01c27edc595ad962c1af3d4",
            "Filters" => [
                "DocumentoTipoEstandar" => "nota",
                "EmpresaNit" => "9013683375",
                "DocumentoNumeroCompleto" => $consecutivo,
            ]
        ];

    } else {

        // Numrot maneja FEDQ en lugar de FVM
        $prefijoConsulta = $prefijo === 'FVM'
            ? 'FEDQ'
            : $prefijo;

        $body = [
            "Key" => "12177dc3ec45485eada8014a6d1d32ca",
            "Secret" => "0abcfb0be01c27edc595ad962c1af3d4",
            "Filters" => [
                "DocumentoTipoEstandar" => "facturadeventa",
                "EmpresaNit" => "9013683375",
                "DocumentoNumeroCompleto" => $prefijoConsulta . $consecutivo,
            ]
        ];
    }

    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
    ])->post(
        'https://www.numrot.net/NRWApi/api/Documents/Find',
        $body
    );

    //dd($body, $response->status(), $response->body());

    if (!$response->successful()) {
        abort(
            500,
            'Error consultando Numrot. Código: ' .
            $response->status() .
            ' Respuesta: ' .
            $response->body()
        );
    }

    $json = $response->json();

    if (!is_array($json)) {
        abort(500, 'Numrot devolvió una respuesta inválida.');
    }

    return $json;
}


    // private function consultarFacturaNumrot($prefijo, $consecutivo): array
    // {
    //     $key = config('services.numrot.key');
    //     $secret = config('services.numrot.secret');
    //     $empresaNit = config('services.numrot.empresa_nit');
    //     $url = config('services.numrot.url');
    //     if ($prefijo === 'CNC') {
    //         $body = [
    //             "Key" => $key,
    //             "Secret" => $secret,
    //             "Filters" => [
    //                 "DocumentoTipoEstandar" => "nota",
    //                 "EmpresaNit" => $empresaNit,
    //                 "DocumentoNumeroCompleto" => $consecutivo,
    //             ]
    //         ];
    //     } else {
    //         $prefijo = 'FEDQ';
    //         $body = [
    //             "Key" => $key,
    //             "Secret" => $secret,
    //             "Filters" => [
    //                 "DocumentoTipoEstandar" => "facturadeventa",
    //                 "EmpresaNit" => $empresaNit,
    //                 "DocumentoNumeroCompleto" => $prefijo . $consecutivo,
    //             ]
    //         ];
    //     }


    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //     ])->post($url, $body);

    //   //  dd($key, $secret, $empresaNit, $url, $prefijo, $consecutivo, $body, $response->status(), $response->body());


    //     if (!$response->successful()) {
    //         abort(500, 'Error consultando Numrot');
    //     }

    //     return $response->json() ?? [];
    // }
    private function extraerUrlPdf(array $data): ?string
    {
        $candidatas = [
            data_get($data, 'UrlPdf'),
            data_get($data, 'PdfUrl'),
            data_get($data, 'DownloadUrl'),
            data_get($data, 'FileUrl'),
            data_get($data, 'DocumentUrl'),

            data_get($data, 'Data.UrlPdf'),
            data_get($data, 'Data.PdfUrl'),
            data_get($data, 'Data.DownloadUrl'),
            data_get($data, 'Data.FileUrl'),
            data_get($data, 'Data.DocumentUrl'),

            data_get($data, 'Documents.0.UrlPdf'),
            data_get($data, 'Documents.0.PdfUrl'),
            data_get($data, 'Documents.0.DownloadUrl'),
            data_get($data, 'Documents.0.FileUrl'),
            data_get($data, 'Documents.0.DocumentUrl'),

            data_get($data, 'Data.Documents.0.UrlPdf'),
            data_get($data, 'Data.Documents.0.PdfUrl'),
            data_get($data, 'Data.Documents.0.DownloadUrl'),
            data_get($data, 'Data.Documents.0.FileUrl'),
            data_get($data, 'Data.Documents.0.DocumentUrl'),
        ];

        foreach ($candidatas as $url) {
            if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        }

        return null;
    }

    private function extraerBase64(array $data): ?string
    {
        $candidatas = [
            data_get($data, 'PdfBase64'),
            data_get($data, 'Base64'),
            data_get($data, 'ArchivoBase64'),

            data_get($data, 'Data.PdfBase64'),
            data_get($data, 'Data.Base64'),
            data_get($data, 'Data.ArchivoBase64'),

            data_get($data, 'Documents.0.PdfBase64'),
            data_get($data, 'Documents.0.Base64'),
            data_get($data, 'Documents.0.ArchivoBase64'),
        ];

        foreach ($candidatas as $valor) {
            if (is_string($valor) && trim($valor) !== '') {
                return trim($valor);
            }
        }

        return null;
    }
}