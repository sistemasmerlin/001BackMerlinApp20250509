<div class="rounded-2xl border bg-white p-5 shadow-sm">

    <div class="mb-4 rounded-2xl bg-white px-5 py-4 shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4 text-sm">

            <div>
                <span class="text-gray-400 font-semibold">ID:</span>
                <span class="font-bold text-gray-800">#{{ $solicitud->id }}</span>
            </div>

            <div>
                <span class="text-gray-400 font-semibold">Cliente:</span>
                <span class="font-bold text-gray-800">{{ $solicitud->razon_social ?: '—' }}</span>
            </div>

            <div>
                <span class="text-gray-400 font-semibold">Asesor:</span>
                <span class="font-bold text-gray-800">{{ $solicitud->nombre_asesor ?: '—' }}</span>
            </div>

            <div>
                <span class="text-gray-400 font-semibold">Número cotización: </span>
                <span class="font-bold text-gray-800">{{ $solicitud->numero_cotizacion ?: '—' }}</span>
            </div>

            <div>
                <span class="text-gray-400 font-semibold">Estado:</span>
                <span class="font-bold text-gray-800">{{ strtoupper($solicitud->estado ?: '—') }}</span>
            </div>

        </div>
    </div>
    <div x-data="{ abierto: false, tab: 'empresa' }" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <button type="button" @click="abierto = !abierto"
            class="flex w-full items-center justify-between bg-white px-6 py-4 text-left hover:bg-gray-50">
            <div>
                <h2 class="text-lg font-bold text-gray-800">
                    Información completa de la solicitud
                </h2>
                <p class="text-sm text-gray-500">
                    Datos de empresa, asesor, contactos, referencias y direcciones.
                </p>
            </div>

            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600"
                x-text="abierto ? 'Ocultar' : 'Ver información'"></span>
        </button>

        <div x-show="abierto" x-collapse class="border-t border-gray-200 bg-gray-50 p-6">
            <div class="mb-5 flex flex-wrap gap-2">
                @foreach ([
        'empresa' => 'Empresa',
        'asesor' => 'Asesor',
        'contactos' => 'Contactos',
        'retenciones' => 'Retenciones',
        'autorizacion' => 'Autorización',
        'referencias' => 'Referencias',
        'direcciones' => 'Direcciones',
        'centrales' => 'Centrales de riesgo',
    ] as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'"
                        class="rounded-full px-4 py-2 text-xs font-bold"
                        :class="tab === '{{ $key }}'
                            ?
                            'bg-zinc-900 text-white' :
                            'border border-gray-200 bg-white text-gray-600'">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @php
                $bloques = [
                    'empresa' => [
                        'titulo' => 'Datos de la empresa',
                        'datos' => [
                            'Fecha solicitud' => optional($solicitud->fecha_solicitud)->format('Y-m-d'),
                            'Departamento' => $solicitud->depto,
                            'Ciudad' => $solicitud->ciudad,
                            'Razón social' => $solicitud->razon_social,
                            'Nombre comercial' => $solicitud->nombre_comercial,
                            'NIT / C.C.' => $solicitud->nit_cc,
                            'Representante legal' => $solicitud->representante_legal,
                            'Identificación representante' => $solicitud->identificacion_representante,
                            'Dirección negocio' => $solicitud->direccion_negocio,
                            'Barrio' => $solicitud->barrio,
                            'Teléfono fijo' => $solicitud->telefono_fijo,
                            'Celular' => $solicitud->celular,
                            'Correo electrónico' => $solicitud->correo_electronico,
                            'Estado' => $solicitud->estado,
                            'Asesor' => optional($solicitud->user)->nombre_asesor ?? optional($solicitud->user)->name,
                        ],
                    ],
                    'asesor' => [
                        'titulo' => 'Datos del asesor',
                        'datos' => [
                            'Código asesor' => $solicitud->codigo_asesor,
                            'Nombre asesor' => $solicitud->nombre_asesor,
                            'Cédula asesor' => $solicitud->cedula_asesor,
                            'Celular asesor' => $solicitud->celular_asesor,
                            'Email asesor' => $solicitud->email_asesor,
                            'Categoría asesor' => $solicitud->categoria_asesor,
                            'Usuario creador' => optional($solicitud->user)->name,
                        ],
                    ],
                    'contactos' => [
                        'titulo' => 'Contactos secundarios',
                        'datos' => [
                            'Contacto compras' => $solicitud->contacto_compras,
                            'Teléfono compras' => $solicitud->telefono_compras,
                            'Correo compras' => $solicitud->correo_compras,
                            'Contacto tesorería' => $solicitud->contacto_tesoreria,
                            'Teléfono tesorería' => $solicitud->telefono_tesoreria,
                            'Correo tesorería' => $solicitud->correo_tesoreria,
                            'Contacto factura electrónica' => $solicitud->contacto_factura_electronica,
                            'Teléfono factura electrónica' => $solicitud->telefono_factura_electronica,
                            'Correo factura electrónica' => $solicitud->correo_factura_electronica,
                        ],
                    ],
                    'retenciones' => [
                        'titulo' => 'Retenciones e infraestructura',
                        'datos' => [
                            'RTE Fuente' => $solicitud->rte_fuente ? 'SI' : 'NO',
                            'RTE IVA' => $solicitud->rte_iva ? 'SI' : 'NO',
                            'RTE ICA' => $solicitud->rte_ica ? 'SI' : 'NO',
                            'Antigüedad comercial' => $solicitud->antiguedad_comercial,
                            'Tiempo antigüedad' => $solicitud->tiempo_antiguedad,
                            'Tipo negocio' => $solicitud->tipo_negocio,
                            'Puntos venta' => $solicitud->puntos_venta,
                            'Canal tradicional' => $solicitud->canal_tradicional,
                            'Canal corporativo' => $solicitud->canal_corporativo,
                            'Número empleados' => $solicitud->numero_empleados,
                            'Ventas proyectadas mes' => $solicitud->ventas_proyectadas_mes
                                ? '$ ' . number_format($solicitud->ventas_proyectadas_mes, 0, ',', '.')
                                : '—',
                            'Cupo sugerido' => $solicitud->cupo_sugerido
                                ? '$ ' . number_format($solicitud->cupo_sugerido, 0, ',', '.')
                                : '—',
                        ],
                    ],
                    'autorizacion' => [
                        'titulo' => 'Autorización tratamiento de datos',
                        'datos' => [
                            'Departamento autorización' => $solicitud->autorizacion_depto,
                            'Ciudad autorización' => $solicitud->autorizacion_ciudad,
                            'Fecha autorización' => optional($solicitud->autorizacion_fecha)->format('Y-m-d'),
                            'Nombre 1' => $solicitud->autorizacion_nombre_1,
                            'Documento 1' => $solicitud->autorizacion_documento_1,
                            'Lugar expedición 1' => $solicitud->autorizacion_lugar_expedicion_1,
                            'Razón social autorización' => $solicitud->autorizacion_razon_social,
                            'NIT autorización' => $solicitud->autorizacion_nit_cc,
                            'Nombre 2' => $solicitud->autorizacion_nombre_2,
                            'Documento 2' => $solicitud->autorizacion_documento_2,
                            'Lugar expedición 2' => $solicitud->autorizacion_lugar_expedicion_2,
                            'Teléfono fijo autorización' => $solicitud->autorizacion_telefono_fijo,
                            'Celular autorización' => $solicitud->autorizacion_celular,
                            'Correo autorización' => $solicitud->autorizacion_correo,
                            'Dirección autorización' => $solicitud->autorizacion_direccion,
                        ],
                    ],
                ];
            @endphp

            @foreach ($bloques as $key => $bloque)
                <div x-show="tab === '{{ $key }}'" class="rounded-2xl bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-gray-800">
                            {{ $bloque['titulo'] }}
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($bloque['datos'] as $label => $value)
                            <div class="border-b border-gray-100 px-5 py-3 md:border-r">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                    {{ $label }}
                                </p>
                                <p class="mt-1 text-sm font-semibold text-gray-800">
                                    {{ is_array($value) ? implode(', ', $value) : ($value ?: '—') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div x-show="tab === 'referencias'" class="rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-3">
                    <h3 class="text-sm font-bold text-gray-800">
                        Referencias comerciales
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Empresa</th>
                                <th class="px-4 py-3 text-left">NIT</th>
                                <th class="px-4 py-3 text-left">Ciudad</th>
                                <th class="px-4 py-3 text-left">Teléfono</th>
                                <th class="px-4 py-3 text-right">Cupo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($solicitud->referencias as $ref)
                                <tr>
                                    <td class="px-4 py-3">{{ $ref->empresa }}</td>
                                    <td class="px-4 py-3">{{ $ref->nit }}</td>
                                    <td class="px-4 py-3">{{ $ref->ciudad }}</td>
                                    <td class="px-4 py-3">{{ $ref->telefono }}</td>
                                    <td class="px-4 py-3 text-right">
                                        $ {{ number_format((float) $ref->cupo_credito, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                                        Sin referencias.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'centrales'" class="rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-3">
                    <h3 class="text-sm font-bold text-gray-800">
                        Reporte en centrales de riesgo
                    </h3>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">
                                Estado del reporte
                            </label>

                            <select wire:model.defer="reporteCentralesRiesgo"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                                <option value="sin_estado">Sin estado</option>
                                <option value="positivo">Positivo</option>
                                <option value="negativo">Negativo</option>
                            </select>

                            @error('reporteCentralesRiesgo')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">
                                Estado actual
                            </label>

                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                    @if ($solicitud->reporte_centrales_riesgo === 'positivo') bg-green-100 text-green-700
                    @elseif($solicitud->reporte_centrales_riesgo === 'negativo') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-600 @endif
                ">
                                {{ strtoupper(str_replace('_', ' ', $solicitud->reporte_centrales_riesgo ?: 'sin_estado')) }}
                            </span>
                        </div>

                    </div>

                    <div class="mt-4">
                        <label class="mb-1 block text-sm font-semibold text-gray-700">
                            Comentario
                        </label>

                        <textarea wire:model.defer="comentarioReporteCentrales" rows="3" @disabled($this->reporteCentralesBloqueado)
                            placeholder="Observación del reporte en centrales de riesgo"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500 disabled:bg-gray-100 disabled:text-gray-500"></textarea>

                        @error('comentarioReporteCentrales')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        @if (!$this->reporteCentralesBloqueado)
                            <button wire:click="actualizarReporteCentrales"
                                class="rounded-lg bg-zinc-800 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">
                                Guardar reporte
                            </button>
                        @else
                            <p class="text-sm font-semibold text-gray-500">
                                Reporte bloqueado. Ya fue definido como
                                {{ strtoupper($solicitud->reporte_centrales_riesgo) }}.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div x-show="tab === 'direcciones'" class="rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-3">
                    <h3 class="text-sm font-bold text-gray-800">
                        Direcciones adicionales
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Contacto</th>
                                <th class="px-4 py-3 text-left">Dirección</th>
                                <th class="px-4 py-3 text-left">Ciudad</th>
                                <th class="px-4 py-3 text-left">Teléfono</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($solicitud->direcciones as $dir)
                                <tr>
                                    <td class="px-4 py-3">{{ $dir->contacto }}</td>
                                    <td class="px-4 py-3">{{ $dir->direccion }}</td>
                                    <td class="px-4 py-3">{{ $dir->ciudad }}</td>
                                    <td class="px-4 py-3">{{ $dir->telefono }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                        Sin direcciones adicionales.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    
    <br>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

        <div class="px-6 py-4">
            <h2 class="text-lg font-bold text-gray-800">Documentos enviados</h2>
            <p class="text-sm text-gray-500">Aprobar, rechazar, eliminar o volver a subir documentos.</p>
        </div>


        @if (session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-y-4 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left">Documento</th>
                        <th class="px-3 py-3 text-left">Archivos enviados</th>
                        <th class="px-3 py-3 text-left">Subir nuevo</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach ($tiposDocumentos as $tipo)
                        @php
                            $documentos = $solicitud->documentos->where('tipo_documento_credito_id', $tipo->id);
                        @endphp

                        <tr class="align-top bg-white">
                            <td class="w-64 px-3 py-4">
                                <p class="font-semibold text-gray-800">{{ $tipo->nombre }}</p>
                                <p class="text-xs text-gray-500">
                                    Máx {{ $tipo->cantidad_maxima }} archivo(s)
                                </p>

                                <span
                                    class="mt-2 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                    {{ $documentos->count() }} / {{ $tipo->cantidad_maxima }}
                                </span>
                            </td>

                            <td class="px-3 py-4">
                                @forelse($documentos as $doc)
                                    <div class="mb-3 rounded-xl border border-gray-200 bg-gray-50 p-3">

                                        <div
                                            class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                                            <div>
                                                <p class="font-semibold text-gray-800">
                                                    {{ $doc->nombre_original }}
                                                </p>

                                                <p class="text-xs text-gray-500">
                                                    {{ $doc->created_at?->format('Y-m-d H:i') }}
                                                </p>

                                                @if ($doc->observacion)
                                                    <p class="mt-1 text-xs text-gray-600">
                                                        <strong>Obs:</strong> {{ $doc->observacion }}
                                                    </p>
                                                @endif
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2">
                                                <span
                                                    class="rounded-full px-3 py-1 text-xs font-semibold
                                                @if ($doc->estado === 'aprobado') bg-green-100 text-green-700
                                                @elseif($doc->estado === 'no_aprobado') bg-red-100 text-red-700
                                                @else bg-amber-100 text-amber-700 @endif
                                            ">
                                                    {{ strtoupper($doc->estado) }}
                                                </span>

                                                @if (!in_array($solicitud->estado, ['aprobado_parcial', 'aprobado', 'rechazado']))
                                                    <a href="{{ Storage::disk($doc->disk)->url($doc->archivo) }}"
                                                        target="_blank"
                                                        class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                                        Ver
                                                    </a>

                                                    <button wire:click="aprobarDocumento({{ $doc->id }})"
                                                        class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                                                        Aprobar
                                                    </button>

                                                    <button wire:click="rechazarDocumento({{ $doc->id }})"
                                                        class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                                                        Rechazar
                                                    </button>

                                                    <button
                                                        onclick="confirm('¿Eliminar este documento?') || event.stopImmediatePropagation()"
                                                        wire:click="eliminarDocumento({{ $doc->id }})"
                                                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                                        Eliminar
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <input type="text"
                                                wire:model.defer="observaciones.{{ $doc->id }}"
                                                placeholder="Observación para aprobar o rechazar"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs focus:border-red-500 focus:ring-red-500">
                                        </div>
                                    </div>
                                @empty
                                    <div
                                        class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-center text-xs text-gray-400">
                                        No se han enviado archivos.
                                    </div>
                                @endforelse
                            </td>

                            <td class="w-72 px-3 py-4">
                                <div class="rounded-xl border border-gray-200 bg-white p-3">

                                    @if (!in_array($solicitud->estado, ['aprobado_parcial', 'aprobado', 'rechazado']))
                                        <input type="file" wire:model="archivos.{{ $tipo->id }}"
                                            accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs">

                                        @error("archivos.$tipo->id")
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror

                                        <button wire:click="subirDocumento({{ $tipo->id }})"
                                            wire:loading.attr="disabled"
                                            class="mt-3 w-full rounded-lg bg-zinc-800 px-3 py-2 text-xs font-semibold text-white hover:bg-zinc-900">
                                            Subir / volver a subir
                                        </button>

                                        <div wire:loading
                                            wire:target="archivos.{{ $tipo->id }}, subirDocumento({{ $tipo->id }})"
                                            class="mt-2 text-xs text-gray-500">
                                            Procesando archivo...
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-500">
                                            No se pueden subir archivos cuando la solicitud ya avanzó de revisión.
                                        </p>
                                    @endif
                                </div>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($this->todosDocumentosRevisados && $solicitud->estado === 'en_revision')
                <div class="mt-6 rounded-2xl bg-white p-5 shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800">Resultado revisión documental</h2>

                    <textarea wire:model.defer="comentarioRevision" rows="3" placeholder="Comentario de revisión"
                        class="mt-4 w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"></textarea>

                    @error('comentarioRevision')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-4 flex gap-3">
                        <button wire:click="pasarSegundaAprobacion"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white">
                            Pasar a segunda aprobación
                        </button>

                        <button wire:click="rechazarSolicitudRevision"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white">
                            Rechazar solicitud
                        </button>
                    </div>
                </div>
            @endif

            @if ($solicitud->estado === 'aprobado_parcial')
                <div class="mt-6 rounded-2xl bg-white p-5 shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800">Cierre aprobado</h2>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Cupo asignado</label>
                            <input type="number" wire:model.defer="cupoAsignado"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2 text-sm">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-700">Condición de pago</label>
                            <select wire:model.defer="condicionPagoAprobada"
                                class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2 text-sm">
                                <option value="">Seleccione</option>
                                <option value="CONTADO">CONTADO</option>
                                <option value="8 DIAS">8 DÍAS</option>
                                <option value="15 DIAS">15 DÍAS</option>
                                <option value="30 DIAS">30 DÍAS</option>
                                <option value="45 DIAS">45 DÍAS</option>
                                <option value="60 DIAS">60 DÍAS</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-semibold text-gray-700">Comentario cierre aprobado</label>
                        <textarea wire:model.defer="comentarioCierreAprobado" rows="3"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2 text-sm"></textarea>
                    </div>

                    <button wire:click="cerrarAprobacion"
                        class="mt-4 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white">
                        Cerrar solicitud - aprobación
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
