<div class="p-8 bg-white text-black space-y-10">

    {{-- HEADER --}}
<div class="flex flex-col gap-4">
    <a href="{{ route('pqrs.inicio') }}"
       class="w-full inline-flex justify-center items-center rounded-lg bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700 transition shadow-sm">
        ← Volver
    </a>
</div>


<section class="space-y-4">
    <div>
        <h1 class="text-3xl font-bold leading-tight">
            PQRS #{{ $pqrs->id }} por {{ strtoupper($pqrs->tipo_pqrs) }}
        </h1>

        @if(strtolower((string)($pqrs->estado ?? '')) === 'cerrado')
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                Esta PQRS está cerrada. Ya no se permiten aprobaciones o rechazos de productos ni de ORM.
            </div>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-300 bg-white">
        <table class="min-w-full text-sm text-black">
            <tbody class="divide-y divide-zinc-200">
                <tr>
                    <td class="px-5 py-4 align-top">
                        <div class="font-bold uppercase">Estado</div>
                        <div class="mt-1 uppercase">{{ $pqrs->estado ?? '—' }}</div>
                    </td>

                   <td class="px-5 py-4 align-top">
                        <div class="text-sm font-bold uppercase"># DE PRODUCTOS</div>
                        <div class="mt-2 text-lg">{{ $pqrs->productos?->count() ?? 0 }}</div>
                    </td>

                    <td class="px-5 py-4 align-top">
                        <div class="text-sm font-bold uppercase">TIENE ORM</div>
                        <div class="mt-2 text-lg">{{ $pqrs->orm ? 'SI' : 'NO' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="px-5 py-4 align-top">
                        <div class="font-bold "> NIT</div>
                        <div class="mt-1 uppercase">{{ $pqrs->nit }}</div>
                    </td>

                    <td class="px-5 py-4 align-top" colspan="2">
                        <div class="font-bold uppercase">CLIENTE</div>
                        <div class="mt-1 uppercase">{{ $pqrs->razon_social }}</div>
                    </td>
                </tr>

                <tr>
                    <td class="px-5 py-4 align-top">
                        <div class="font-bold">Teléfono</div>
                        <div class="mt-1">{{ $pqrs->telefono ?? '—' }}</div>
                    </td>
                    <td class="px-5 py-4 align-top">
                        <div class="font-bold">Correo cliente</div>
                        <div class="mt-1 break-all">{{ $pqrs->correo_cliente ?? '—' }}</div>
                    </td>
                    <td class="px-5 py-4 align-top">
                        <div class="font-bold">Dirección</div>
                        <div class="mt-1">
                            {{ $pqrs->direccion ?? '—' }} — {{ $pqrs->ciudad ?? '—' }} — {{ $pqrs->departamento ?? '—' }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-5 py-4 align-top">
                        <div class="font-bold">Fecha creación</div>
                        <div class="mt-1">{{ optional($pqrs->created_at)->format('Y-m-d H:i') ?? '—' }}</div>
                    </td>

                    <td class="px-5 py-4 align-top">
                        <div class="font-bold">Fecha revisado</div>
                        <div class="mt-1">{{ optional($pqrs->fecha_revisado)->format('Y-m-d H:i') ?? '—' }}</div>
                    </td>

                    <td class="px-5 py-4 align-top">
                        <div class="font-bold">Fecha cierre</div>
                        <div class="mt-1">{{ optional($pqrs->fecha_cierre)->format('Y-m-d H:i') ?? '—' }}</div>
                    </td>

                    {{-- <td class="px-5 py-4 align-top">
                        <div class="font-bold">Número ORM</div>
                        <div class="mt-1">{{ $pqrs->numero_orm ?? '—' }}</div>
                    </td> --}}
                </tr>

                <tr>
                    <td class="px-5 py-4 align-top">
                        <div class="font-bold">Asesor</div>
                        <div class="mt-1">{{ $pqrs->nombre_asesor ?? '—' }}</div>
                    </td>

                    <td class="px-5 py-4 align-top">
                        <div class="font-bold">Código asesor</div>
                        <div class="mt-1">{{ $pqrs->cod_asesor ?? '—' }}</div>
                    </td>

                    <td class="px-5 py-4 align-top">
                        <div class="font-bold">Correo asesor</div>
                        <div class="mt-1 break-all">{{ $pqrs->correo_asesor ?? '—' }}</div>
                    </td>

                </tr>
            </tbody>
        </table>
    </div>
</section>


<br>
    @php
        $facturasUnicas = $this->facturasUnicas();
    @endphp
    <br>

    <section class="space-y-5">
        <h1 class="text-3xl font-bold leading-tight">
            FACTURAS RELACIONADAS
        </h1>

        <div class="rounded-xl border border-zinc-300 bg-white overflow-hidden">
            <div class="p-5">
                @if($facturasUnicas->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($facturasUnicas as $fact)
                            <div class="rounded-lg border border-zinc-200 p-4 text-sm bg-zinc-50">
                                <div class="font-semibold text-zinc-800 mb-1">
                                    {{ $fact['tipo_docto'] ?: 'DOC' }} - {{ $fact['nro_docto'] ?: '—' }}
                                </div>

                                <div class="text-zinc-500">
                                    Fecha:
                                    {{ $fact['fecha'] ? \Carbon\Carbon::parse($fact['fecha'])->format('Y-m-d') : '—' }}
                                </div>
                                <a
                                    href="{{ route('admin.facturas.descargar', [
                                        'prefijo' => $fact['tipo_docto'],
                                        'consecutivo' => $fact['nro_docto']
                                    ]) }}"
                                    target="_blank"
                                    class="inline-flex items-center rounded-lg bg-zinc-900 px-3 py-2 text-xs font-semibold text-white hover:bg-black">
                                    Descargar factura
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-zinc-400 text-sm">Sin facturas relacionadas</span>
                @endif
            </div>
        </div>
    </section>

