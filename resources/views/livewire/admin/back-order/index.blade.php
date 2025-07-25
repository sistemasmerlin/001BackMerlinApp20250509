<div class="space-y-6">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Informe Backorders</h1>
    </div>

    <div class="flex items-center space-x-4 mb-4">
        <div>
            <label class="block text-sm text-gray-700 dark:text-gray-300">Desde</label>
            <input type="date" wire:model="fechaInicio" class="border rounded p-1 text-sm w-full">
        </div>

        <div>
            <label class="block text-sm text-gray-700 dark:text-gray-300">Hasta</label>
            <input type="date" wire:model="fechaFin" class="border rounded p-1 text-sm w-full">
        </div>

        <button wire:click="filtrar"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 mt-5">
            Buscar
        </button>

        <button wire:click="exportarExcel"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 mt-5">
            Exportar Excel
        </button>
    </div>


    @if (session()->has('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
        {{ session('success') }}
    </div>
    @endif

    <div class="overflow-x-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-6">
        <div wire:ignore>
            <table id="backorder" class="w-full table-auto text-sm text-left text-gray-700 dark:text-zinc-300" style="padding-top: 10px;">
                <thead class="text-xs text-zinc-50 bg-zinc-950 uppercase dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-3">Id</th>
                        <th class="px-4 py-3">Fecha Creacion</th>
                        <th class="px-4 py-3">Nit</th>
                        <th class="px-4 py-3">Razón Social</th>
                        <th class="px-4 py-3">Cond Pago</th>
                        <th class="px-4 py-3">Lista Precio</th>
                        <th class="px-4 py-3">Sucursal</th>
                        <th class="px-4 py-3">Nota ERP</th>
                        <th class="px-4 py-3">Valor Flete</th>
                        <th class="px-4 py-3">Código Asesor</th>
                        <th class="px-4 py-3">Nombre Asesor</th>
                        <th class="px-4 py-3">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backorders as $backorder)
                    <tr>
                        <td>{{ $backorder->id }}</td>
                        <td>{{ $backorder->created_at}}</td>
                        <td style="text-align:center">{{ $backorder->pedido->nit }}</td>
                        <td>{{ $backorder->pedido->razon_social }}</td>
                        <td style="text-align:center">{{ $backorder->pedido->condicion_pago }}</td>
                        <td style="text-align:center">{{ $backorder->pedido->lista_precio }}</td>
                        <td style="text-align:center">{{ $backorder->pedido->id_sucursal }}</td>
                        <td>{{ $backorder->pedido->nota }}</td>
                        <td style="text-align: right;">{{ number_format($backorder->pedido->flete) }}</td>
                        <td style="text-align:center">{{ $backorder->pedido->codigo_asesor }}</td>
                        <td>{{ $backorder->pedido->nombre_asesor }}</td>
                        <td>
                            <button
                                wire:click="mostrarDetalle({{ $backorder->id }})"
                                class="px-2 py-1 my-4  bg-blue-500 text-white rounded hover:bg-blue-600">
                                Ver Detalle
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>   
    </div>


    @if($modalBackorder)

    <div class="fixed inset-0 flex items-center justify-center bg-black/10 z-50 p-4">

        <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl shadow-xl w-full max-w-2xl w-full relative border border-gray-200 dark:border-zinc-700">

            <button
                wire:click="cerrarDetalle"
                class="absolute top-3 right-3 text-gray-500 hover:text-red-500 text-xl"
                title="Cerrar">
                ✖
            </button>

            <h2 class="text-lg font-bold mb-4 text-center text-gray-800 dark:text-white">
                Detalle del Backorder #{{ $modalBackorder->id }}
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 dark:text-zinc-300 border border-gray-200 dark:border-zinc-700">
                    <thead class="bg-gray-100 dark:bg-zinc-700 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="px-3 py-2">Referencia</th>
                            <th class="px-3 py-2">Descripción</th>
                            <th class="px-3 py-2">Cantidad</th>
                            <th class="px-3 py-2">Descuento</th>
                            <th class="px-3 py-2">Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modalBackorder->detalles as $item)
                        <tr class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                            <td class="px-3 py-2">{{ $item->referencia }}</td>
                            <td class="px-3 py-2">{{ $item->descripcion }}</td>
                            <td class="px-3 py-2">{{ $item->cantidad }}</td>
                            <td class="px-3 py-2">{{ $item->descuento }}</td>
                            <td class="px-3 py-2">{{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    @endif

    @push('scripts')
        <script>
            function iniciarDataTable() {
                if ($.fn.DataTable.isDataTable('#backorder')) {
                    $('#backorder').DataTable().destroy();
                }

                $('#backorder').DataTable({
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