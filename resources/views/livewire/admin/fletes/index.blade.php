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
        @endif

        <form wire:submit.prevent="importarFlete" enctype="multipart/form-data">
            <div class="flex mb-4">
                <input type="file" wire:model="excel_fletes" accept=".xls,.xlsx" class="mt-2 bg-yellow-100 block  border border-gray-300 rounded-xl shadow mx-2" required>
                <button type="submit" class="bg-green-500 hover:bg-green-700 font-bold text-white px-4 py-1 rounded">Importar</button>
            </div>
        </form>
        
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
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

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
