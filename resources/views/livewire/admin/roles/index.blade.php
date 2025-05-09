<div class="p-6 bg-white dark:bg-zinc-800 rounded-xl shadow space-y-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">Roles</h1>
        <button wire:click="abrirModal" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg">
            + Crear Rol
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 rounded-xl">
            <thead class="text-xs text-zinc-700 uppercase bg-zinc-100 dark:bg-zinc-700 dark:text-zinc-300">
                <tr>
                    <th class="px-6 py-3">Nombre</th>
                    <th class="px-6 py-3">Permisos</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $rol)
                    <tr class="bg-white border-b dark:bg-zinc-800 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4">{{ $rol->name }}</td>
                        <td class="px-6 py-4">
                            {{ implode(', ', $rol->permissions->pluck('name')->toArray()) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button wire:click="editar({{ $rol->id }})" class="text-indigo-600 hover:text-indigo-800 font-medium transition">
                                Editar
                            </button>
                        </td>
                        <td class="px-4 py-2 text-center flex gap-3 justify-center">
                            <button
                                wire:click="eliminarRol({{ $rol->id }})"
                                onclick="return confirm('¿Estás seguro de eliminar este rol?')"
                                class="text-red-600 hover:text-red-800 font-medium"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($openModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-4 w-full max-w-md border border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-bold text-zinc-800 dark:text-white mb-3">
                    {{ $rol_id ? 'Editar Rol' : 'Nuevo Rol' }}
                </h2>

                <form wire:submit.prevent="guardar" class="space-y-3 text-sm">
                    @error('nombre')
                        <div class="p-2 bg-red-100 text-red-700 rounded">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="space-y-1">
                        <label class="block text-zinc-700 dark:text-zinc-300">Nombre del rol</label>
                        <input type="text" wire:model="nombre" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-3 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-zinc-700 dark:text-zinc-300">Permisos</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($permisos as $permiso)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" value="{{ $permiso->id }}" wire:model="permisosSeleccionados">
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $permiso->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 gap-3">
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