@if($pqrs->tipo_pqrs == 'factura')

{{-- <section class="space-y-5">
    <h1 class="text-3xl font-bold leading-tight">
        ADJUNTOS GENERALES
    </h1>

    <div class="rounded-xl border border-zinc-300 bg-white overflow-hidden">
        <div class="p-5">
            @if($pqrs->adjuntos && $pqrs->adjuntos->count())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($pqrs->adjuntos as $adj)
                        <div class="rounded-lg border border-zinc-200 p-3 text-sm bg-zinc-50">
                            <div class="font-semibold text-zinc-800 mb-1">
                                Adjunto ID: {{ $adj->id }}
                            </div>

                            <div class="text-zinc-500 break-all mb-3">
                                {{ $adj->original_name }}
                            </div>

                            <div class="text-xs text-zinc-500 mb-3">
                                Origen: {{ $adj->origen ?? 'factura' }}
                            </div>

                            @if(str_starts_with($adj->mime, 'image/'))
                                <a href="{{ asset('storage/' . $adj->path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $adj->path) }}"
                                         alt="Adjunto"
                                         class="h-32 w-32 rounded-lg object-cover border border-zinc-200">
                                </a>
                            @else
                                <a href="{{ asset('storage/' . $adj->path) }}"
                                   target="_blank"
                                   class="inline-flex items-center rounded-lg bg-zinc-900 px-3 py-2 text-xs font-semibold text-white hover:bg-black">
                                    Ver archivo
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <span class="text-zinc-400 text-sm">Sin adjuntos generales</span>
            @endif
        </div>
    </div>
</section> --}}

@endif

