<div class="p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Solicitudes de crédito</h1>
            <p class="text-sm text-gray-500">Gestión y almacenamiento de solicitudes</p>
        </div>

        <div class="flex gap-2">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por razón social, NIT o nombre comercial"
                class="w-full md:w-80 rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500"
            >
{{-- 
            <button
                wire:click="abrirModal"
                class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
            >
                Nueva solicitud
            </button> --}}
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('auco_debug'))
        <div class="alert alert-warning mt-3">
            <strong>Respuesta / debug AUCO:</strong>
            <pre style="white-space: pre-wrap; margin-top:10px;">{{ json_encode(session('auco_debug'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @error('referencias')
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            {{ $message }}
        </div>
    @enderror

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">id</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Razón social</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">NIT / C.C.</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ciudad</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($solicitudes as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $item->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ optional($item->fecha_solicitud)->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $item->razon_social }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $item->nit_cc }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $item->ciudad }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                {{ strtoupper($item->estado) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">

                                    {{-- <button
                                        wire:click="verDetalle({{ $item->id }})"
                                        class="inline-flex items-center rounded-lg bg-zinc-800 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-900"
                                    >
                                        Ver
                                    </button>

                                    <a
                                        href="{{ route('admin.solicitudes-credito.pdf.unificado.ver', $item->id) }}"
                                        target="_blank"
                                        class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700"
                                    >
                                        Ver PDF Unificado
                                    </a> --}}

                                    <a
                                        href="{{ route('admin.solicitudes-credito.show', $item->id) }}"
                                        class="inline-flex items-center rounded-lg bg-zinc-800 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-900"
                                    >
                                        Ver detalle
                                    </a>

                            </div>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">
                            No hay solicitudes registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $solicitudes->links() }}
    </div>

    {{-- @if($modal)
        <div class="fixed inset-0 z-50 bg-black/50 p-4 overflow-y-auto">
            <div class="mx-auto my-6 w-full max-w-6xl rounded-2xl bg-white shadow-2xl">
                <div class="sticky top-0 flex items-center justify-between border-b bg-white px-6 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Nueva solicitud de crédito</h2>
                        <p class="text-sm text-gray-500">Completa la información principal</p>
                    </div>

                    <button wire:click="cerrarModal" class="rounded-lg px-3 py-2 text-sm text-gray-500 hover:bg-gray-100">
                        Cerrar
                    </button>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Fecha solicitud</label>
                            <input type="date" wire:model="solicitud.fecha_solicitud" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Razón social</label>
                            <input type="text" wire:model="solicitud.razon_social" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Nombre comercial</label>
                            <input type="text" wire:model="solicitud.nombre_comercial" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">NIT / C.C.</label>
                            <input type="text" wire:model="solicitud.nit_cc" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Ciudad</label>
                            <input type="text" wire:model="solicitud.ciudad" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Departamento</label>
                            <input type="text" wire:model="solicitud.depto" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Celular</label>
                            <input type="text" wire:model="solicitud.celular" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Correo electrónico</label>
                            <input type="email" wire:model="solicitud.correo_electronico" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Referencias comerciales</h3>
                            <button wire:click="agregarReferencia" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                                Agregar referencia
                            </button>
                        </div>

                        <div class="space-y-4">
                            @foreach($referencias as $index => $referencia)
                                <div class="rounded-2xl border border-gray-200 p-4">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 class="font-semibold text-gray-800">Referencia {{ $index + 1 }}</h4>
                                        <button wire:click="eliminarReferencia({{ $index }})" class="text-sm text-red-500 hover:text-red-700">
                                            Eliminar
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <input type="text" wire:model="referencias.{{ $index }}.empresa" placeholder="Empresa" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="text" wire:model="referencias.{{ $index }}.nit" placeholder="NIT" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="text" wire:model="referencias.{{ $index }}.telefono" placeholder="Teléfono" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="text" wire:model="referencias.{{ $index }}.depto" placeholder="Departamento" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="text" wire:model="referencias.{{ $index }}.ciudad" placeholder="Ciudad" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="number" step="0.01" wire:model="referencias.{{ $index }}.cupo_credito" placeholder="Cupo crédito" class="rounded-xl border border-gray-300 px-4 py-2">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button wire:click="cerrarModal" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button wire:click="guardar" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                            Guardar solicitud
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif --}}

@if($modalDetalle && $solicitudDetalle)
    <div class="fixed inset-0 z-50 bg-black/50 p-4 overflow-y-auto">
        <div class="mx-auto my-6 w-full max-w-6xl rounded-2xl bg-white shadow-2xl">

            <div class="sticky top-0 z-10 flex items-center justify-between border-b bg-white px-6 py-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        Solicitud #{{ $solicitudDetalle->id }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ $solicitudDetalle->razon_social }} - {{ $solicitudDetalle->nit_cc }}
                    </p>
                </div>

                <button
                    wire:click="cerrarDetalle"
                    class="rounded-lg px-3 py-2 text-sm text-gray-500 hover:bg-gray-100"
                >
                    Cerrar
                </button>
            </div>

            <div class="p-6 space-y-8">

                {{-- PDF --}}
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">PDF adjunto</h3>

                    @if($solicitudDetalle->pdf_unificado_path)
                        <div class="flex flex-wrap gap-2">
                            <a
                                href="{{ route('admin.solicitudes-credito.pdf.unificado.ver', $solicitudDetalle->id) }}"
                                target="_blank"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                            >
                                Ver PDF actual
                            </a>

                            <a
                                href="{{ route('admin.solicitudes-credito.pdf.unificado.descargar', $solicitudDetalle->id) }}"
                                class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700"
                            >
                                Descargar PDF
                            </a>
                        </div>

                        <p class="mt-2 text-xs text-gray-500">
                            Archivo: {{ $solicitudDetalle->pdf_unificado_nombre }}
                        </p>
                    @else
                        <p class="text-sm text-gray-500">Esta solicitud no tiene PDF adjunto.</p>
                    @endif

                    <div class="mt-5 border-t pt-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Reemplazar PDF
                        </label>

                        <input
                            type="file"
                            wire:model="nuevoPdf"
                            accept="application/pdf"
                            class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm"
                        >

                        @error('nuevoPdf')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <button
                            wire:click="reemplazarPdf"
                            class="mt-3 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                        >
                            Reemplazar archivo
                        </button>

                        <p class="mt-2 text-xs text-gray-500">
                            Al reemplazar, se eliminará el PDF actual y se guardará el nuevo.
                        </p>
                    </div>
                </div>

                {{-- Datos principales --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">Datos de la empresa</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach([
                            'Fecha solicitud' => optional($solicitudDetalle->fecha_solicitud)->format('Y-m-d'),
                            'Departamento' => $solicitudDetalle->depto,
                            'Ciudad' => $solicitudDetalle->ciudad,
                            'Razón social' => $solicitudDetalle->razon_social,
                            'Nombre comercial' => $solicitudDetalle->nombre_comercial,
                            'NIT / C.C.' => $solicitudDetalle->nit_cc,
                            'Representante legal' => $solicitudDetalle->representante_legal,
                            'Identificación representante' => $solicitudDetalle->identificacion_representante,
                            'Dirección negocio' => $solicitudDetalle->direccion_negocio,
                            'Barrio' => $solicitudDetalle->barrio,
                            'Teléfono fijo' => $solicitudDetalle->telefono_fijo,
                            'Celular' => $solicitudDetalle->celular,
                            'Correo electrónico' => $solicitudDetalle->correo_electronico,
                            'Estado' => $solicitudDetalle->estado,
                            'Asesor' => optional($solicitudDetalle->user)->nombre_asesor ?? optional($solicitudDetalle->user)->name,
                        ] as $label => $value)
                            <div class="rounded-xl border border-gray-200 p-3">
                                <p class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</p>
                                <p class="mt-1 text-sm text-gray-800">{{ $value ?: '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
    <h3 class="mb-3 text-lg font-semibold text-gray-800">Datos del asesor</h3>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @foreach([
            'Código asesor' => $solicitudDetalle->codigo_asesor,
            'Nombre asesor' => $solicitudDetalle->nombre_asesor,
            'Cédula asesor' => $solicitudDetalle->cedula_asesor,
            'Celular asesor' => $solicitudDetalle->celular_asesor,
            'Email asesor' => $solicitudDetalle->email_asesor,
            'Categoría asesor' => $solicitudDetalle->categoria_asesor,
            'Usuario creador' => optional($solicitudDetalle->user)->name,
        ] as $label => $value)
            <div class="rounded-xl border border-gray-200 p-3">
                <p class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-sm text-gray-800">
                    @if(is_array($value))
                        {{ implode(', ', $value) }}
                    @else
                        {{ $value ?: '—' }}
                    @endif
                </p>
            </div>
        @endforeach
    </div>
</div>

                {{-- Contactos --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">Contactos secundarios</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach([
                            'Contacto compras' => $solicitudDetalle->contacto_compras,
                            'Teléfono compras' => $solicitudDetalle->telefono_compras,
                            'Correo compras' => $solicitudDetalle->correo_compras,
                            'Contacto tesorería' => $solicitudDetalle->contacto_tesoreria,
                            'Teléfono tesorería' => $solicitudDetalle->telefono_tesoreria,
                            'Correo tesorería' => $solicitudDetalle->correo_tesoreria,
                            'Contacto factura electrónica' => $solicitudDetalle->contacto_factura_electronica,
                            'Teléfono factura electrónica' => $solicitudDetalle->telefono_factura_electronica,
                            'Correo factura electrónica' => $solicitudDetalle->correo_factura_electronica,
                        ] as $label => $value)
                            <div class="rounded-xl border border-gray-200 p-3">
                                <p class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</p>
                                <p class="mt-1 text-sm text-gray-800">{{ $value ?: '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Retenciones e infraestructura --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">Retenciones e infraestructura</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach([
                            'RTE Fuente' => $solicitudDetalle->rte_fuente ? 'SI' : 'NO',
                            'RTE IVA' => $solicitudDetalle->rte_iva ? 'SI' : 'NO',
                            'RTE ICA' => $solicitudDetalle->rte_ica ? 'SI' : 'NO',
                            'Antigüedad comercial' => $solicitudDetalle->antiguedad_comercial,
                            'Tiempo antigüedad' => $solicitudDetalle->tiempo_antiguedad,
                            'Tipo negocio' => $solicitudDetalle->tipo_negocio,
                            'Puntos venta' => $solicitudDetalle->puntos_venta,
                            'Canal tradicional' => $solicitudDetalle->canal_tradicional,
                            'Canal corporativo' => $solicitudDetalle->canal_corporativo,
                            'Número empleados' => $solicitudDetalle->numero_empleados,
                            'Ventas proyectadas mes' => $solicitudDetalle->ventas_proyectadas_mes ? '$ '.number_format($solicitudDetalle->ventas_proyectadas_mes, 0, ',', '.') : '—',
                            'Cupo sugerido' => $solicitudDetalle->cupo_sugerido ? '$ '.number_format($solicitudDetalle->cupo_sugerido, 0, ',', '.') : '—',
                        ] as $label => $value)
                            <div class="rounded-xl border border-gray-200 p-3">
                                <p class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</p>
                                <p class="mt-1 text-sm text-gray-800">{{ $value ?: '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Autorización --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">Autorización tratamiento de datos</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach([
                            'Departamento autorización' => $solicitudDetalle->autorizacion_depto,
                            'Ciudad autorización' => $solicitudDetalle->autorizacion_ciudad,
                            'Fecha autorización' => optional($solicitudDetalle->autorizacion_fecha)->format('Y-m-d'),
                            'Nombre 1' => $solicitudDetalle->autorizacion_nombre_1,
                            'Documento 1' => $solicitudDetalle->autorizacion_documento_1,
                            'Lugar expedición 1' => $solicitudDetalle->autorizacion_lugar_expedicion_1,
                            'Razón social autorización' => $solicitudDetalle->autorizacion_razon_social,
                            'NIT autorización' => $solicitudDetalle->autorizacion_nit_cc,
                            'Nombre 2' => $solicitudDetalle->autorizacion_nombre_2,
                            'Documento 2' => $solicitudDetalle->autorizacion_documento_2,
                            'Lugar expedición 2' => $solicitudDetalle->autorizacion_lugar_expedicion_2,
                            'Teléfono fijo autorización' => $solicitudDetalle->autorizacion_telefono_fijo,
                            'Celular autorización' => $solicitudDetalle->autorizacion_celular,
                            'Correo autorización' => $solicitudDetalle->autorizacion_correo,
                            'Dirección autorización' => $solicitudDetalle->autorizacion_direccion,
                        ] as $label => $value)
                            <div class="rounded-xl border border-gray-200 p-3">
                                <p class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</p>
                                <p class="mt-1 text-sm text-gray-800">{{ $value ?: '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Referencias --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">Referencias comerciales</h3>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left">Empresa</th>
                                    <th class="px-3 py-2 text-left">NIT</th>
                                    <th class="px-3 py-2 text-left">Ciudad</th>
                                    <th class="px-3 py-2 text-left">Teléfono</th>
                                    <th class="px-3 py-2 text-right">Cupo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($solicitudDetalle->referencias as $ref)
                                    <tr>
                                        <td class="px-3 py-2">{{ $ref->empresa }}</td>
                                        <td class="px-3 py-2">{{ $ref->nit }}</td>
                                        <td class="px-3 py-2">{{ $ref->ciudad }}</td>
                                        <td class="px-3 py-2">{{ $ref->telefono }}</td>
                                        <td class="px-3 py-2 text-right">$ {{ number_format((float) $ref->cupo_credito, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 text-center text-gray-500">Sin referencias.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Direcciones --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">Direcciones adicionales</h3>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left">Contacto</th>
                                    <th class="px-3 py-2 text-left">Dirección</th>
                                    <th class="px-3 py-2 text-left">Ciudad</th>
                                    <th class="px-3 py-2 text-left">Teléfono</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($solicitudDetalle->direcciones as $dir)
                                    <tr>
                                        <td class="px-3 py-2">{{ $dir->contacto }}</td>
                                        <td class="px-3 py-2">{{ $dir->direccion }}</td>
                                        <td class="px-3 py-2">{{ $dir->ciudad }}</td>
                                        <td class="px-3 py-2">{{ $dir->telefono }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-gray-500">Sin direcciones adicionales.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endif
</div>