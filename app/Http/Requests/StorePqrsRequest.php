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
            'telefono_cliente' => ['required', 'string'],
            // ✅ direccion_envio (solo si hay recogida)
            'direccion_envio' => ['nullable', 'array'],
            'direccion_envio.tipo' => ['nullable', 'in:punto,manual'],
            'direccion_envio.data' => ['nullable', 'array'],

            'productos' => ['nullable', 'array'],

            'productos.*.referencia' => ['required', 'string', 'max:50'],
            'productos.*.descripcion' => ['required', 'string', 'max:250'],
            'productos.*.tipo_docto' => ['required', 'string', 'max:10'],
            'productos.*.nro_docto'  => ['required', 'string', 'max:30'],
            'productos.*.fecha'      => ['required'],
            'productos.*.precio' => ['required', 'numeric', 'min:0'],
            'productos.*.bruto'  => ['required', 'numeric', 'min:0'],
            'productos.*.iva'    => ['required', 'numeric', 'min:0'],
            'productos.*.neto'   => ['required', 'numeric', 'min:0'],
            'productos.*.notas' => ['nullable', 'string', 'max:2000'],

            'productos.*.unidadesSolicitadas' => ['required', 'numeric', 'min:0.0001'],

            // ✅ nuevo
            'productos.*.causal_id'  => ['required', 'integer', 'exists:pqrs_causales,id'],

            // opcional si lo mandas (yo lo dejaría opcional)
            'productos.*.submotivo_id' => ['required', 'integer'],

            'productos.*.requiereRecogida' => ['required', 'boolean'],

            'productos.*.adjuntos' => ['nullable', 'array'],
            'productos.*.adjuntos.*.name'   => ['required_with:productos.*.adjuntos', 'string'],
            'productos.*.adjuntos.*.mime'   => ['required_with:productos.*.adjuntos', 'string'],
            'productos.*.adjuntos.*.base64' => ['required_with:productos.*.adjuntos', 'string'],

            // ======= factura =======

            'factura' => ['nullable', 'array'],
            'factura.motivo' => ['nullable', 'string'],
            'factura.motivo2' => ['nullable', 'string'],
            'factura.causal_id' => ['nullable', 'integer', 'exists:pqrs_causales,id'],
            'factura.requiereRecogida' => ['nullable', 'boolean'],
            'factura.notas' => ['nullable', 'string'],

            'factura.info' => ['nullable', 'array'],
            'factura.info.numero' => ['nullable', 'string'],
            'factura.info.total' => ['nullable', 'numeric'],
            'factura.info.fecha' => ['nullable'],

            'factura.items' => ['nullable', 'array'],
            'factura.items.*.referencia' => ['nullable', 'string'],
            'factura.items.*.descripcion' => ['nullable', 'string'],
            'factura.items.*.tipo_docto' => ['nullable', 'string'],
            'factura.items.*.nro_docto' => ['nullable', 'string'],
            'factura.items.*.fecha' => ['nullable'],
            'factura.items.*.cant_inv' => ['nullable', 'numeric'],
            'factura.items.*.precio' => ['nullable', 'numeric'],
            'factura.items.*.bruto' => ['nullable', 'numeric'],
            'factura.items.*.iva' => ['nullable', 'numeric'],
            'factura.items.*.neto' => ['nullable', 'numeric'],
            'factura.items.*.unidadesSolicitadas' => ['nullable', 'numeric'],

            'factura.adjuntos' => ['nullable', 'array'],
            'factura.adjuntos.*.name' => ['nullable', 'string'],
            'factura.adjuntos.*.mime' => ['nullable', 'string'],
            'factura.adjuntos.*.base64' => ['nullable', 'string'],
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
