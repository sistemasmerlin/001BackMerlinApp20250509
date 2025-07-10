<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">Permisos</h1>
        <button wire:click="abrirModal" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg">
            + Crear Permiso
        </button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded-md shadow text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="w-2/4 overflow-x-auto max-w-screen-lg mx-auto rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 p-6">
        <table id="tabla" class="w-4/5 table-auto text-left text-zinc-600 dark:text-zinc-300 pt-3">
            <thead class="bg-zinc-100 dark:bg-zinc-700 text-xs uppercase text-zinc-700 dark:text-zinc-300" >
                <tr>
                    <th class="px-6 py-3">Nombre</th>
                    <th class="px-6 py-3" style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permisos as $permiso)
                    <tr class="bg-white border-b dark:bg-zinc-800 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <td class="px-6 py-4">{{ $permiso->name }}</td>
                        <td class="px-6 py-4 text-center">
                            <button wire:click="editar({{ $permiso->id }})" class="px-3 py-1 bg-blue-500 hover:bg-blue-800 text-white font-semibold rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button
                                wire:click="eliminarPermiso({{ $permiso->id }})"
                                onclick="return confirm('¿Estás seguro de eliminar este permiso?')"
                                class="px-3 py-1 bg-red-500 hover:bg-red-800 text-white font-semibold rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
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

    @push('scripts')
        <script>
            function iniciarDataTable() {
                if ($.fn.DataTable.isDataTable('#tabla')) {
                    $('#tabla').DataTable().destroy();
                }

                $('#tabla').DataTable({
                    responsive: false,
                    fixedHeader: true, //Encabezado fijo
                    scrollX: false, //Evita que encabezado se salga de la tabla
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
