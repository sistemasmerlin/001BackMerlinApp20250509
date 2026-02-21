<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Órdenes de Recogida (ORM)</h1>
            <p class="text-sm text-gray-500">Crea y gestiona recogidas asociadas a PQRS.</p>
        </div>

        <button wire:click="crear"
            class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">
            + Nueva ORM
        </button>
    </div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="rounded-xl border bg-white p-3 md:col-span-2">
            <label class="text-xs font-medium text-gray-500">Buscar</label>
            <input wire:model.live="q" type="text" placeholder="NIT / razón social / id pqrs / ciudad"
                class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900" />
        </div>

        <div class="rounded-xl border bg-white p-3">
            <label class="text-xs font-medium text-gray-500">Estado</label>
            <select wire:model.live="estado"
                class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                <option value="">Todos</option>
                <option value="creada">Creada</option>
                <option value="en_tramite">En trámite</option>
                <option value="cerrada">Cerrada</option>
            </select>
        </div>

        <div class="rounded-xl border bg-white p-3 flex items-end">
            <button wire:click="limpiarFiltros"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Limpiar
            </button>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-2xl border bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">ORM</th>
                        <th class="px-4 py-3 text-left">PQRS</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-left">Ciudad</th>
                        <th class="px-4 py-3 text-left">Transportadora</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($rows as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-900">#{{ $r->id }}</td>

                            <td class="px-4 py-3 text-gray-700">
                                <span class="rounded-lg bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                                    PQRS {{ $r->pqrs_id }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $r->razon_social }}</div>
                                <div class="text-xs text-gray-500">{{ $r->nit }}</div>
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $r->ciudad }} <span class="text-xs text-gray-400">{{ $r->departamento }}</span>
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $r->transportadora?->razon_social ?? '—' }}
                            </td>

                            <td class="px-4 py-3">
                                @php
                                    $cls = match($r->estado){
                                        'creada' => 'bg-gray-100 text-gray-700',
                                        'en_tramite' => 'bg-amber-100 text-amber-800',
                                        'cerrada' => 'bg-emerald-100 text-emerald-800',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $cls }}">
                                    {{ str_replace('_',' ', $r->estado) }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="editar({{ $r->id }})"
                                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                        Editar
                                    </button>

                                    <button wire:click="cambiarEstado({{ $r->id }}, 'en_tramite')"
                                        class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                                        En trámite
                                    </button>

                                    <button wire:click="cambiarEstado({{ $r->id }}, 'cerrada')"
                                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">
                                        Cerrar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                No hay ORMs.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t bg-white px-4 py-3">
            {{ $rows->links() }}
        </div>
    </div>

    {{-- Modal Crear/Editar ORM --}}
    @if ($modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" wire:click="cerrarModal"></div>

            <div class="relative w-full max-w-4xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            {{ $editId ? 'Editar ORM' : 'Nueva ORM' }}
                        </h2>
                        <p class="text-sm text-gray-500">Asociada a una PQRS.</p>
                    </div>
                    <button wire:click="cerrarModal" class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100">✕</button>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">

                    <div>
                        <label class="text-xs font-medium text-gray-600">PQRS ID</label>
                        <input wire:model.defer="pqrs_id" type="number"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('pqrs_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Estado</label>
                        <select wire:model.defer="estado_form"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="creada">Creada</option>
                            <option value="en_tramite">En trámite</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                        @error('estado_form') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Transportadora</label>
                        <select wire:model.defer="transportadora_id"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">—</option>
                            @foreach($transportadoras as $t)
                                <option value="{{ $t->id }}">{{ $t->razon_social }} ({{ $t->nit }})</option>
                            @endforeach
                        </select>
                        @error('transportadora_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-medium text-gray-600">Razón social</label>
                        <input wire:model.defer="razon_social" type="text"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">NIT</label>
                        <input wire:model.defer="nit" type="text"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-medium text-gray-600">Dirección</label>
                        <input wire:model.defer="direccion" type="text"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Teléfono</label>
                        <input wire:model.defer="telefono" type="text"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Departamento</label>
                        <input wire:model.defer="departamento" type="text"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Ciudad</label>
                        <input wire:model.defer="ciudad" type="text"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">LPS</label>
                        <input wire:model.defer="lps" type="number"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Cajas</label>
                        <input wire:model.defer="cajas" type="number"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Peso</label>
                        <input wire:model.defer="peso" type="number" step="0.01"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Valor declarado</label>
                        <input wire:model.defer="valor_declarado" type="number" step="0.01"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Fecha recogida programada</label>
                        <input wire:model.defer="fecha_recogida_programada" type="date"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600">Fecha recibido transportadora</label>
                        <input wire:model.defer="fecha_recibido_transportadora" type="datetime-local"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div class="md:col-span-3">
                        <label class="text-xs font-medium text-gray-600">Comentarios</label>
                        <textarea wire:model.defer="comentarios" rows="3"
                            class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-gray-900 focus:ring-gray-900"></textarea>
                    </div>

                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="cerrarModal"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                        class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>