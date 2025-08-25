<div class="space-y-6">
    <!-- Encabezado y Botón -->

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Listar Pedidos</h1>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @elseif(session()->has('warning'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-red-500">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="w-full mx-auto rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 p-6">

        <div class="flex gap-2">
            <form method="GET" class="space-y-4">
                <h2 class="text-lg font-semibold">Seleccionar fechas:</h2>
                <div class="flex gap-2">
                    <label for="fecha_inicio" class="text-sm font-medium">Desde:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="border rounded px-1 py-1" value="{{ request('fecha_inicio') }}">
                    <label for="fecha_final" class="text-sm font-medium">Hasta:</label>
                    <input type="date" id="fecha_final" name="fecha_final" class="border rounded px-1 py-1" value="{{ request('fecha_final') }}">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Buscar</button>
                    @if(request('fecha_inicio') || request('fecha_final'))
                        <a href="{{ route('pedidos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white px-3 py-1 rounded">
                            Limpiar Filtro
                        </a>
                    @endif
                </div>
            </form>
        </div> <br>

        <div wire:ignore>
            <table id="pedidos" class="w-3/4 table-auto text-sm text-left text-gray-700 dark:text-zinc-300" style="padding-top: 10px;">
                <thead class="text-xs text-zinc-50 uppercase bg-gray-900 dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-3">Id</th>
                        <th class="px-4 py-3">Prefijo</th>
                        <th class="px-4 py-3">Nit Cliente</th>
                        <th class="px-4 py-3">Cambiar Cliente</th>
                        <th class="px-4 py-3">Nombre Asesor</th>
                        <th class="px-4 py-3">Ver</th>
                        <th class="px-4 py-3">Enviar Siesa</th>
                        <th class="px-4 py-3">Sucursal</th>
                        <th class="px-4 py-3">Cond Pago</th>
                        <th class="px-4 py-3">Valor Flete</th>
                        <th class="px-4 py-3">Estado Siesa</th>
                        <th class="px-4 py-3">Observaciones</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Fecha Pedido</th>
                        <th class="px-4 py-3">Opciones</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                        <tr class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                            <td class="px-4 py-2 border-t-2">{{ $pedido->id }}</td>
                            @if($pedido->prefijo == 'PES')
                            <td class="px-4 py-2 bg-lime-300 border-t-2"><strong>{{ $pedido->prefijo }}</strong></td>
                            @else
                            <td class="px-4 py-2 border-t-2" ><strong>{{ $pedido->prefijo }}</strong></td>
                            @endif

                            <td class="px-6 py-4  border-t-2"><strong>{{ $pedido->nit }}</strong> - {{ $pedido->razon_social }}</td>
                            <td class="px-6 py-4 border-t-2">
                                <div class="flex items-center gap-2">
                                @if($pedido->prefijo == 'PES')
                                    <flux:modal.trigger name="cambiar-nit" wire:click="abrirModalNit({{ $pedido->id }})">
                                    <button class="px-3 py-1 bg-blue-600 hover:bg-blue-800 text-white font-semibold rounded-lg mt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>
                                    </flux:modal.trigger>
                                @else
                                    <div>
                                        N/A
                                    </div>
                                @endif
                                </div>
                            </td>

                            <td class="px-4 py-2 border-t-2">{{ $pedido->nombre_asesor }}</td>
                            <td class="px-4 py-2 border-t-2">
                                <a href="{{ route('admin.pedidos.detalle', $pedido->id) }}" class="inline-flex px-3 py-1 bg-green-500 hover:bg-green-800 text-white font-semibold rounded-lg">
                                    <svg class="size-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                        <path d="M288 144a110.9 110.9 0 0 0 -31.2 5 a55.4 55.4 0 0 1 7.2 27 a56 56 0 0 1 -56 56 a55.4 55.4 0 0 1 -27-7.2A111.7 111.7 0 1 0 288 144zm284.5 97.4C518.3 135.6 410.9 64 288 64S57.7 135.6 3.5 241.4a32.4 32.4 0 0 0 0 29.2C57.7 376.4 165.1 448 288 448s230.3-71.6 284.5-177.4a32.4 32.4 0 0 0 0-29.2zM288 400c-98.7 0-189.1-55-237.9-144C98.9 167 189.3 112 288 112s189.1 55 237.9 144C477.1 345 386.7 400 288 400z"/>
                                    </svg>
                                </a>
                            </td>
                            @if($pedido->prefijo == 'PES')
                            <td class="px-4 py-2 border-t-2">
                                <a href="{{ route('pedidos.enviar', $pedido->id) }}"
                                class="inline-block px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                <svg  class="size-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"> <path d="M440 6.5L24 246.4c-34.4 19.9-31.1 70.8 5.7 85.9L144 379.6V464c0 46.4 59.2 65.5 86.6 28.6l43.8-59.1 111.9 46.2c5.9 2.4 12.1 3.6 18.3 3.6 8.2 0 16.3-2.1 23.6-6.2 12.8-7.2 21.6-20 23.9-34.5l59.4-387.2c6.1-40.1-36.9-68.8-71.5-48.9zM192 464v-64.6l36.6 15.1L192 464zm212.6-28.7l-153.8-63.5L391 169.5c10.7-15.5-9.5-33.5-23.7-21.2L155.8 332.6 48 288 464 48l-59.4 387.3z"/></svg>
                                </a>
                            </td>
                            @else
                            <td class="px-4 py-2 border-t-2">
                                N/A
                            </td>
                            @endif
                            <td class="px-4 py-2 border-t-2">{{ $pedido->id_sucursal }}</td>
                            <td class="px-4 py-2 border-t-2">{{ $pedido->condicion_pago }}</td>
                            <td class="px-4 py-2 border-t-2">{{ $pedido->flete }}</td>
                            <td class="px-4 py-2 border-t-2">{{ $pedido->estado_siesa }}</td>

                            @if($pedido->prefijo == 'PES')

                            <td class="px-4 py-2  border-t-2">{{ $pedido->observaciones }} 
                                <br>
                            <flux:modal.trigger name="edit-nota" wire:click="editarNota({{ $pedido->id }})">
                                    <button class="px-3 py-1 bg-blue-600 hover:bg-blue-800 text-white font-semibold rounded-lg mt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>
                                </flux:modal.trigger>
                            </td>
                            @else
                            <td class="px-4 py-2 border-t-2">
                                N/A
                            </td>
                            @endif

                            @if($pedido->nota == 'Negociación especial')
                            <td class="px-4 py-2 bg-lime-300 border-t-2"><strong>{{ $pedido->nota }}</strong></td>
                            @else
                            <td class="px-4 py-2 bg-gray-200 border-t-2"><strong>{{ $pedido->nota }}</strong></td>
                            @endif
                            <td class="px-4 py-2 border-t-2">{{ $pedido->fecha_pedido }}</td>
                            @if($pedido->prefijo == 'PES')
                            <td class="px-4 py-2 border-t-2">
                                <button
                                    wire:click="eliminarCotizacion({{ $pedido->id }})"
                                    class="px-3 py-1 bg-red-500 hover:bg-red-800 text-white font-semibold rounded-lg"
                                    onclick="return confirm('¿Estás seguro de eliminar esta cotizacion?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </td>
                            @else
                            <td class="px-4 py-2 border-t-2">
                                N/A
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody> 
            </table>
        </div>

        <!-- Modal para editar nota -->

        <flux:modal name="edit-nota" class="md:w-96" >
            <form wire:submit.prevent="guardarNota">
                <div class="space-y-6">
                        <div class="bg-blue-500 p-3 rounded">
                            <h2 class="text-center text-accent"><strong>ACTUALIZAR NOTA</strong> </h2>
                        </div>
                        <div>
                            <label for="notaId" class="block text-sm text-gray-900 font-bold">ID COTIZACION:</label>
                            <input id="notaId" wire:model.defer="notaId" class="w-full border bg-gray-300 border-gray-300 rounded p-1" rows="4" readonly>
                        </div>
                        <div>
                            <label for="observacion" class="block text-sm text-gray-900 font-bold">NOTA:</label>
                            <input id="observacion" wire:model.defer="observacion" class="w-full border border-gray-300 rounded p-2" rows="4">
                        </div>
                        <div class="flex justify-end">
                            <flux:spacer />
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Guardar</button>
                        </div>
                    </div>
                </form>
        </flux:modal>

         <flux:modal name="cambiar-nit" class="md:w-96">
    <form wire:submit.prevent="guardarNuevoNit">
        <div class="space-y-6">
            <div class="bg-amber-500 p-3 rounded">
                <h2 class="text-center text-accent font-bold">CAMBIAR NIT DEL CLIENTE</h2>
            </div>

            <div>
                <label class="block text-sm text-gray-900 font-bold">ID PEDIDO:</label>
                <input
                    class="w-full border bg-gray-200 border-gray-300 rounded p-2"
                    value="{{ $pedidoIdParaNit }}"
                    readonly>
            </div>

            <div>
<label for="nuevoNit" class="block text-sm text-gray-900 font-bold">NIT:</label>
<input
    id="nuevoNit"
    wire:model.defer="nuevoNit"
    type="text"
    inputmode="numeric"
    class="w-full border border-gray-300 rounded p-2"
    placeholder="Ej: 900123456" required />

<!-- ========================== -->
<!-- Select de Sucursal -->
<label for="nuevaSucursal" class="block text-sm text-gray-900 font-bold mt-3">Sucursal:</label>
<select
    id="nuevaSucursal"
    wire:model.defer="nuevaSucursal"
    class="w-full border border-gray-300 rounded p-2 bg-white" required>
    <option value="">-- Seleccione Sucursal --</option>
    @for($i = 20; $i <= 30; $i++)
        <option value="{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}">
            {{ str_pad($i, 3, '0', STR_PAD_LEFT) }}
        </option>
    @endfor
</select>

<!-- ========================== -->
<!-- Select de Lista de precios -->
<label for="nuevaListaPrecios" class="block text-sm text-gray-900 font-bold mt-3">Lista de precios:</label>
<select
    id="nuevaListaPrecios"
    wire:model.defer="nuevaListaPrecios"
    class="w-full border border-gray-300 rounded p-2 bg-white" required>
    <option value="">-- Seleccione Lista --</option>
    <option value="001">001</option>
</select>

<!-- ========================== -->
<!-- Select de Punto de Envío -->
<label for="nuevoPuntoEnvio" class="block text-sm text-gray-900 font-bold mt-3">Punto de envío:</label>
<select
    id="nuevoPuntoEnvio"
    wire:model.defer="nuevoPuntoEnvio"
    class="w-full border border-gray-300 rounded p-2 bg-white" required>
    <option value="">-- Seleccione Punto de envío --</option>
    @for($i = 0; $i <= 20; $i++)
        <option value="{{ str_pad($i, 3, '0', STR_PAD_LEFT) }}">
            {{ str_pad($i, 3, '0', STR_PAD_LEFT) }}
        </option>
    @endfor
</select>

                @error('nuevoNit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                @if ($mensajeCliente)
                    <p class="mt-2 text-sm text-gray-600">{{ $mensajeCliente }}</p>
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancelar</button>
                </flux:modal.close>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Guardar
                </button>
            </div>
        </div>
    </form>
        </flux:modal>


    </div>

    @push('scripts')
        <script>
            function iniciarDataTable() {
                if ($.fn.DataTable.isDataTable('#pedidos')) {
                    $('#pedidos').DataTable().destroy();
                }

                $('#pedidos').DataTable({
                    responsive: false,
                    fixedHeader: true, //Encabezado fijo
                    scrollX: true, //Evita que escabezado se salga de la tabla
                    "order": [[0, "desc"]], //Orden de los datos por el ID en la tabla
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