<section class="space-y-5">

    <br>
    @if(strtolower((string)($pqrs->estado ?? '')) !== 'cerrado')
        <div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
            <button
                type="button"
                wire:click="aprobarProductosMasivo"
                style="background:#15803d; color:#fff; border:none; border-radius:10px; padding:12px 18px; font-size:14px; font-weight:700; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.08);">
                ✔ Aprobar productos seleccionados
            </button>

            <button
                type="button"
                wire:click="rechazarProductosMasivo"
                style="background:#b91c1c; color:#fff; border:none; border-radius:10px; padding:12px 18px; font-size:14px; font-weight:700; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.08);">
                ✖ Rechazar productos seleccionados
            </button>
        </div>
    @endif

    @if(strtolower((string)($pqrs->estado ?? '')) !== 'cerrado' && $pqrs->orm)

        <div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
            <button
                type="button"
                wire:click="aprobarOrmMasivo"
                style="background:#1d4ed8; color:#fff; border:none; border-radius:10px; padding:12px 18px; font-size:14px; font-weight:700; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.08);">
                🚚 Aprobar ORM seleccionadas
            </button>

            <button
                type="button"
                wire:click="rechazarOrmMasivo"
                style="background:#c2410c; color:#fff; border:none; border-radius:10px; padding:12px 18px; font-size:14px; font-weight:700; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.08);">
                🚫 Rechazar ORM seleccionadas
            </button>
        </div>
    @endif

        <h1 class="text-3xl font-bold leading-tight">
            PRODUCTOS ({{ $pqrs->productos?->count() ?? 0 }})
        </h1>

        <div class="rounded-xl border border-zinc-300 bg-white overflow-hidden">
            <div class="overflow-x-auto">

                @php
                    function iconoEstadoPqrs($estado) {
                        return match(strtolower(trim((string)$estado))) {
                            'pendiente' => '⏳',
                            'aprobado', 'aprobada' => '✅',
                            'rechazado', 'rechazada' => '❌',
                            default => '—',
                        };
                    }
                @endphp
                <table class="min-w-[1500px] w-full text-sm text-black">
                    <thead class="bg-zinc-100 border-b border-zinc-300">
                        <tr class="text-left">
                            <th class="px-3 py-3 font-bold whitespace-nowrap text-center" title="Seleccionar producto">
                                <div class="flex flex-col items-center gap-1">
                                    <span>☑</span>
                                        @if(strtolower((string)($pqrs->estado ?? '')) !== 'cerrado')
                                            <input
                                                type="checkbox"
                                                wire:change="toggleTodosProductos($event.target.checked)">
                                        @endif
                                </div>
                            </th>

                            <th class="px-3 py-3 font-bold whitespace-nowrap text-center" title="Seleccionar ORM">
                                <div class="flex flex-col items-center gap-1">
                                    <span>🚚</span>
                                        @if(strtolower((string)($pqrs->estado ?? '')) !== 'cerrado')
                                            <input
                                                type="checkbox"
                                                wire:change="toggleTodosOrm($event.target.checked)">
                                        @endif
                                </div>
                            </th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap text-center" title="Estado producto">📦</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap text-center" title="Estado ORM">🚚</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap">Ref</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap">Descripción</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap text-center">Und</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap text-right">Unit</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap text-right">Bruto</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap text-right">Imp</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap text-right">Neto</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap">Factura</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap">Causal</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap text-center">Rec</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap">Notas</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap">Opciones</th>
                            <th class="px-3 py-3 font-bold whitespace-nowrap">Adjuntos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($pqrs->productos as $p)
                            <tr class="align-top hover:bg-zinc-50">
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if($this->puedeRevisarProducto($p) && strtolower((string)($pqrs->estado ?? '')) !== 'cerrado')
                                        <input
                                            type="checkbox"
                                            value="{{ $p->id }}"
                                            wire:model="seleccionProductos">
                                    @endif
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if((int)$p->requiere_recogida === 1 && $this->puedeRevisarProducto($p) && strtolower((string)($pqrs->estado ?? '')) !== 'cerrado')
                                        <input
                                            type="checkbox"
                                            value="{{ $p->id }}"
                                            wire:model="seleccionOrm">
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-lg" title="Estado producto: {{ $p->estado ?? 'pendiente' }}">
                                    {{ iconoEstadoPqrs($p->estado ?? 'pendiente') }}
                                </td>

                                <td class="px-3 py-4 whitespace-nowrap text-center text-lg" title="Estado ORM: {{ $p->estado_orm ?? 'pendiente' }}">
                                    @if((int)($p->requiere_recogida ?? 0) === 1)
                                        {{ iconoEstadoPqrs($p->estado_orm ?? 'pendiente') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap font-medium">
                                    {{ $p->referencia ?? '—' }}
                                </td>

                                <td class="px-4 py-4 min-w-[260px]">
                                    {{ $p->descripcion_ref ?? 'Sin descripción' }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    {{ (int)($p->unidades_solicitadas ?? 0) }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    {{ number_format((float)($p->precio_unitario ?? 0), 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    {{ number_format((float)($p->valor_bruto ?? 0), 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    {{ number_format((float)($p->valor_imp ?? 0), 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-right font-semibold">
                                    {{ number_format((float)($p->valor_neto ?? 0), 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    {{ $p->tipo_docto ?? '—' }}-{{ $p->nro_docto ?? '—' }}
                                </td>

                                <td class="px-4 py-4 min-w-[220px]">
                                    {{ $p->causal?->nombre ?? $p->causal_id ?? '—' }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    {{ (int)($p->requiere_recogida ?? 0) === 1 ? 'SI' : 'NO' }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    {{ $p->notas ??  '-' }}
                                </td>
                                <td class="px-4 py-4 min-w-[240px]">
                                    <div class="flex flex-col gap-2">

                                        @if(
                                            strtolower((string)($pqrs->estado ?? '')) !== 'cerrado'
                                            && $this->puedeRevisarProducto($p)
                                        )
                                            <div class="flex flex-wrap gap-2">
                                                <button
                                                    wire:click="aprobarProducto({{ $p->id }})"
                                                    type="button"
                                                    class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700 transition">
                                                    ✔ Aprobar
                                                </button>

                                                <button
                                                    wire:click="rechazarProducto({{ $p->id }})"
                                                    type="button"
                                                    class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700 transition">
                                                    ✖ Rechazar
                                                </button>
                                            </div>

                                            @if((int)$p->requiere_recogida === 1)
                                                <div class="flex flex-wrap gap-2">
                                                    <button
                                                        wire:click="aprobarOrmProducto({{ $p->id }})"
                                                        type="button"
                                                        class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition">
                                                        🚚 ORM OK
                                                    </button>

                                                    <button
                                                        wire:click="rechazarOrmProducto({{ $p->id }})"
                                                        type="button"
                                                        class="rounded-lg px-3 py-2 text-xs font-semibold text-white transition"
                                                        style="background-color: #d97706;">
                                                        🚫 ORM NO
                                                    </button>
                                                </div>
                                            @endif
                                        @elseif(strtolower((string)($pqrs->estado ?? '')) === 'cerrado')
                                            <div class="text-xs font-semibold text-zinc-500">
                                                PQRS cerrada
                                            </div>
                                        @else
                                            <div class="text-xs font-semibold text-zinc-400">
                                                Sin permiso para revisar
                                            </div>
                                        @endif

                                        <div class="text-[11px] text-zinc-500 pt-1">
                                            Producto: <span class="font-semibold">{{ $p->estado ?? 'pendiente' }}</span>
                                            @if((int)$p->requiere_recogida === 1)
                                                <br>
                                                ORM: <span class="font-semibold">{{ $p->estado_orm ?? 'pendiente' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 min-w-[260px]">
                                    @if($p->adjuntos->count())
                                        <div class="flex flex-col gap-3">
                                            @foreach($p->adjuntos as $adj)
                                                <div class="rounded-lg border border-zinc-200 p-2 text-xs">
                                                    <div class="font-semibold text-zinc-800">
                                                        Producto ID: {{ $p->id }} | Adjunto ID: {{ $adj->id }}
                                                    </div>

                                                    <div class="text-zinc-500 break-all mb-2">
                                                        {{ $adj->original_name }}
                                                    </div>

                                                    @if(str_starts_with($adj->mime, 'image/'))
                                                        <a href="{{ asset('storage/' . $adj->path) }}" target="_blank">
                                                            <img src="{{ asset('storage/' . $adj->path) }}"
                                                                alt="Adjunto"
                                                                class="h-24 w-24 rounded-lg object-cover border border-zinc-200">
                                                        </a>
                                                    @else
                                                        <a href="{{ asset('storage/' . $adj->path) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center rounded-lg bg-zinc-900 px-3 py-2 text-xs font-semibold text-white hover:bg-black">
                                                            Ver archivo
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-zinc-400 text-xs">Sin adjuntos</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="18" class="px-4 py-8 text-center text-sm text-zinc-500">
                                    No hay productos asociados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    {{-- ORM --}}

    <br>
    <section class="space-y-4">
        @if($pqrs->orm)
            <div>
                <h1 class="text-3xl font-bold leading-tight">
                    ORDEN DE RECOGIDA (ORM) #{{ $pqrs->orm->id ?? '—' }}
                </h1>
            </div>

            <div class="overflow-hidden rounded-xl border border-zinc-300 bg-white">
                <table class="min-w-full text-sm text-black">
                    <tbody class="divide-y divide-zinc-200">
                        <tr>
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold">ESTADO</div>
                                <div class="mt-1">{{ $pqrs->orm->estado ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-4 align-top" colspan="2">
                                <div class="text-sm font-bold uppercase">CLIENTE</div>
                                <div class="mt-2 text-lg">{{ $pqrs->orm->nit }} - {{ $pqrs->orm->razon_social }}</div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="text-sm font-bold uppercase">BOTONES</div>

                                <div class="mt-3 flex flex-col md:flex-row flex-wrap gap-2">
                                    <button
                                        type="button"
                                        wire:click="abrirModalOrm"
                                        class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-xs font-semibold text-white hover:bg-black transition">
                                        Editar ORM
                                    </button>

                                    @if(strtolower((string)($pqrs->estado ?? '')) !== 'cerrado')
                                        <button
                                            type="button"
                                            wire:click="eliminarOrm"
                                            wire:confirm="¿Seguro que deseas eliminar esta ORM? Esto limpiará también la información ORM en productos y PQRS."
                                            class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-xs font-semibold shadow transition"
                                            style="background-color:#dc2626; color:#fff;">
                                            Eliminar
                                        </button>

                                        @if($pqrs->orm && strtolower((string)($pqrs->orm->estado ?? '')) === 'programada')
                                            <button
                                                type="button"
                                                wire:click="marcarRecogidaTransportadora"
                                                wire:confirm="¿Confirmas marcar esta ORM como recogida por transportadora?"
                                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-blue-700 transition">
                                                Marcar recogida transportadora
                                            </button>
                                        @endif

                                        @if($pqrs->orm && strtolower((string)($pqrs->orm->estado ?? '')) === 'recogida_transportadora')
                                            <button
                                                type="button"
                                                wire:click="marcarEnBodega"
                                                wire:confirm="¿Confirmas marcar esta ORM como en bodega?"
                                                class="inline-flex items-center justify-center rounded-lg bg-orange-600 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-orange-700 transition">
                                                Marcar en bodega
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-5 py-4 align-top" colspan="3">
                                <div class="font-bold">DIRECCIÓN</div>
                                <div class="mt-1">{{ $pqrs->orm->direccion ?? '—' }} ( {{ $pqrs->orm->ciudad ?? '—' }} - {{ $pqrs->orm->departamento ?? '—' }} )</div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold">TELEFONO</div>
                                <div class="mt-1">{{ $pqrs->orm->telefono ?? '—' }} </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold">LIOS</div>
                                <div class="mt-1">{{ $pqrs->orm->lios ?? '—' }} </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold">CAJAS</div>
                                <div class="mt-1">{{ $pqrs->orm->cajas ?? '—' }} </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold">PESO</div>
                                <div class="mt-1">{{ $pqrs->orm->peso ?? '—' }} </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold">VALOR DECLARADO</div>
                                <div class="mt-1">{{ number_format($pqrs->orm->valor_declarado) ?? '—' }} </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-5 py-4 align-top" colspan="2">
                                <div class="font-bold">TRANSPORTADORA</div>
                                <div class="mt-1">{{ $pqrs->orm->transportadora?->razon_social  ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold">GUIA</div>
                                <div class="mt-1">{{ $pqrs->orm->numero_guia ?? '—' }} </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold">FECHA RECOGIDA PROGRAMADA</div>
                                <div class="mt-1">{{ $pqrs->orm->fecha_recogida_programada ?? '—' }} </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-5 py-4 align-top" colspan="2">
                                <div class="font-bold">FECHA RECOGIDA TRANSPORTADORA</div>
                                <div class="mt-1">
                                    {{ optional($pqrs->orm->fecha_recogida_transportadora)->format('Y-m-d H:i') ?? '—' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top" colspan="2">
                                <div class="font-bold">MARCADO POR</div>
                                <div class="mt-1">
                                    {{ $pqrs->orm->usuarioMarcaRecogidaTransportadora?->name ?? '—' }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-5 py-4 align-top" colspan="2">
                                <div class="font-bold">FECHA LLEGADA A BODEGA</div>
                                <div class="mt-1">
                                    {{ optional($pqrs->orm->fecha_llegada_bodega)->format('Y-m-d H:i') ?? '—' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top" colspan="2">
                                <div class="font-bold">RECIBIDO POR</div>
                                <div class="mt-1">
                                    {{ $pqrs->orm->usuarioRecibe?->name ?? '—' }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-xl border border-zinc-300 bg-white px-6 py-8 text-sm text-zinc-500">
                Esta PQRS no tiene ORM asociada.
            </div>
        @endif
    </section>

@if ( $pqrs->estado == 'revision')

<div class="rounded-xl border border-zinc-300 bg-white p-4 text-sm space-y-2">
    <div class="font-bold">Validación de cierre</div>

    <div>
        {{ $pqrs->productos->contains(fn($p) => strtolower((string)($p->estado ?? 'pendiente')) === 'pendiente') ? '❌' : '✅' }}
        Todos los productos revisados
    </div>

    <div>
        {{
            $pqrs->productos->where('requiere_recogida', 1)->contains(fn($p) => strtolower((string)($p->estado_orm ?? 'pendiente')) === 'pendiente')
            ? '❌' : '✅'
        }}
        Todas las ORM revisadas
    </div>

    <div>
        {{ (!$pqrs->orm || strtolower((string)$pqrs->orm->estado) === 'en_bodega') ? '✅' : '❌' }}
        ORM en bodega
    </div>
</div>

@endif

<br>
@if ( $pqrs->estado == 'cerrado')
<section class="grid grid-cols-1 gap-10 md:grid-cols-2">

    <div>
        <h1 class="text-3xl font-bold leading-tight">
        INFORMACIÓN DEL CIERRE
        </h1>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-300 bg-white">
        <table class="min-w-full text-sm text-black">
            <tbody class="divide-y divide-zinc-200">
                <tr>
                    <td class="px-5 py-4 align-top">
                        <div class="font-bold uppercase">Tipo de acuerdo:</div>
                        <div class="mt-1 uppercase">{{ $pqrs->tipo_acuerdo ?? '—' }}</div>
                    </td>

                   <td class="px-5 py-4 align-top">
                        <div class="text-sm font-bold uppercase">Valor:</div>
                        <div class="mt-2 text-lg">{{ $pqrs->valor_acuerdo ?? '—' }}</div>
                    </td>

                    <td class="px-5 py-4 align-top">
                        <div class="text-sm font-bold uppercase">Nota crédito:</div>
                        <div class="mt-2 text-lg"{{ $pqrs->nota_acuerdo ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="px-5 py-4 align-top" colspan="3">
                        <div class="text-sm font-bold uppercase"> Comentario de cierre:</div>
                        <div class="mt-1 uppercase">{{ $pqrs->comentario_cierre ?? '—' }}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

@endif


@php
    $validacionCierre = $this->validacionCierrePqrs();
@endphp

@if($validacionCierre['puede'])
    <div style="margin-top:16px;">
        <button
            type="button"
            wire:click="abrirModalCerrar"
            style="background:#047857; color:#fff; border:none; border-radius:10px; padding:12px 18px; font-size:14px; font-weight:700; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.08);">
            Cerrar PQRS
        </button>
    </div>
@endif

@if($showModalOrm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-6xl rounded-2xl bg-white shadow-2xl overflow-hidden">
            
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 class="text-lg font-bold text-zinc-900">Editar ORM</h3>
                <button wire:click="cerrarModalOrm" class="text-zinc-500 hover:text-zinc-800 text-2xl leading-none">
                    ✕
                </button>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-sm font-semibold">Transportadora</label>
                        <select wire:model="transportadora_id"
                            class="w-full rounded-lg border border-zinc-300 px-3 py-2">
                            <option value="">Seleccione</option>
                            @foreach($transportadoras as $t)
                                <option value="{{ $t->id }}">{{ $t->razon_social }}</option>
                            @endforeach
                        </select>
                        @error('transportadora_id') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">Número guía</label>
                        <input type="text" wire:model="numero_guia"
                            class="w-full rounded-lg border border-zinc-300 px-3 py-2">
                        @error('numero_guia') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">Fecha recogida programada</label>
                        <input type="datetime-local" wire:model="fecha_recogida_programada"
                            class="w-full rounded-lg border border-zinc-300 px-3 py-2">
                        @error('fecha_recogida_programada') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-sm font-semibold">Cajas</label>
                        <input type="number" step="1" wire:model="cajas"
                            class="w-full rounded-lg border border-zinc-300 px-3 py-2">
                        @error('cajas') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">Líos</label>
                        <input type="number" step="1" wire:model="lios"
                            class="w-full rounded-lg border border-zinc-300 px-3 py-2">
                        @error('lios') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">Peso</label>
                        <input type="number" step="0.01" wire:model="peso"
                            class="w-full rounded-lg border border-zinc-300 px-3 py-2">
                        @error('peso') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">Comentarios</label>
                    <textarea wire:model="comentarios" rows="4"
                        class="w-full rounded-lg border border-zinc-300 px-3 py-2"></textarea>
                    @error('comentarios') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="rounded-lg bg-zinc-50 p-4 text-sm">
                    <span class="font-semibold">Valor declarado calculado:</span>
                    {{ number_format((float) $pqrs->productos()->where('estado', 'aprobado')->sum('valor_neto'), 0, ',', '.') }}
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t px-6 py-4">
                <button wire:click="cerrarModalOrm"
                    class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                    Cancelar
                </button>

                <button wire:click="guardarOrm"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Guardar ORM
                </button>
            </div>
        </div>
    </div>
@endif

@if($showModalCerrar)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h2 class="text-xl font-bold">Cerrar PQRS</h2>
                <button type="button" wire:click="cerrarModalCerrar" class="text-zinc-500 hover:text-black">
                    ✕
                </button>
            </div>

            <div class="space-y-5 px-6 py-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-zinc-700">Tipo de acuerdo</label>
                    <select wire:model.live="tipo_acuerdo" class="w-full rounded-lg border border-zinc-300 px-3 py-2">
                        <option value="">Seleccione</option>
                        <option value="nota">Nota</option>
                        <option value="no_aplica">No aplica</option>
                        <option value="atencion_comercial">Atención comercial</option>
                    </select>
                    @error('tipo_acuerdo')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                @if($tipo_acuerdo === 'nota')
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-zinc-700">Número de nota</label>
                            <input
                                type="text"
                                wire:model="nota_acuerdo"
                                class="w-full rounded-lg border border-zinc-300 px-3 py-2">
                            @error('nota_acuerdo')
                                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-zinc-700">Valor</label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model="valor_acuerdo"
                                class="w-full rounded-lg border border-zinc-300 px-3 py-2">
                            @error('valor_acuerdo')
                                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif

                <div>
                    <label class="mb-2 block text-sm font-semibold text-zinc-700">Comentario de cierre</label>
                    <textarea
                        wire:model="comentario_cierre"
                        rows="4"
                        class="w-full rounded-lg border border-zinc-300 px-3 py-2"></textarea>
                    @error('comentario_cierre')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t px-6 py-4">
                <button
                    type="button"
                    wire:click="cerrarModalCerrar"
                    class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                    Cancelar
                </button>

                <button
                    type="button"
                    wire:click="guardarCierre"
                    style="background:#047857; color:#fff; border:none; border-radius:10px; padding:12px 18px; font-size:14px; font-weight:700; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.08);">
                    Guardar cierre
                </button>
            </div>
        </div>
    </div>
@endif



</div>