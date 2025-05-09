<div class="space-y-6">
    <!-- Encabezado y Botón -->

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Lista de Usuarios</h1>
        <button wire:click="abrirModal" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg">
            + Crear Usuarioo
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    
    <!-- Tabla -->
    <div class="overflow-x-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700">
        <table class="w-full table-auto text-sm text-left text-gray-700 dark:text-zinc-300">
            <thead class="text-xs text-gray-600 uppercase bg-gray-100 dark:bg-zinc-700">
                <tr>
                    <th class="px-4 py-3">Id Asesor</th>
                    <th class="px-4 py-3">Id Recibos</th>
                    <th class="px-4 py-3">Cédula</th>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Rol</th>
                    <th class="px-4 py-3">Editar</th>
                    <th class="px-4 py-3">Eliminar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    <tr class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                        <td class="px-4 py-2">{{ $usuario->codigo_asesor }}</td>
                        <td class="px-4 py-2">{{ $usuario->codigo_recibos }}</td>
                        <td class="px-4 py-2">{{ $usuario->cedula }}</td>
                        <td class="px-4 py-2">{{ $usuario->name }}</td>
                        <td class="px-4 py-2">{{ $usuario->email }}</td>
                        <td class="px-4 py-2">
                            @foreach ($usuario->roles as $rol)
                                <span class="inline-block bg-indigo-100 text-indigo-800 text-xs font-semibold px-2 py-1 rounded-full mr-1 dark:bg-indigo-900 dark:text-indigo-200">
                                    {{ $rol->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-4 py-2">
                            <button wire:click="editarUsuario({{ $usuario->id }})" class="text-indigo-600 hover:text-indigo-800 font-medium transition">
                                Editar
                            </button>
                        </td>
                        <td class="px-4 py-2 flex gap-2 justify-center">
                            <button
                                wire:click="eliminarUsuario({{ $usuario->id }})"
                                class="text-red-500 hover:text-red-700 font-medium transition"
                                onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <!-- Modal -->
    @if ($openModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-4 w-full max-w-md border border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-bold text-zinc-800 dark:text-white mb-3">
                    {{ $modoEditar ? 'Editar Usuario' : 'Nuevo Usuario' }}
                </h2>

                <form wire:submit.prevent="{{ $modoEditar ? 'actualizarUsuario' : 'guardarUsuario' }}" class="space-y-3 text-sm">

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-1">
                        <label class="block text-zinc-700 dark:text-zinc-300">Nombre</label>
                        <input type="text" wire:model="name" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-3 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                    </div>

                    <div class="space-y-1">
                        <label class="block text-zinc-700 dark:text-zinc-300">Email</label>
                        <input type="email" wire:model="email" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-3 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                    </div>

                    @unless($modoEditar)
                        <div class="space-y-1">
                            <label class="block text-zinc-700 dark:text-zinc-300">Contraseña</label>
                            <input type="password" wire:model="password" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-3 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                        </div>
                    @endunless

                    <div class="grid grid-cols-3 gap-2">
                        <div class="space-y-1">
                            <label class="block text-zinc-700 dark:text-zinc-300">Cédula</label>
                            <input type="text" wire:model="cedula" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-2 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-zinc-700 dark:text-zinc-300">Código Asesor</label>
                            <input type="text" wire:model="codigo_asesor" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-2 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-zinc-700 dark:text-zinc-300">Código Recibos</label>
                            <input type="text" wire:model="codigo_recibos" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-2 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-zinc-700 dark:text-zinc-300">Roles</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($roles as $rol)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" wire:model="rolesSeleccionados" value="{{ $rol->id }}" class="rounded text-indigo-600 border-gray-300 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $rol->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-4 gap-3">
                        <button
                            type="button"
                            wire:click="$set('openModal', false)"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg"
                        >
                            {{ $modoEditar ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>