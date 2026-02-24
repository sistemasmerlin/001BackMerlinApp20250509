<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePqrsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente' => ['required', 'array'],
            'sucursal' => ['required', 'array'],
            'pqrs' => ['required', 'array'],
            'asesor' => ['nullable', 'array'],
            'modoAplicacion' => ['required', 'in:productos,factura'],
            'correo_cliente' => ['nullable', 'string', 'max:150'],

            // ✅ direccion_envio (solo si hay recogida)
            'direccion_envio' => ['nullable', 'array'],
            'direccion_envio.tipo' => ['nullable', 'in:punto,manual'],
            'direccion_envio.data' => ['nullable', 'array'],

            'productos' => ['nullable', 'array'],

            'productos.*.referencia' => ['nullable', 'string', 'max:50'],
            'productos.*.tipo_docto' => ['nullable', 'string', 'max:10'],
            'productos.*.nro_docto'  => ['nullable', 'string', 'max:30'],
            'productos.*.fecha'      => ['nullable'],

            'productos.*.unidadesSolicitadas' => ['nullable', 'numeric', 'min:0.0001'],

            // ✅ nuevo
            'productos.*.causal_id'  => ['required', 'integer', 'exists:pqrs_causales,id'],

            // opcional si lo mandas (yo lo dejaría opcional)
            'productos.*.submotivo_id' => ['nullable', 'integer'],

            'productos.*.requiereRecogida' => ['nullable', 'boolean'],

            'productos.*.adjuntos' => ['nullable', 'array'],
            'productos.*.adjuntos.*.name'   => ['required_with:productos.*.adjuntos', 'string'],
            'productos.*.adjuntos.*.mime'   => ['required_with:productos.*.adjuntos', 'string'],
            'productos.*.adjuntos.*.base64' => ['required_with:productos.*.adjuntos', 'string'],

            // ======= factura =======
            'factura' => ['nullable', 'array'],
            'factura.requiereRecogida' => ['nullable', 'boolean'],
            'factura.adjuntos' => ['nullable', 'array'],
            'factura.adjuntos.*.name' => ['required_with:factura.adjuntos', 'string'],
            'factura.adjuntos.*.mime' => ['required_with:factura.adjuntos', 'string'],
            'factura.adjuntos.*.base64' => ['required_with:factura.adjuntos', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente.required' => 'Falta información del cliente.',
            'sucursal.required' => 'Falta información de la sucursal.',
            'pqrs.asunto.required' => 'El asunto es obligatorio.',
            'pqrs.descripcion.required' => 'La descripción es obligatoria.',
            'modoAplicacion.in' => 'modoAplicacion debe ser productos o factura.',
            'productos.required_if' => 'Debes agregar al menos un producto.',
            'factura.required_if' => 'Debes consultar y seleccionar una factura.',
        ];
    }
}
