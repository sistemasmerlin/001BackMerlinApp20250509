<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MotivosVisita;
use App\Models\ReporteVisita;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class MotivosVisitaController extends Controller
{
    public function index(){

        $motivos = MotivosVisita::where('estado', '=', 1)->get();

        return response()->json([
            'motivos' => $motivos,
        ]); 
    }



public function store(Request $request)
{
    // Obtener los datos del JSON
    $data = $request->json()->all();

    // Validación manual (porque estás usando $data directamente)
    $validator = Validator::make($data, [
        'nit' => 'required',
        'razon_social' => 'required',
        'sucursal' => 'required',
        'vendedor' => 'required',
        'latitud' => 'required|numeric|not_in:0',
        'longitud' => 'required|numeric|not_in:0',
        'ciudad' => 'required',
        'notas' => 'nullable|string',
        'motivos' => 'required|array',
        'motivos.*' => 'exists:motivos_visitas,id',
    ]);

    // Si falla la validación
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Validación exitosa
    $validated = $validator->validated();

    $ciudad = $this->obtenerCiudadGoogle($validated['latitud'], $validated['longitud']);

    // Crear el reporte
    $reporte = ReporteVisita::create([
        'nit' => $validated['nit'],
        'razon_social' => $validated['razon_social'],
        'sucursal' => $validated['sucursal'],
        'vendedor' => $validated['vendedor'],
        'latitud' => $validated['latitud'],
        'longitud' => $validated['longitud'],
        'ciudad' => $ciudad,
        'notas' => $validated['notas'] ?? '',
    ]);

    // Relacionar motivos
    $reporte->motivos()->attach($validated['motivos']);

    // Retornar respuesta
    return response()->json([
        'success' => true,
        'mensaje' => 'Reporte de visita creado',
        'reporte' => $reporte->load('motivos'),
    ]);
}

    public function obtenerCiudadGoogle($latitud, $longitud)
    {
        $apiKey = config('services.google_maps.key'); // o colócala directamente
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitud},{$longitud}&key={$apiKey}";

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['results'][0])) {
                return $data['results'][0]['formatted_address'];
            }
        }

        return 'Desconocida';
    }


}
