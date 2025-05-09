<div class="p-6 bg-white dark:bg-zinc-800 rounded-xl shadow space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">Permisos</h1>
        <button wire:click="abrirModal" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg">
            + Crear Permiso
        </button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded-md shadow text-sm">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full text-sm text-left text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 rounded-xl">
        <thead class="bg-zinc-100 dark:bg-zinc-700 text-xs uppercase text-zinc-700 dark:text-zinc-300">
            <tr>
                <th class="px-6 py-3">Nombre</th>
                <th class="px-6 py-3 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permisos as $permiso)
                <tr class="bg-white border-b dark:bg-zinc-800 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                    <td class="px-6 py-4">{{ $permiso->name }}</td>
                    <td class="px-6 py-4 text-center">
                        <button wire:click="editar({{ $permiso->id }})" class="text-indigo-600 hover:text-indigo-800 font-medium transition">
                            Editar
                        </button>
                    </td>
                    <td class="px-6 py-4 text-center flex justify-center gap-3">
                        <button
                            wire:click="eliminarPermiso({{ $permiso->id }})"
                            onclick="return confirm('¿Estás seguro de eliminar este permiso?')"
                            class="text-red-600 hover:text-red-800 font-medium transition"
                        >
                            Eliminar
                        </button>
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Modal --}}
    @if ($openModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-6 w-full max-w-md border border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-bold text-zinc-800 dark:text-white mb-4">
                    {{ $permiso_id ? 'Editar Permiso' : 'Nuevo Permiso' }}
                </h2>

                <form wire:submit.prevent="{{ $permiso_id ? 'actualizar' : 'guardar' }}" class="space-y-4 text-sm">
                    @error('nombre')
                        <div class="p-2 bg-red-100 text-red-700 rounded">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="space-y-1">
                        <label class="text-zinc-700 dark:text-zinc-300">Nombre del permiso</label>
                        <input type="text" wire:model.defer="nombre" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-3 py-2 focus:ring-indigo-500 focus:outline-none" />
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" wire:click="$set('openModal', false)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg">
                            Cancelar
                        </button>
                        <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
