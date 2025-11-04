<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MotivosVisita;
use App\Models\ReporteVisita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $latitud = $request->ubicacion['lat'];
        $longitud = $request->ubicacion['lng'];

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        if (! $user->hasRole(['Asesor', 'Televentas', 'Coordinador Comercial', 'Comercial'])) {
            return response()->json(['message' => 'Acceso no autorizado. Solo asesores pueden iniciar sesión.'], 403);
        }    

        $expiration = Carbon::tomorrow()->setHour(5)->setMinute(0)->setSecond(0);

        // Crear el token
        $tokenResult = $user->createToken('api-token', ['*']);

        // Actualizar manualmente la fecha de expiración en la tabla
        $tokenResult->accessToken->expires_at = $expiration;
        $tokenResult->accessToken->save();



        $ciudad = $this->obtenerCiudadGoogle($latitud, $longitud);

        $reporte = ReporteVisita::create([
            'nit' => $user->codigo_asesor,
            'razon_social' => $user->name,
            'sucursal' => '000',
            'vendedor' => $user->codigo_asesor,
            'latitud' =>  $latitud,
            'longitud' => $longitud,
            'ciudad' => $ciudad,
            'notas' => 'Inicio de sesión',
        ]);

        $motivoVentaId = MotivosVisita::where('motivo', 'Inicio sesion')->value('id');

        $reporte->motivos()->attach([$motivoVentaId]);

        $relacionados = [];

        if ($user->hasRole(['Televentas', 'Coordinador Comercial'])) {
            $relacionados = $user->relacionados()
                ->select('users.id', 'users.name', 'users.email', 'users.codigo_asesor', 'users.cedula')
                ->get();
        }

        return response()->json([
            'token' => $tokenResult->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'codigo_asesor' => $user->codigo_asesor,
                'cedula' => $user->cedula,
                'relacionados' => $relacionados,
            ],
            'expires_at' => $expiration->toDateTimeString(),
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
