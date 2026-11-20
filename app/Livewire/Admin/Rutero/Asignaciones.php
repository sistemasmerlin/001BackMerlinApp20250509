<?php

namespace App\Livewire\Admin\Rutero;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Asignaciones extends Component
{
    private const ROL_LIDER = 'Líder comercial';
    private const ROL_ASESOR = 'Asesor';
    private const PERMISO = 'Administrar reporte visitas';

    public string $liderId = '';
    public string $asesorId = '';
    public string $mensaje = '';

    public function updatedLiderId(): void
    {
        $this->reset('asesorId', 'mensaje');
        $this->resetValidation();
    }

    public function asignar(): void
    {
        Gate::authorize(self::PERMISO);

        $this->reset('mensaje');

        $datos = $this->validate([
            'liderId' => ['required', 'integer', 'min:1'],
            'asesorId' => ['required', 'integer', 'min:1', 'different:liderId'],
        ], [], [
            'liderId' => 'líder de zona',
            'asesorId' => 'asesor',
        ]);

        if (! User::role(self::ROL_LIDER)->whereKey($datos['liderId'])->exists()) {
            throw ValidationException::withMessages([
                'liderId' => 'Seleccione un líder de zona válido.',
            ]);
        }

        if (! User::role(self::ROL_ASESOR)->whereKey($datos['asesorId'])->exists()) {
            throw ValidationException::withMessages([
                'asesorId' => 'Seleccione un asesor válido.',
            ]);
        }

        $ahora = now();

        DB::table('rutero_lider_asesor')->upsert([
            [
                'lider_id' => $datos['liderId'],
                'asesor_id' => $datos['asesorId'],
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ], ['lider_id', 'asesor_id'], ['updated_at']);

        $this->reset('asesorId');
        $this->mensaje = 'Asesor asignado.';
    }

    public function retirar(int $asesorId): void
    {
        Gate::authorize(self::PERMISO);

        $datos = $this->validate([
            'liderId' => ['required', 'integer', 'min:1'],
        ]);

        $eliminados = DB::table('rutero_lider_asesor')
            ->where('lider_id', $datos['liderId'])
            ->where('asesor_id', $asesorId)
            ->delete();

        $this->mensaje = $eliminados
            ? 'Asignación retirada.'
            : 'La asignación ya no existe.';
    }

    public function render(): View
    {
        Gate::authorize(self::PERMISO);

        $lideres = User::role(self::ROL_LIDER)
            ->orderBy('name')
            ->get(['id', 'name']);

        $lider = $lideres->first(
            fn (User $usuario) => (string) $usuario->id === $this->liderId
        );

        $asignados = collect();
        $disponibles = collect();

        if ($lider) {
            $idsAsignados = DB::table('rutero_lider_asesor')
                ->where('lider_id', $lider->id)
                ->pluck('asesor_id');

            $columnas = ['id', 'name', 'codigo_asesor', 'nombre_asesor'];

            $asignados = User::whereIn('id', $idsAsignados)
                ->orderBy('name')
                ->get($columnas);

            $disponibles = User::role(self::ROL_ASESOR)
                ->where('id', '<>', $lider->id)
                ->whereNotIn('id', $idsAsignados)
                ->orderBy('name')
                ->get($columnas);
        }

        return view('livewire.admin.rutero.asignaciones', compact(
            'lideres', 'lider', 'asignados', 'disponibles'
        ));
    }
}