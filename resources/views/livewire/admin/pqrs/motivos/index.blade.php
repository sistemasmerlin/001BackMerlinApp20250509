<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Motivos PQRS</h1>
            <p class="text-sm text-gray-500 dark:text-zinc-400">Catálogo global de motivos.</p>
        </div>

        <button wire:click="create"
            class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
            + Nuevo
        </button>
    </div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div class="rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-lg bg-red-100 px-4 py-2 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Card --}}
    <div class="w-full mx-auto rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 p-6">

        {{-- Filters --}}
        <div class="flex flex-col md:flex-row gap-2 md:items-center md:justify-between mb-4">
            <div class="flex gap-2 w-full md:w-2/3">
                <input type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                    placeholder="Buscar por nombre..."
                    wire:model.live="q">
            </div>

            <div class="flex gap-2 items-center">
                <select wire:model.live="perPage" class="border border-gray-300 rounded-lg px-3 py-2">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-zinc-300">
                <thead class="text-xs text-zinc-50 uppercase bg-gray-900 dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-3 w-24">Orden</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3 w-28">Activo</th>
                        <th class="px-4 py-3 w-40">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $m)
                        <tr class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                            <td class="px-4 py-2">{{ $m->orden }}</td>
                            <td class="px-4 py-2 font-semibold">{{ $m->nombre }}</td>
                            <td class="px-4 py-2">
                                @if($m->activo)
                                    <span class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-semibold">Sí</span>
                                @else
                                    <span class="px-2 py-1 rounded bg-gray-200 text-gray-700 text-xs font-semibold">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex gap-2">
                                    <button wire:click="edit({{ $m->id }})"
                                        class="px-3 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white">
                                        Editar
                                    </button>

                                    <button wire:click="toggleActivo({{ $m->id }})"
                                        class="px-3 py-1 rounded {{ $m->activo ? 'bg-amber-600 hover:bg-amber-700' : 'bg-green-600 hover:bg-green-700' }} text-white">
                                        {{ $m->activo ? 'Inactivar' : 'Activar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>

    {{-- MODAL (Flux) --}}
    <flux:modal name="modal-motivo" class="md:w-[520px]">
        <form wire:submit.prevent="save">
            <div class="space-y-5">

                <div class="bg-blue-600 p-3 rounded-lg">
                    <h2 class="text-center text-white font-bold">
                        {{ $editId ? 'Editar Motivo' : 'Nuevo Motivo' }}
                    </h2>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-900">Nombre</label>
                    <input wire:model.defer="nombre"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                    @error('nombre') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-bold text-gray-900">Orden</label>
                        <input type="number" wire:model.defer="orden"
                            class="w-full border border-gray-300 rounded-lg p-2" />
                        @error('orden') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900">Activo</label>
                        <select wire:model.defer="activo"
                            class="w-full border border-gray-300 rounded-lg p-2 bg-white">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                        @error('activo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <button type="button"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                            Cancelar
                        </button>
                    </flux:modal.close>

                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        Guardar
                    </button>
                </div>
            </div>
        </form>
    </flux:modal>

</div>
