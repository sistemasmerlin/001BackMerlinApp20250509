<div class="space-y-6">
    <!-- Encabezado y Botón -->

    <div>
        <div class="flex flex-col items-center mb-4 relative">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white text-center">Lista de Promociones</h1>
        </div>
        <div>
            <button wire:click="abrirModal" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg">
            + Agregar promoción</button>
        </div>
    </div>
    
    <!-- Espacio para subir plano excel -->
    <div >
        <h1 class="text-2xl font-bold">Excel Promociones</h1>
        @livewire('admin.promociones.promocion-detalle')

        <p><strong>Nota:</strong> Al subir un nuevo plano, este eliminar todos los registros del anterior.</p>
    </div>

    <div class="w-full mx-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-6">
        <div wire:ignore>
            <table id="promociones" class="w-3/4 table-auto text-sm text-left text-gray-700 dark:text-zinc-300" style="padding-top: 10px;">
                <thead class="text-xs text-gray-600 uppercase bg-gray-100 dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-3">Id</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3">Fecha inicio</th>
                        <th class="px-4 py-3">Fecha fin</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Creado por</th>
                        <th class="px-4 py-3">Asignar</th>
                        <th class="px-4 py-3">Asignado</th>
                        <th class="px-4 py-3">Ver</th>
                        <th class="px-4 py-3">Editar</th>
                        <th class="px-4 py-3">Eliminar</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($promociones as $promocion)
                        <tr class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                            <td class="px-4 py-2">{{ $promocion->id }}</td>
                            <td class="px-4 py-2">{{ $promocion->nombre }}</td>
                            <td class="px-4 py-2">{{ $promocion->descripcion }}</td>
                            <td class="px-4 py-2">{{ $promocion->fecha_inicio }}</td>
                            <td class="px-4 py-2">{{ $promocion->fecha_fin }}</td>
                            @if($promocion->estado = '1')    
                            <td class="px-4 py-2">ACTIVA</td>     
                            @else
                            <td class="px-4 py-2">IANCTIVA</td>  
                            @endif           
                            <td class="px-4 py-2">{{ $promocion->creado_por }}</td>
                            <td class="justify-center items-center">
                                <button wire:click="abrirModalAsignar({{ $promocion->id }})" class="px-3 py-1 bg-blue-500 hover:bg-blue-800 text-white font-semibold rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>

                                </button>                        
                            </td>
                            <td class="px-4 py-2 space-y-1">
                                @foreach($promocion->relaciones as $relacion)
                                    <div class="flex items-center justify-between bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">
                                        <span class="text-xs font-medium">
                                            {{ $relacion->asignado }} <span class="text-gray-500">({{ $relacion->subcanal }})</span>
                                        </span>
                                        <button wire:click="eliminarRelacion({{ $relacion->id }})" class="text-red-500 hover:text-red-700 text-xs font-bold ml-2">
                                            <flux:icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </td>
                            <td class="justify-center items-center">
                                <a href="{{ route('admin.promociones.detalle', $promocion->id) }}" class="inline-flex px-3 py-1 bg-green-500 hover:bg-green-800 text-white font-semibold rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </a>
                            </td>
                            <td class="justify-center items-center">
                                <button wire:click="editarPromocion({{ $promocion->id }})" class="px-3 py-1 bg-blue-500 hover:bg-blue-800 text-white font-semibold rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                            </td>
                            <td class="justify-center items-center">
                                <button
                                    wire:click="eliminarPromocion({{ $promocion->id }})"
                                    class="px-3 py-1 bg-red-500 hover:bg-red-800 text-white font-semibold rounded-lg"
                                    onclick="return confirm('¿Estás seguro de eliminar esta promoción')">
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

    @if ($openModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-4 w-full max-w-md border border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-bold text-zinc-800 dark:text-white mb-3">
                    {{ $modoEditar ? 'Editar Promocion' : 'Nuevo Promocion' }}
                </h2>

                <form wire:submit.prevent="{{ $modoEditar ? 'actualizarPromocion' : 'guardarPromocion' }}" class="space-y-3 text-sm">

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
                        <input type="text" wire:model="nombre" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-3 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="space-y-1">
                            <label class="block text-zinc-700 dark:text-zinc-300">Descripcion</label>
                            <input type="text" wire:model="descripcion" class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-2 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-zinc-700 dark:text-zinc-300">Fecha inicio</label>
                            <input type="date" wire:model="fecha_inicio"
                                class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-2 py-1.5 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-zinc-700 dark:text-zinc-300">Fecha fin</label>
                            <input type="date" wire:model="fecha_fin"
                                class="w-full rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white px-2 py-1.5 focus:ring-indigo-500 focus:outline-none" />
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

    @if ($openAsignarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-xl border border-zinc-300 dark:border-zinc-700 w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-zinc-800 dark:text-white mb-4">
                    Asignar Promoción
                </h2>

                <!-- Tipo de asignación -->
                <div class="mb-4">
                    <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Tipo de asignación</label>
                    <select wire:change="$set('tipoAsignacion', $event.target.value)" class="w-full rounded border px-3 py-2 bg-white dark:bg-zinc-800 text-black dark:text-white">
                        <option value="">Seleccione una opción</option>
                        <option value="cliente">Cliente</option>
                        <option value="todos">Todos</option>
                        <option value="ciudad">Ciudad</option>
                        <option value="asesor">Asesor</option>
                    </select>
                </div>

                <!-- Cliente -->
                @if ($tipoAsignacion === 'cliente')
                    <div class="mb-4">
                        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">NIT del cliente</label>
                        <input type="text" wire:model="nitCliente" class="w-full border rounded px-3 py-2 dark:bg-zinc-800 dark:text-white">
                    </div>
                @endif

                <!-- Todos -->
                @if ($tipoAsignacion === 'todos')

                    <p>Por favor no usar TODOS, este es de uso exclusivo de los descuentos generales por subtotal de pedidos</p>
                    <div class="mb-4">
                        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Subcanal</label>
                        <select wire:model="subcanalSeleccionado" class="w-full border rounded px-3 py-2 dark:bg-zinc-800 dark:text-white">
                            <option value="">Seleccione un subcanal</option>
                            <option value="MAYORISTA">MAYORISTA</option>
                            <option value="MINORISTA">MINORISTA</option>
                            <option value="TODOS">TODOS</option>
                        </select>
                    </div>
                @endif

                <!-- Ciudad -->
                @if ($tipoAsignacion === 'ciudad')
                    <div class="mb-4">
                        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Departamento</label>

                        <select wire:model.live="departamentoSeleccionado" class="w-full rounded border px-3 py-2 dark:bg-zinc-800 dark:text-white">
                            <option value="">Seleccione un departamento</option>
                            @foreach ($departamentos as $cod => $nombre)
                                <option value="{{ $cod }}">{{ $nombre }} ({{ $cod }})</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($departamentoSeleccionado)
                        <div class="mb-4">
                            <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Ciudad</label>
                            <select 
                                wire:model.live="ciudadCodigoSeleccionada"
                                wire:change="actualizarCiudadSeleccionada($event.target.value)"
                                class="w-full rounded border px-3 py-2 dark:bg-zinc-800 dark:text-white"
                            >
                                <option value="">Seleccione una ciudad</option>
                                @foreach ($ciudadesFiltradas as $ciudad)
                                    <option value="{{ $ciudad['cod_ciudad'] }}">
                                        {{ $ciudad['ciudad'] }} ({{ $ciudad['cod_ciudad'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @endif

                <!-- Asesor -->
                @if ($tipoAsignacion === 'asesor')
                    <div class="mb-4">
                        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Código del asesor</label>
                        <input type="text" wire:model="codigoAsesor" class="w-full border rounded px-3 py-2 dark:bg-zinc-800 dark:text-white">

                        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Subcanal</label>
                        <select wire:model="subcanalSeleccionado" class="w-full border rounded px-3 py-2 dark:bg-zinc-800 dark:text-white">
                            <option value="">Seleccione un subcanal</option>
                            <option value="MAYORISTA">MAYORISTA</option>
                            <option value="MINORISTA">MINORISTA</option>
                        </select>
                    </div>
                @endif

                <!-- Botones -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button wire:click="$set('openAsignarModal', false)"
                            class="bg-gray-200 dark:bg-zinc-700 text-black dark:text-white px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-zinc-600">
                        Cancelar
                    </button>
                    <form wire:submit.prevent="asignarPromocion">
                        <!-- todos los campos aquí -->

                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" wire:click="$set('openAsignarModal', false)"
                                    class="bg-gray-200 dark:bg-zinc-700 text-black dark:text-white px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-zinc-600">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                                Asignar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif


    @push('scripts')
        <script>
            function iniciarDataTable() {
                if ($.fn.DataTable.isDataTable('#promociones')) {
                    $('#promociones').DataTable().destroy();
                }

                $('#promociones').DataTable({
                    responsive: false,
                    fixedHeader: true, //Encabezado fijo
                    scrollX: true, //Evita que escabezado se salga de la tabla
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