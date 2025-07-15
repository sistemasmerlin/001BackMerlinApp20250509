<div class="space-y-6">
    <!-- Encabezado y Botón -->

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Listar Pedidos</h1>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="w-full mx-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-6">
        <table id="pedidos" class="w-3/4 table-auto text-sm text-left text-gray-700 dark:text-zinc-300" style="padding-top: 10px;">
            <thead class="text-xs text-zinc-50 uppercase bg-gray-900 dark:bg-zinc-700">
                <tr>
                    <th class="px-4 py-3">Prefijo</th>
                    <th class="px-4 py-3">Id</th>
                    <th class="px-4 py-3">OC</th>
                    <th class="px-4 py-3">Código Asesor</th>
                    <th class="px-4 py-3">Nombre Asesor</th>
                    <th class="px-4 py-3">Ver</th>
                    <th class="px-4 py-3">Enviar Siesa</th>
                    <th class="px-4 py-3">Nit Cliente</th>
                    <th class="px-4 py-3">Razón Social</th>
                    <th class="px-4 py-3">Lista Precio</th>
                    <th class="px-4 py-3">Sucursal</th>
                    <th class="px-4 py-3">Punto de Envío</th>
                    <th class="px-4 py-3">Cond Pago</th>
                    <th class="px-4 py-3">Valor Flete</th>
                    <th class="px-4 py-3">Estado Siesa</th>
                    <th class="px-4 py-3">Correo Cliente</th>
                    <th class="px-4 py-3">Observaciones</th>
                    <th class="px-4 py-3">Nota ERP</th>
                    <th class="px-4 py-3">Fecha Pedido</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidos as $pedido)
                    <tr class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                        <td class="px-4 py-2">{{ $pedido->prefijo }}</td>
                        <td class="px-4 py-2">{{ $pedido->id }}</td>
                        <td class="px-4 py-2">{{ $pedido->orden_compra }}</td>
                        <td class="px-4 py-2">{{ $pedido->codigo_asesor }}</td>
                        <td class="px-4 py-2">{{ $pedido->nombre_asesor }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.pedidos.detalle', $pedido->id) }}" class="inline-flex px-3 py-1 bg-green-500 hover:bg-green-800 text-white font-semibold rounded-lg">
                                <svg class="size-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                    <path d="M288 144a110.9 110.9 0 0 0 -31.2 5 a55.4 55.4 0 0 1 7.2 27 a56 56 0 0 1 -56 56 a55.4 55.4 0 0 1 -27-7.2A111.7 111.7 0 1 0 288 144zm284.5 97.4C518.3 135.6 410.9 64 288 64S57.7 135.6 3.5 241.4a32.4 32.4 0 0 0 0 29.2C57.7 376.4 165.1 448 288 448s230.3-71.6 284.5-177.4a32.4 32.4 0 0 0 0-29.2zM288 400c-98.7 0-189.1-55-237.9-144C98.9 167 189.3 112 288 112s189.1 55 237.9 144C477.1 345 386.7 400 288 400z"/>
                                </svg>
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('pedidos.enviar', $pedido->id) }}"
                            class="inline-block px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <svg  class="size-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"> <path d="M440 6.5L24 246.4c-34.4 19.9-31.1 70.8 5.7 85.9L144 379.6V464c0 46.4 59.2 65.5 86.6 28.6l43.8-59.1 111.9 46.2c5.9 2.4 12.1 3.6 18.3 3.6 8.2 0 16.3-2.1 23.6-6.2 12.8-7.2 21.6-20 23.9-34.5l59.4-387.2c6.1-40.1-36.9-68.8-71.5-48.9zM192 464v-64.6l36.6 15.1L192 464zm212.6-28.7l-153.8-63.5L391 169.5c10.7-15.5-9.5-33.5-23.7-21.2L155.8 332.6 48 288 464 48l-59.4 387.3z"/></svg>
                            </a>
                        </td>
                        <td class="px-4 py-2">{{ $pedido->nit }}</td>
                        <td class="px-4 py-2">{{ $pedido->razon_social }}</td>
                        <td class="px-4 py-2">{{ $pedido->lista_precio }}</td>
                        <td class="px-4 py-2">{{ $pedido->id_sucursal }}</td>
                        <td class="px-4 py-2">{{ $pedido->direccionEnvio['id_punto_envio'] }}</td>
                        <td class="px-4 py-2">{{ $pedido->condicion_pago }}</td>
                        <td class="px-4 py-2">{{ $pedido->flete }}</td>
                        <td class="px-4 py-2">{{ $pedido->estado_siesa }}</td>
                        <td class="px-4 py-2">{{ $pedido->correo_cliente }}</td>
                        <td class="px-4 py-2">{{ $pedido->observaciones }}</td>
                        <td class="px-4 py-2">{{ $pedido->nota }}</td>
                        <td class="px-4 py-2">{{ $pedido->fecha_pedido }}</td>
                        
                    </tr>
                @endforeach
            </tbody> 
        </table>
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
                    "lengthMenu": [15, 50, 100],
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