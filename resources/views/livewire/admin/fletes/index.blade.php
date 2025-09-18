<div class="space-y-6">

    <!-- Encabezado <div>
    {{-- Success is as dangerous as failure. --}}
</div>
 -->
    <h1 class="text-2xl font-bold">Excel Fletes Ciudades</h1>
    <div>
        @if (session()->has('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @elseif (session()->has('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded">
                {{ session('error') }}
            </div>
         @endif

        @can('Subir fletes masivo')
        <form wire:submit.prevent="importarFlete" enctype="multipart/form-data">
            <div class="flex mb-4">
                <input type="file" wire:model="excel_fletes" accept=".xls,.xlsx" class="mt-2 bg-yellow-100 block  border border-gray-300 rounded-xl shadow mx-2" required>
                <button type="submit" class="bg-green-500 hover:bg-green-700 font-bold text-white px-4 py-1 rounded">Importar</button>
            </div>
        </form>
        @endcan
    </div>

    <!-- Tabla de detalles -->
    <div class="w-4/5 overflow-x-auto mx-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700  p-6">
        <div wire:ignore>
            <table id="tabla" class=" table-auto text-sm text-left text-gray-700 dark:text-zinc-300" style="padding-top: 10px; table-layout: fixed;">
                <thead class="text-xs text-zinc-50 bg-zinc-950 uppercase dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-1">#</th>
                        <th class="px-4 py-2">Departamento</th>
                        <th class="px-4 py-2">Codigo Dep</th>
                        <th class="px-4 py-2">Ciudad</th>
                        <th class="px-4 py-2">Con Ciudad</th>
                        <th class="px-4 py-2">Menor</th>
                        <th class="px-4 py-2">Mayor</th>
                        <th class="px-4 py-2">Minimo</th>
                        <th class="px-4 py-2">Entrega</th>
                        <th class="px-4 py-2">Monto</th>
                        <th class="px-4 py-2">Monto Min</th>
                        <th class="px-4 py-2">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fletes as $flete)
                        <tr class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                            <td class="px-4 py-1">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $flete->depto }}</td>
                            <td class="px-4 py-2">{{ $flete->cod_depto }}</td>
                            <td class="px-4 py-2">{{ $flete->ciudad }}</td>
                            <td class="px-4 py-2">{{ $flete->cod_ciudad }}</td>
                            <td class="px-4 py-2">{{ $flete->menor }}</td>
                            <td class="px-4 py-2">{{ $flete->mayor }}</td>
                            <td class="px-4 py-2">{{ $flete->minimo }}</td>
                            <td class="px-4 py-2">{{ $flete->entrega }}</td>
                            <td class="px-4 py-2">{{ $flete->monto }}</td>
                            <td class="px-4 py-2">{{ $flete->monto_minimo }}</td>
                            <td class="px-4 py-2">
                                @can('Editar Flete')
                                    <button wire:click="editarFlete({{$flete->id}})" class="px-3 py-1 bg-blue-500 hover:bg-blue-800 text-white font-semibold rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>
                                @endcan
                                @can('Eliminar Flete')
                                    <button wire:click="eliminarFlete({{$flete->id}})" class="px-3 py-1 bg-red-500 hover:bg-red-800 text-white font-semibold rounded-lg" onclick="return confirm('¿Estás seguro de eliminar este flete?')" >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                @endcan
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

   <!--Modal Edicion-->

   @if ($modalEditar)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg p-6 w-1/2">
                    <div class="bg-blue-400 rounded-lg">
                        <h2 class="text-xl font-semibold mb-4 text-center">Editar Flete</h2>
                    </div>

                    <form wire:submit.prevent="actualizarFlete">
                        <input type="hidden" wire:model="fleteId">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm">Departamento</label>
                                <input type="text" wire:model="depto" class="w-full border rounded p-2 bg-zinc-300" readonly>
                            </div>
                            <div>
                                <label class="block text-sm">Código Depto</label> 
                                <input type="text" wire:model="cod_depto" class="w-full border rounded p-2 bg-zinc-300" readonly>
                            </div>
                            <div>
                                <label class="block text-sm">Ciudad</label>
                                <input type="text" wire:model="ciudad" class="w-full border rounded p-2 bg-zinc-300" readonly>
                            </div>
                            <div>
                                <label class="block text-sm">Código Ciudad</label>
                                <input type="text" wire:model="cod_ciudad" class="w-full border rounded p-2 bg-zinc-300" readonly>
                            </div>
                            <div>
                                <label class="block text-sm">Menor</label>
                                <input type="text" wire:model="menor" class="w-full border rounded p-2">
                            </div>
                            <div>
                                <label class="block text-sm">Mayor</label>
                                <input type="text" wire:model="mayor" class="w-full border rounded p-2">
                            </div>
                            <div>
                                <label class="block text-sm">Mínimo</label>
                                <input type="text" wire:model="minimo" class="w-full border rounded p-2">
                            </div>
                            <div>
                                <label class="block text-sm">Entrega</label>
                                <input type="text" wire:model="entrega" class="w-full border rounded p-2">
                            </div>
                            <div>
                                <label class="block text-sm">Monto</label>
                                <input type="number" wire:model="monto" class="w-full border rounded p-2">
                            </div>
                            <div>
                                <label class="block text-sm">Monto mínimo</label>
                                <input type="number" wire:model="monto_minimo" class="w-full border rounded p-2">
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" wire:click="$set('modalEditar', false)"
                                    class="bg-gray-500 dark:bg-zinc-700 text-zinc-50 dark:text-white px-4 py-2 rounded hover:bg-gray-700 dark:hover:bg-zinc-600">
                                Cancelar
                            </button>
                            <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-800">
                                Guardar
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
                if ($.fn.DataTable.isDataTable('#tabla')) {
                    $('#tabla').DataTable().destroy();
                }

                $('#tabla').DataTable({
                    responsive: false,
                    fixedHeader: true, //Encabezado fijo
                    scrollX: true, //Evita que encabezado se salga de la tabla
                    "lengthMenu": [50, 500, 5000],
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
