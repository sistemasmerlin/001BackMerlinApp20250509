<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Support\Carbon;
use App\Models\User;

class AuthController extends Controller
{

    public function login(Request $request)
    {
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
}
