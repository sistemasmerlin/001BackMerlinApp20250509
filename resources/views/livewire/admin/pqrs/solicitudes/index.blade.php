<div class="p-4 sm:p-6 space-y-4">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900">PQRS - Solicitudes</h1>
            <p class="text-sm text-zinc-500">Últimas solicitudes, filtros y búsqueda rápida.</p>
        </div>

        <div class="flex gap-2">
            @if($this->tieneFiltros)
                <button wire:click="limpiar"
                    class="inline-flex items-center rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">
                    Limpiar filtros
                </button>
            @endif
        </div>
    </div>

    {{-- Filtros --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Buscar cliente --}}
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Cliente (NIT o Razón social)</label>
                <input type="text"
                    wire:model.live.debounce.400ms="q"
                    placeholder="Ej: 900... o MERLIN..."
                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none" />
            </div>

            {{-- Asesor --}}
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Asesor (código o nombre)</label>
                <input type="text"
                    wire:model.live.debounce.400ms="asesor"
                    placeholder="Ej: 03 o Juan..."
                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none" />
            </div>

            {{-- Fecha inicio --}}
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Desde</label>
                <input type="date"
                    wire:model.live="fechaInicio"
                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none" />
            </div>

            {{-- Fecha fin --}}
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Hasta</label>
                <input type="date"
                    wire:model.live="fechaFin"
                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none" />
            </div>

        </div>

        <div class="mt-3 flex items-center justify-between text-xs text-zinc-500">
            <div>
                Mostrando <span class="font-medium text-zinc-700">{{ $rows->count() }}</span> de
                <span class="font-medium text-zinc-700">{{ $rows->total() }}</span>
            </div>

            <div wire:loading class="text-zinc-600">
                Cargando...
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 text-zinc-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">ID</th>
                        <th class="px-4 py-3 text-left font-semibold">Extemporanea</th>
                        <th class="px-4 py-3 text-left font-semibold">Cliente</th>
                        <th class="px-4 py-3 text-left font-semibold">Ciudad</th>
                        <th class="px-4 py-3 text-left font-semibold">Asesor</th>
                        <th class="px-4 py-3 text-left font-semibold">Estado</th>
                        <th class="px-4 py-3 text-left font-semibold">Creación</th>
                        <th class="px-4 py-3 text-left font-semibold">ORM</th>
                        <th class="px-4 py-3 text-right font-semibold">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100">
                    @forelse($rows as $r)
                        <tr class="hover:bg-zinc-50">
                            <td class="px-4 py-3 font-medium text-zinc-900">#{{ $r->id }}</td>

                            <td class="px-4 py-3 font-medium text-zinc-900">
                                
                                {{ $r->enviado_otro_usuario ? 'SI' : 'NO' }}
                                
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-medium text-zinc-900">
                                    {{ $r->razon_social ?? '—' }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    NIT: {{ $r->nit ?? '—' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-zinc-700">
                                {{ $r->ciudad ?? '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="text-zinc-900 font-medium">
                                    {{ $r->nombre_asesor ?? '—' }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $r->cod_asesor ? 'Cod: '.$r->cod_asesor : '—' }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">
                                    {{ $r->estado ?? '—' }}
                                </span>
                            </td> 

                            <td class="px-4 py-3 text-zinc-700">
                                {{ optional($r->fecha_creacion)->format('Y-m-d H:i') ?? optional($r->created_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-zinc-700">
                                {{ $r->orm?->id ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{-- Por ahora solo botón placeholder --}}
                                <a href="{{ route('pqrs.detalle', $r->id) }}"
                                class="inline-flex items-center rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-zinc-500">
                                No hay PQRS para mostrar con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="border-t border-zinc-200 px-4 py-3">
            {{ $rows->links() }}
        </div>
    </div>

</div>
