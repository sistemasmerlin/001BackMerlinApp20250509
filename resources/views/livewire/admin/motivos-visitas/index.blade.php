<div class="space-y-6">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Motivos visita</h1>
        <button wire:click="crear" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
            + Nuevo motivo
        </button>
    </div>

    @if (session()->has('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
        {{ session('success') }}
    </div>
    @endif

    <div class="w-4/5 overflow-x-auto mx-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-3">
        <div wire:ignore>
            <table class="w-full mx-auto table-auto text-sm text-left text-gray-700 dark:text-zinc-300">
                <thead class="text-xs text-zinc-50 bg-zinc-950 uppercase dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-center">ID</th>
                        <th class="px-4 py-3">Motivo</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($motivos as $motivo)
                    <tr class="border-b border-gray-200">
                        <td class="text-center">{{ $motivo->id }}</td>
                        <td>{{ $motivo->motivo }}</td>
                        <td class="flex space-x-2 py-2">
                            <button wire:click="editar({{ $motivo->id }})" class="px-3 py-1 bg-blue-500 hover:bg-blue-800 text-white font-semibold rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                            </button>

                            <button wire:click="confirmarEliminar({{ $motivo->id }})" class="px-3 py-1 bg-red-500 hover:bg-red-800 text-white font-semibold rounded-lg" >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    @if($modal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-4 w-full max-w-md border border-zinc-200 dark:border-zinc-700">

            <h2 class="text-xl font-bold mb-4">
                {{ $modoEditar ? 'Editar Motivo' : 'Nuevo Motivo' }}
            </h2>

            <div class="space-y-2">
                <label class="block text-sm font-medium">Motivo</label>
                <input type="text" wire:model="motivo" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <button wire:click="$set('modal', false)" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded">
                    Cancelar
                </button>
                <button wire:click="guardar" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded">
                    Guardar
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
