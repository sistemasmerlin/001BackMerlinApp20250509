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
            // === Payload base Ionic ===
            'cliente'        => ['required', 'array'],
            'sucursal'       => ['required', 'array'],
            'pqrs'           => ['required', 'array'],

            'correo_cliente' => ['nullable', 'string', 'max:150'],

            // ✅ opcional (pero recomendado)
            'asesor'                 => ['nullable', 'array'],
            'asesor.codigo_asesor'   => ['nullable', 'string', 'max:20'],
            'asesor.nombre'          => ['nullable', 'string', 'max:120'],
            'asesor.correo'          => ['nullable', 'string', 'max:150'],

            /*'modoAplicacion' => ['required', 'in:productos,factura'],
          // modo productos
            'productos' => ['required_if:modoAplicacion,productos', 'array', 'min:1'],
            'productos.*.referencia'          => ['required_if:modoAplicacion,productos', 'string', 'max:80'],
            'productos.*.descripcion'         => ['nullable', 'string', 'max:255'],
            'productos.*.tipo_docto'          => ['nullable', 'string', 'max:20'],
            'productos.*.nro_docto'           => ['nullable', 'string', 'max:50'],
            'productos.*.fecha'               => ['nullable'], // YYYYMMDD
            'productos.*.unidadesSolicitadas' => ['required_if:modoAplicacion,productos', 'numeric', 'min:1'],
            'productos.*.submotivo_id'        => ['required_if:modoAplicacion,productos', 'integer'],
            'productos.*.requiereRecogida'    => ['nullable', 'boolean'],
            'productos.*.notas'               => ['nullable', 'string', 'max:2000'],
            'productos.*.adjuntos'            => ['nullable', 'array'],
            'productos.*.adjuntos.*.name'     => ['required_with:productos.*.adjuntos', 'string', 'max:200'],
            'productos.*.adjuntos.*.mime'     => ['required_with:productos.*.adjuntos', 'string', 'max:100'],
            'productos.*.adjuntos.*.base64'   => ['required_with:productos.*.adjuntos', 'string'], 

            // modo factura
            'factura' => ['required_if:modoAplicacion,factura', 'array'],
            'factura.info' => ['required_if:modoAplicacion,factura', 'array'],
            'factura.items' => ['required_if:modoAplicacion,factura', 'array', 'min:1'],
            'factura.submotivo_id' => ['required_if:modoAplicacion,factura', 'integer'],
            'factura.requiereRecogida' => ['nullable', 'boolean'],
            'factura.notas' => ['nullable', 'string', 'max:2000'],
            'factura.adjuntos' => ['nullable', 'array'],
            'factura.adjuntos.*.name'   => ['required_with:factura.adjuntos', 'string', 'max:200'],
            'factura.adjuntos.*.mime'   => ['required_with:factura.adjuntos', 'string', 'max:100'],
            'factura.adjuntos.*.base64' => ['required_with:factura.adjuntos', 'string'], */
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
