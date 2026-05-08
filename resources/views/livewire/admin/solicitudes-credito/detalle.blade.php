<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Solicitud #{{ $solicitud->id }}
            </h1>
            <p class="text-sm text-gray-500">
                {{ $solicitud->razon_social }} - {{ $solicitud->nit_cc }}
            </p>
        </div>

        <a
            href="{{ route('admin.solicitudes-credito.index') }}"
            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
        >
            Volver
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
            <p class="text-xs uppercase text-gray-400">Estado</p>
            <p class="font-semibold text-gray-800">{{ strtoupper($solicitud->estado) }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-4 shadow-sm">
            <p class="text-xs uppercase text-gray-400">Ciudad</p>
            <p class="font-semibold text-gray-800">{{ $solicitud->ciudad }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-4 shadow-sm">
            <p class="text-xs uppercase text-gray-400">Asesor</p>
            <p class="font-semibold text-gray-800">{{ $solicitud->nombre_asesor ?: '—' }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-4 shadow-sm">
            <p class="text-xs uppercase text-gray-400">Cupo sugerido</p>
            <p class="font-semibold text-gray-800">
                $ {{ number_format((float) $solicitud->cupo_sugerido, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="rounded-2xl border bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-lg font-bold text-gray-800">Documentos enviados</h2>

        <div class="space-y-4">
            @foreach($tiposDocumentos as $tipo)
                @php
                    $documentos = $solicitud->documentos
                        ->where('tipo_documento_credito_id', $tipo->id);
                @endphp

                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $tipo->nombre }}</h3>
                            <p class="text-xs text-gray-500">
                                Máximo {{ $tipo->cantidad_maxima }} archivo(s)
                            </p>
                        </div>

                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                            {{ $documentos->count() }} / {{ $tipo->cantidad_maxima }}
                        </span>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-100">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left">Archivo</th>
                                    <th class="px-3 py-2 text-left">Estado</th>
                                    <th class="px-3 py-2 text-left">Fecha</th>
                                    <th class="px-3 py-2 text-right">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @forelse($documentos as $doc)
                                    <tr>
                                        <td class="px-3 py-2">
                                            {{ $doc->nombre_original }}
                                        </td>

                                        <td class="px-3 py-2">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold
                                                @if($doc->estado === 'aprobado') bg-green-100 text-green-700
                                                @elseif($doc->estado === 'no_aprobado') bg-red-100 text-red-700
                                                @else bg-amber-100 text-amber-700
                                                @endif
                                            ">
                                                {{ strtoupper($doc->estado) }}
                                            </span>
                                        </td>

                                        <td class="px-3 py-2">
                                            {{ $doc->created_at?->format('Y-m-d H:i') }}
                                        </td>

                                        <td class="px-3 py-2 text-right">
                                            <a
                                                href="{{ Storage::disk($doc->disk)->url($doc->archivo) }}"
                                                target="_blank"
                                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700"
                                            >
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-gray-400">
                                            No se han enviado archivos para este documento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>