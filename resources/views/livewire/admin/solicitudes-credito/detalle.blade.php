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

                @php
                    $estadoActual = strtolower(trim($solicitud->estado ?? ''));
                @endphp

                <div class="mt-2">
                    @if ($estadoActual === 'en_revision')
                        <button type="button"
                            onclick="confirm('¿Seguro que deseas pasar esta solicitud a pendiente?') || event.stopImmediatePropagation()"
                            wire:click="pasarAPendiente"
                            class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white hover:bg-amber-600">
                            Pasar a pendiente
                        </button>
                    @elseif ($estadoActual === 'pendiente')
                        <button type="button"
                            onclick="confirm('¿Seguro que deseas pasar esta solicitud a en revisión?') || event.stopImmediatePropagation()"
                            wire:click="pasarAEnRevision"
                            class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white hover:bg-amber-600">
                            Pasar a en revisión
                        </button>
                    @endif
                </div>
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
                            'Razón social' => $solicitud->razon_social,
                            'Nombre comercial' => $solicitud->nombre_comercial,
                            'NIT / C.C.' => $solicitud->nit_cc,
                            'Representante legal' => $solicitud->representante_legal,
                            'Identificación representante' => $solicitud->identificacion_representante,
                            'Dirección negocio' =>
                                $solicitud->direccion_negocio .
                                ' - ' .
                                $solicitud->barrio .
                                ' (' .
                                $solicitud->ciudad .
                                ' - ' .
                                $solicitud->depto .
                                ')',
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
                            'Nombre asesor' => $solicitud->nombre_asesor . ' - ' . optional($solicitud->user)->name,
                            'Cédula asesor' => $solicitud->cedula_asesor,
                            'Celular asesor' => $solicitud->celular_asesor,
                            'Email asesor' => $solicitud->email_asesor,
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
                            'Fecha autorización' => optional($solicitud->autorizacion_fecha)->format('Y-m-d'),
                            'Nombre' => $solicitud->autorizacion_nombre_1,
                            'Documento' => $solicitud->autorizacion_documento_1,
                            'Lugar expedición' => $solicitud->autorizacion_lugar_expedicion_1,
                            'Razón social autorización' => $solicitud->autorizacion_razon_social,
                            'NIT autorización' => $solicitud->autorizacion_nit_cc,
                            'Teléfono fijo autorización' => $solicitud->autorizacion_telefono_fijo,
                            'Celular autorización' => $solicitud->autorizacion_celular,
                            'Correo autorización' => $solicitud->autorizacion_correo,
                            'Dirección autorización' =>
                                $solicitud->autorizacion_direccion .
                                ' - ' .
                                $solicitud->autorizacion_barrio .
                                ' (' .
                                $solicitud->autorizacion_ciudad .
                                ' - ' .
                                $solicitud->autorizacion_depto .
                                ')',
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
                    <button type="button" wire:click="abrirModalReferencia"
                        class="rounded-lg bg-zinc-800 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-900">
                        Crear nueva referencia
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Empresa</th>
                                <th class="px-4 py-3 text-left">NIT</th>
                                <th class="px-4 py-3 text-left">Ciudad</th>
                                <th class="px-4 py-3 text-left">Teléfono</th>
                                <th class="px-4 py-3 text-right">Cupo solicitado</th>
                                <th class="px-4 py-3 text-left">Quién da referencia</th>
                                <th class="px-4 py-3 text-right">Cupo asignado</th>
                                <th class="px-4 py-3 text-left">Antigüedad</th>
                                <th class="px-4 py-3 text-left">Promedio pago</th>
                                <th class="px-4 py-3 text-left">Cheques devueltos</th>
                                <th class="px-4 py-3 text-left">Activo</th>
                                <th class="px-4 py-3 text-left">Fecha referencia</th>
                                <th class="px-4 py-3 text-left">Último despacho</th>
                                <th class="px-4 py-3 text-left">Concepto</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
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
                                    <td class="px-4 py-3">{{ $ref->quien_da_referencia ?: '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        {{ $ref->cupo_asignado ? '$ ' . number_format((float) $ref->cupo_asignado, 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-4 py-3">{{ $ref->antiguedad_comercial ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ $ref->promedio_pago ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ $ref->cheques_devueltos ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ $ref->activo ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        {{ optional($ref->fecha_referencia)->format('Y-m-d') ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ optional($ref->ultimo_despacho)->format('Y-m-d') ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3">{{ $ref->concepto ?: '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" wire:key="btn-info-ref-{{ $ref->id }}"
                                            wire:click.prevent="abrirInfoReferenciacion({{ $ref->id }})"
                                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                            Agregar info referenciación
                                        </button>

                                        <button type="button"
                                            onclick="confirm('¿Eliminar esta referencia comercial?') || event.stopImmediatePropagation()"
                                            wire:click="eliminarReferencia({{ $ref->id }})"
                                            class="ml-2 rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="px-4 py-6 text-center text-gray-400">
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
                        Consulta DataCrédito / Centrales de riesgo
                    </h3>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Puntaje / Score</label>
                            <input type="text" inputmode="numeric" wire:model.defer="datacreditoScore"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Ingresos / Ventas</label>
                            <input type="text" inputmode="numeric" wire:model.defer="datacreditoIngresosVentas"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Nivel de
                                endeudamiento</label>
                            <input type="text" inputmode="numeric"
                                wire:model.defer="datacreditoNivelEndeudamiento"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Sector reporte
                                negativo</label>
                            <input type="text" wire:model.defer="datacreditoSectorReporteNegativo"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]/g, '')"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Valor reporte
                                negativo</label>
                            <input type="text" inputmode="numeric"
                                wire:model.defer="datacreditoValorReporteNegativo"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Resultado</label>
                            <select wire:model.defer="datacreditoResultado"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm">
                                <option value="">Seleccione</option>
                                <option value="APROBADO">APROBADO</option>
                                <option value="RECHAZADO">RECHAZADO</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Comentario centrales</label>
                        <textarea wire:model.defer="comentarioReporteCentrales" rows="3"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm"></textarea>
                    </div>

        @if($solicitud->estado != 'aprobado_parcial')
                
                    <button wire:click="actualizarReporteCentrales"
                        class="mt-4 rounded-lg bg-zinc-800 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">
                        Guardar centrales
                    </button>
        @endif
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
                    @php
                        $cupoMayor25 = (float) $solicitud->cupo_sugerido > 25000000;

                        $documentosFinancieros = ['DECLARACION DE RENTA', 'ESTADO DE RESULTADOS', 'BALANCE GENERAL'];

                        $tiposDocumentosVisibles = $tiposDocumentos->filter(function ($tipo) use (
                            $cupoMayor25,
                            $documentosFinancieros,
                        ) {
                            if ($cupoMayor25) {
                                return true;
                            }

                            return !in_array($tipo->nombre, $documentosFinancieros);
                        });
                    @endphp

                    @foreach ($tiposDocumentosVisibles as $tipo)
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
        </div>
    </div>



    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold text-gray-800">Comentarios de la solicitud</h2>

        <textarea wire:model.defer="nuevoComentario" rows="3" placeholder="Escribe un comentario interno"
            class="mt-4 w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"></textarea>

        @error('nuevoComentario')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror


        <button wire:click="guardarComentario"
            class="mt-3 rounded-lg bg-zinc-800 px-4 py-2 text-sm font-semibold text-white">
            Guardar comentario
        </button>
        

        <div class="mt-5 space-y-3">
            @forelse($solicitud->comentarios()->with('usuario')->latest()->get() as $comentario)
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="flex justify-between gap-3">
                        <p class="text-sm font-bold text-gray-800">
                            {{ $comentario->usuario?->name ?? 'Usuario no disponible' }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $comentario->created_at?->format('Y-m-d H:i') }}
                        </p>
                    </div>

                    <p class="mt-2 text-sm text-gray-700">
                        {{ $comentario->comentario }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-gray-400">Sin comentarios registrados.</p>
            @endforelse
        </div>

        @if (in_array(strtolower(trim($solicitud->estado ?? '')), ['pendiente', 'en_revision']))

            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5">
                <h3 class="text-sm font-bold text-amber-800">
                    Pendientes para habilitar segunda aprobación
                </h3>

                @if (count($this->pendientesParaAprobacion) > 0)

                    <ul class="mt-3 space-y-2 text-sm text-amber-700">

                        @foreach ($this->pendientesParaAprobacion as $pendiente)
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5">•</span>
                                <span>{{ $pendiente }}</span>
                            </li>
                        @endforeach

                    </ul>
                @else
                    <div class="mt-3 rounded-lg bg-green-100 px-4 py-3 text-sm font-semibold text-green-700">
                        Todo está completo. Puedes pasar a segunda aprobación.
                    </div>

                @endif
            </div>

            <div class="mt-6 rounded-2xl bg-white p-5 shadow-sm border border-gray-200">

                <h2 class="text-lg font-bold text-gray-800">
                    Resultado revisión documental
                </h2>

                <textarea wire:model.defer="comentarioRevision" rows="3" placeholder="Comentario obligatorio"
                    class="mt-4 w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"></textarea>

                @error('comentarioRevision')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-4 flex gap-3">

                    <button wire:click="pasarSegundaAprobacion" @disabled(!$this->puedePasarSegundaAprobacion)
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-white
                {{ $this->puedePasarSegundaAprobacion ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }}">

                        Pasar a segunda aprobación

                    </button>

                    <button wire:click="rechazarSolicitudRevision"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">

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

    @if ($modalInfoReferencia)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-lg font-bold text-gray-800">Información de referenciación</h2>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <input wire:model.defer="referenciacionForm.quien_da_referencia" placeholder="Quién da referencia"
                    class="rounded-xl border px-4 py-2 text-sm">
                <input wire:model.defer="referenciacionForm.cupo_asignado" type="number" placeholder="Cupo asignado"
                    class="rounded-xl border px-4 py-2 text-sm">
                <input wire:model.defer="referenciacionForm.antiguedad_comercial" placeholder="Antigüedad comercial"
                    class="rounded-xl border px-4 py-2 text-sm">
                <input wire:model.defer="referenciacionForm.promedio_pago" placeholder="Promedio pago"
                    class="rounded-xl border px-4 py-2 text-sm">
                <input wire:model.defer="referenciacionForm.cheques_devueltos" placeholder="Cheques devueltos"
                    class="rounded-xl border px-4 py-2 text-sm">
                <input wire:model.defer="referenciacionForm.activo" placeholder="Activo"
                    class="rounded-xl border px-4 py-2 text-sm">
                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-500">Fecha referencia</label>
                    <input wire:model.defer="referenciacionForm.fecha_referencia" type="date"
                        class="w-full rounded-xl border px-4 py-2 text-sm">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-500">Último despacho</label>
                    <input wire:model.defer="referenciacionForm.ultimo_despacho" type="date"
                        class="w-full rounded-xl border px-4 py-2 text-sm">
                </div>
            </div>

            <textarea wire:model.defer="referenciacionForm.concepto" rows="3" placeholder="Concepto"
                class="mt-4 w-full rounded-xl border px-4 py-2 text-sm"></textarea>

            <div class="mt-5 flex justify-end gap-3">
                <button wire:click="$set('modalInfoReferencia', false)" class="rounded-lg border px-4 py-2 text-sm">
                    Cancelar
                </button>

                <button wire:click="guardarInfoReferenciacion"
                    class="rounded-lg bg-zinc-800 px-4 py-2 text-sm font-semibold text-white">
                    Guardar
                </button>
            </div>
        </div>
    </div>
@endif

@if ($modalReferencia)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-lg font-bold text-gray-800">Crear nueva referencia</h2>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <input wire:model.defer="referenciaForm.empresa" placeholder="Empresa"
                    class="rounded-xl border px-4 py-2 text-sm">
                <input wire:model.defer="referenciaForm.nit" placeholder="NIT"
                    class="rounded-xl border px-4 py-2 text-sm">
                <select wire:model.live="referenciaForm.cod_depto" class="rounded-xl border px-4 py-2 text-sm">
                    <option value="">Seleccione departamento</option>
                    @foreach ($departamentosReferencia as $d)
                        <option value="{{ $d['cod_depto'] }}">
                            {{ $d['cod_depto'] }} - {{ $d['depto'] }}
                        </option>
                    @endforeach
                </select>

                <select wire:model.live="referenciaForm.cod_ciudad" class="rounded-xl border px-4 py-2 text-sm">
                    <option value="">Seleccione ciudad</option>
                    @foreach ($ciudadesReferencia as $c)
                        <option value="{{ $c['cod_ciudad'] }}">
                            {{ $c['cod_ciudad'] }} - {{ $c['ciudad'] }}
                        </option>
                    @endforeach
                </select>
                <input wire:model.defer="referenciaForm.telefono" placeholder="Teléfono"
                    class="rounded-xl border px-4 py-2 text-sm">
                <input wire:model.defer="referenciaForm.cupo_credito" type="number" placeholder="Cupo crédito"
                    class="rounded-xl border px-4 py-2 text-sm">
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button wire:click="$set('modalReferencia', false)" class="rounded-lg border px-4 py-2 text-sm">
                    Cancelar
                </button>

                <button wire:click="guardarReferencia"
                    class="rounded-lg bg-zinc-800 px-4 py-2 text-sm font-semibold text-white">
                    Guardar referencia
                </button>
            </div>
        </div>
    </div>
@endif


</div>


</div>
