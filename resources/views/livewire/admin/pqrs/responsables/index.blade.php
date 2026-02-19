<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Responsables PQRS</h1>
            <p class="text-sm text-gray-500 dark:text-zinc-400">Catálogo de responsables (correos, SLA, activo).</p>
        </div>

        <flux:modal.trigger name="modal-responsable" wire:click="create">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                + Nuevo
            </button>
        </flux:modal.trigger>
    </div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div class="rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @elseif(session()->has('error'))
        <div class="rounded-lg bg-red-100 px-4 py-2 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="w-full mx-auto rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 p-6 space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[250px]">
                <label class="block text-sm font-semibold text-gray-700 dark:text-zinc-200 mb-1">Buscar</label>
                <input type="text"
                    wire:model.live="q"
                    placeholder="Buscar por nombre..."
                    class="w-full border border-gray-300 dark:border-zinc-700 rounded-lg px-3 py-2 bg-white dark:bg-zinc-900 text-gray-800 dark:text-white">
            </div>

            <div class="w-[140px]">
                <label class="block text-sm font-semibold text-gray-700 dark:text-zinc-200 mb-1">Ver</label>
                <select wire:model.live="perPage"
                    class="w-full border border-gray-300 dark:border-zinc-700 rounded-lg px-3 py-2 bg-white dark:bg-zinc-900 text-gray-800 dark:text-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-zinc-300">
                <thead class="text-xs text-white uppercase bg-gray-900 dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-3 w-[90px]">Orden</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Correos</th>
                        <th class="px-4 py-3 w-[110px]">SLA</th>
                        <th class="px-4 py-3 w-[110px]">Activo</th>
                        <th class="px-4 py-3 w-[140px]">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $r)
                        <tr class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                            <td class="px-4 py-2">{{ $r->orden }}</td>

                            <td class="px-4 py-2 font-semibold">
                                {{ $r->nombre }}
                            </td>

                            <td class="px-4 py-2">
                                @if(is_array($r->correos) && count($r->correos))
                                    <span class="text-xs">{{ implode(', ', $r->correos) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-2">{{ $r->sla_dias_default ?? '—' }}</td>

                            <td class="px-4 py-2">
                                @if($r->activo)
                                    <span class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-semibold">Sí</span>
                                @else
                                    <span class="px-2 py-1 rounded bg-gray-200 text-gray-800 text-xs font-semibold">No</span>
                                @endif
                            </td>

                            <td class="px-4 py-2">
                                <div class="flex gap-2">
                                    <flux:modal.trigger name="modal-responsable" wire:click="edit({{ $r->id }})">
                                        <button class="bg-blue-600 hover:bg-blue-800 text-white px-3 py-1 rounded-lg">
                                            Editar
                                        </button>
                                    </flux:modal.trigger>

                                    <flux:modal.trigger name="modal-delete" wire:click="confirmDelete({{ $r->id }})">
                                        <button class="bg-red-600 hover:bg-red-800 text-white px-3 py-1 rounded-lg">
                                            Eliminar
                                        </button>
                                    </flux:modal.trigger>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                Sin registros
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div>
            {{ $items->links() }}
        </div>
    </div>

    {{-- Modal Crear/Editar --}}
    <flux:modal name="modal-responsable" class="md:w-[680px]">
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="bg-blue-600 p-3 rounded">
                <h2 class="text-center text-white font-bold">
                    {{ $editId ? 'EDITAR' : 'NUEVO' }} RESPONSABLE
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900">Nombre</label>
                    <input wire:model.defer="nombre"
                        class="w-full border border-gray-300 rounded p-2"
                        placeholder="Ej: Cartera / Servicio al cliente">
                    @error('nombre') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900">Correos (separados por coma)</label>
                    <textarea wire:model.defer="correos_texto"
                        class="w-full border border-gray-300 rounded p-2"
                        rows="2"
                        placeholder="correo1@..., correo2@..."></textarea>
                    @error('correos_texto') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-900">SLA días (default)</label>
                    <input type="number" wire:model.defer="sla_dias_default"
                        class="w-full border border-gray-300 rounded p-2" placeholder="Ej: 5">
                    @error('sla_dias_default') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-900">Orden</label>
                    <input type="number" wire:model.defer="orden"
                        class="w-full border border-gray-300 rounded p-2" placeholder="0">
                    @error('orden') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-900">Activo</label>
                    <select wire:model.defer="activo" class="w-full border border-gray-300 rounded p-2 bg-white">
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                    @error('activo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        Cancelar
                    </button>
                </flux:modal.close>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Guardar
                </button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal Delete --}}
    <flux:modal name="modal-delete" class="md:w-96">
        <form wire:submit.prevent="delete" class="space-y-6">
            <div class="bg-red-600 p-3 rounded">
                <h2 class="text-center text-white font-bold">CONFIRMAR ELIMINACIÓN</h2>
            </div>

            <p class="text-sm text-gray-700">
                ¿Seguro que deseas eliminar este registro?
            </p>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        Cancelar
                    </button>
                </flux:modal.close>

                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                    Eliminar
                </button>
            </div>
        </form>
    </flux:modal>

</div>
