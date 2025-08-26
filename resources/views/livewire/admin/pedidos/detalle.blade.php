<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">
            Detalle de pedidos: {{ $pedido->id }}
        </h2>
    </div>
    <div>
        <a type="button" href="{{ route('pedidos.index') }}" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
            Atrás
        </a>
    </div>


    <div class="mb-6 gap-2">
        <input type="number" step="0.01" wire:model.defer="descuentoGlobal" placeholder="Descuento global" class="rounded border px-3 py-1">
        <button wire:click="aplicarDescuentoGlobal" class="ml-2 px-3 py-1 bg-blue-600 hover:bg-blue-800 text-white rounded">Aplicar a todos</button>
        
        <button wire:click="guardarCambiosGeneral" class="px-2 py-1 bg-green-600 text-white font-semibold rounded hover:bg-green-800">
           Guardar cambios
        </button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabla de detalles -->
    <div class="w-4/5 mx-auto rounded-xl border border-gray-200 dark:border-zinc-700 shadow p-6">
        <div wire:ignore>
            <table id="detalle" class="w-3/4 table-auto text-sm text-left text-gray-700 dark:text-zinc-300" style="padding-top: 10px;">
                <thead class="text-xs text-zinc-50 uppercase bg-zinc-900 dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-2">Pedido id</th>
                        <th class="px-4 py-2">Referencia</th>
                        <th class="px-4 py-2">Descripción</th>
                        <th class="px-4 py-2">Marca</th>
                        <th class="px-4 py-2">Cantidad</th>
                        <th class="px-4 py-2">Precio Unitario</th>
                        <th class="px-4 py-2">Descuento</th>
                        <th class="px-4 py-2">Precio Total</th>
                        <th class="px-4 py-2">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detalles as $index => $detalle)
                        <tr wire:key="detalle-{{ $detalle['id'] }}" class="hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors ">
                            <td class="px-4 py-2 border-t-2">{{ $detalle['pedido_id'] }}</td>
                            <td class="px-4 py-2 border-t-2">{{ $detalle['referencia'] }}</td>
                            <td class="px-4 py-2 border-t-2">{{ $detalle['descripcion'] }}</td>
                            <td class="px-4 py-2 border-t-2">{{ $detalle['marca'] }}</td>
                            <td class="px-4 py-2 border-t-2">
                                <input type="number" wire:model.defer="detalles.{{ $loop->index }}.cantidad" class="w-20 rounded border px-2 py-1">
                            </td>
                            <td class="px-4 py-2 border-t-2">{{ number_format($detalle['precio_unitario']) }}</td>
                            <td class="px-4 py-2 border-t-2">
                                <input type="number" step="0.01" wire:model.defer="detalles.{{ $loop->index }}.descuento" class="w-20 rounded border px-2 py-1">
                            </td>
                            @php
                                $total = $detalle['cantidad'] * $detalle['precio_unitario'];
                            @endphp
                            <td class="px-4 py-2 border-t-2">{{number_format($total)}}</td>

                            <td class="px-3 py-3 border-t-2">
                                <button wire:click="guardarLinea({{ $loop->index }})" class="inline-block px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                <svg  class="size-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M48 96l0 320c0 8.8 7.2 16 16 16l320 0c8.8 0 16-7.2 16-16l0-245.5c0-4.2-1.7-8.3-4.7-11.3l33.9-33.9c12 12 18.7 28.3 18.7 45.3L448 416c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96C0 60.7 28.7 32 64 32l245.5 0c17 0 33.3 6.7 45.3 18.7l74.5 74.5-33.9 33.9L320.8 84.7c-.3-.3-.5-.5-.8-.8L320 184c0 13.3-10.7 24-24 24l-192 0c-13.3 0-24-10.7-24-24L80 80 64 80c-8.8 0-16 7.2-16 16zm80-16l0 80 144 0 0-80L128 80zm32 240a64 64 0 1 1 128 0 64 64 0 1 1 -128 0z"/></svg>
                                </button>
                                <button
                                    wire:click="eliminarItem({{ $detalle['id'] }})"
                                    class="px-2 py-1 bg-red-500 hover:bg-red-800 text-white font-semibold rounded"
                                    onclick="return confirm('¿Estás seguro de eliminar este item?')">
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

    

    @push('scripts')
        <script>
            function iniciarDataTable() {
                if ($.fn.DataTable.isDataTable('#detalle')) {
                    $('#detalle').DataTable().destroy();
                }

                $('#detalle').DataTable({
                    responsive: false,
                    fixedHeader: true, //Encabezado fijo
                    scrollX: true, //Evita que escabezado se salga de la tabla
                    "lengthMenu": [30, 50, 100],
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
