<div class="p-6 space-y-6 text-black">

    {{-- HEADER --}}
    <div class="flex items-center justify-between border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-black">
                PQRS #{{ $pqrs->id }}
            </h1>
            <p class="text-sm font-medium text-black">
                {{ $pqrs->razon_social }} — NIT: {{ $pqrs->nit }}
            </p>
        </div>

        <a href="{{ route('pqrs.inicio') }}"
           class="border border-black px-4 py-2 text-sm font-semibold hover:bg-black hover:text-white transition">
            ← Volver
        </a>
    </div>

    {{-- ================= ENCABEZADO COMPLETO ================= --}}
    <div class="border border-black p-5 space-y-4">

        <h2 class="font-bold text-lg border-b border-black pb-2">
            Información General
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">

            <div><strong>Estado:</strong> {{ $pqrs->estado }}</div>
            <div><strong>Fecha creación:</strong> {{ optional($pqrs->fecha_creacion ?? $pqrs->created_at)->format('Y-m-d H:i') }}</div>
            <div><strong>Fecha revisado:</strong> {{ optional($pqrs->fecha_revisado)->format('Y-m-d H:i') ?? '—' }}</div>

            <div><strong>Fecha cierre:</strong> {{ optional($pqrs->fecha_cierre)->format('Y-m-d H:i') ?? '—' }}</div>
            <div><strong>Asesor:</strong> {{ $pqrs->nombre_asesor }}</div>
            <div><strong>Código asesor:</strong> {{ $pqrs->cod_asesor }}</div>

            <div><strong>Correo cliente:</strong> {{ $pqrs->correo_cliente }}</div>
            <div><strong>Correo asesor:</strong> {{ $pqrs->correo_asesor }}</div>
            <div><strong>Teléfono:</strong> {{ $pqrs->telefono }}</div>

            <div class="md:col-span-2">
                <strong>Dirección:</strong>
                {{ $pqrs->direccion }} — {{ $pqrs->ciudad }} — {{ $pqrs->departamento }}
            </div>

            <div><strong>Número ORM:</strong> {{ $pqrs->numero_orm ?? '—' }}</div>

            <div><strong>Tipo acuerdo:</strong> {{ $pqrs->tipo_acuerdo ?? '—' }}</div>
            <div><strong>Valor acuerdo:</strong> {{ $pqrs->valor_acuerdo ?? '—' }}</div>
            <div><strong>Nota acuerdo:</strong> {{ $pqrs->nota_acuerdo ?? '—' }}</div>

            <div><strong>Comentario cierre:</strong> {{ $pqrs->comentario_cierre ?? '—' }}</div>

        </div>
    </div>

{{-- ================= PRODUCTOS ================= --}}
<div class="border border-black bg-white">

    <div class="px-4 py-3 border-b border-black">
        <h2 class="font-bold text-black">
            Productos ({{ $pqrs->productos?->count() ?? 0 }})
        </h2>
    </div>

    @if(($pqrs->productos?->count() ?? 0) > 0)

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-black">
                <thead>
                    <tr class="border-b border-black">
                        <th class="px-4 py-3 text-left font-semibold">Referencia</th>
                        <th class="px-4 py-3 text-left font-semibold">Documento</th>
                        <th class="px-4 py-3 text-left font-semibold">Causal</th>
                        <th class="px-4 py-3 text-left font-semibold">Recogida</th>
                        <th class="px-4 py-3 text-left font-semibold">Adjuntos</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($pqrs->productos as $p)
                        <tr class="border-b border-black hover:bg-black hover:text-white transition">

                            <td class="px-4 py-3 font-medium">
                                {{ $p->referencia ?? '—' }}
                                <div class="text-xs">
                                    {{ $p->descripcion_ref ?? '' }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                {{ $p->tipo_docto ?? '—' }}-{{ $p->nro_docto ?? '—' }}
                                <div class="text-xs">
                                    {{ $p->fecha ?? '' }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                {{ $p->causal?->nombre ?? $p->causal_id ?? '—' }}
                            </td>

                            <td class="px-4 py-3 font-semibold">
                                {{ (int)($p->requiere_recogida ?? 0) === 1 ? 'SI' : 'NO' }}
                            </td>

                            <td class="px-4 py-3">
                                @php $adj = $p->adjuntos ?? collect(); @endphp

                                @if($adj->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($adj as $a)
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium">
                                                    {{ $a->original_name ?? 'archivo' }}
                                                </span>

                                                @if(!empty($a->path))
                                                    <a href="{{ asset('storage/'.$a->path) }}"
                                                       target="_blank"
                                                       class="underline font-semibold">
                                                        Ver
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    —
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @else
        <div class="p-4 text-black font-medium">
            No hay productos asociados.
        </div>
    @endif

</div>


{{-- ================= ORM ABAJO ================= --}}
<div class="border border-black bg-white p-5 space-y-4">

    <h2 class="font-bold text-lg border-b border-black pb-2 text-black">
        Orden de Recogida (ORM)
    </h2>

    @if($pqrs->orm)

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-black">

            <div><strong>Estado:</strong> {{ $pqrs->orm->estado }}</div>

            <div>
                <strong>Transportadora:</strong>
                {{ $pqrs->orm->transportadora?->razon_social ?? '—' }}
            </div>

            <div>
                <strong>Fecha programada:</strong>
                {{ optional($pqrs->orm->fecha_recogida_programada)->format('Y-m-d') ?? '—' }}
            </div>

            <div>
                <strong>Fecha recibido:</strong>
                {{ optional($pqrs->orm->fecha_recibido_transportadora)->format('Y-m-d H:i') ?? '—' }}
            </div>

            <div><strong>LPS:</strong> {{ $pqrs->orm->lps ?? '—' }}</div>
            <div><strong>Cajas:</strong> {{ $pqrs->orm->cajas ?? '—' }}</div>

            <div><strong>Peso:</strong> {{ $pqrs->orm->peso ?? '—' }}</div>
            <div><strong>Valor declarado:</strong> {{ $pqrs->orm->valor_declarado ?? '—' }}</div>

            <div>
                <strong>Usuario recibe:</strong>
                {{ $pqrs->orm->usuarioRecibe?->name ?? '—' }}
            </div>

            <div class="md:col-span-3">
                <strong>Dirección:</strong>
                {{ $pqrs->orm->direccion ?? '—' }} —
                {{ $pqrs->orm->ciudad ?? '—' }} —
                {{ $pqrs->orm->departamento ?? '—' }}
            </div>

            <div class="md:col-span-3">
                <strong>Comentarios:</strong>
                {{ $pqrs->orm->comentarios ?? '—' }}
            </div>

        </div>

    @else
        <p class="text-black font-medium">
            No tiene ORM asociada.
        </p>
    @endif

</div>
</div>