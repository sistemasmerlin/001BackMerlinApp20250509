<div class="space-y-6">
    
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Lista de Usuarios</h1>
        <button wire:click="abrirModal" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            + Crear Usuarioo
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="overflow-x-auto rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 p-6" >
        <table id="tabla" class="w-full table-auto text-sm text-left text-gray-700 dark:text-zinc-300" style="padding-top: 10px;">
            <thead class="text-xs text-gray-600 dark:text-zinc-50 uppercase bg-gray-100 dark:bg-zinc-700">
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
                            <td class="px-3 py-2">
                                <button wire:click="editarUsuario({{ $usuario->id }})" class="px-3 py-1 bg-blue-500 hover:bg-blue-800 text-white font-semibold rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                </button>
                            </td>
                            <td class="px-3 py-2 flex gap-2 justify-center">
                                <button
                                    wire:click="eliminarUsuario({{ $usuario->id }})"
                                    class="px-3 py-1 bg-red-500 hover:bg-red-800 text-white font-semibold rounded-lg"
                                    onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                                >
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

                    @if ($modoEditar)
                        <div class="space-y-1 relative">
                            <label class="block text-zinc-700 dark:text-zinc-300">Nueva Contraseña</label>
                            <input :type="$wire.mostrarPassword ? 'text' : 'password'"
                                wire:model.defer="nuevaPassword"
                                class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-3 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                            <button type="button"
                                    wire:click="$toggle('mostrarPassword')"
                                    class="absolute right-2 top-7 text-xs text-indigo-600 hover:underline">
                                {{ $mostrarPassword ? 'Ocultar' : 'Ver' }}
                            </button>
                        </div>
                    @else
                        <div class="space-y-1 relative">
                            <label class="block text-zinc-700 dark:text-zinc-300">Contraseña</label>
                            <input :type="$wire.mostrarPassword ? 'text' : 'password'"
                                wire:model.defer="password"
                                class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-3 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                            <button type="button"
                                    wire:click="$toggle('mostrarPassword')"
                                    class="absolute right-2 top-7 text-xs text-indigo-600 hover:underline">
                                {{ $mostrarPassword ? 'Ocultar' : 'Ver' }}
                            </button>
                        </div>
                    @endif


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

    @push('scripts')
        <script>
            function iniciarDataTable() {
                if ($.fn.DataTable.isDataTable('#tabla')) {
                    $('#tabla').DataTable().destroy();
                }

                $('#tabla').DataTable({
                    responsive: false,
                    "lengthMenu": [10, 50, 100],
                    "language": {
                        "lengthMenu": "Ver _MENU_",
                        "zeroRecords": "Sin datos",
                        "info": "Página _PAGE_ de _PAGES_",
                        "infoEmpty": "No hay datos disponibles",
                        "infoFiltered": "(Filtrado de _MAX_ registros totales)",
                        'search': 'Buscar:',
                        'paginate': {
                            'next': 'Siguiente',
                            'previous': 'Anterior'
                        }
                    }
                });
            }

            //cuando la vista carga por primera vez.
            document.addEventListener("livewire:load", () => {
                iniciarDataTable();
            });
            //cuando se vuelve a la vista.
            document.addEventListener("livewire:navigated", () => {
                setTimeout(() => iniciarDataTable(), 50);
            });

        </script>
        @endpush

</div>