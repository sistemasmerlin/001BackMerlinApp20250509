<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
            Detalle de Promoción: {{$promocion->id}} - {{ $promocion->nombre }}
        </h2>
    </div>

    
    <div class="flex justify-between items-center mb-4">
        <form wire:submit.prevent="importarCsv" enctype="multipart/form-data">
            <input type="file" wire:model="archivoCsv" accept=".csv,.xls" class="mb-2">
            @error('archivoCsv') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

            <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                Importar CSV
            </button>
        </form>
    </div>

    <!-- Espacio para subir plano excel -->
    <div >
        <h1 class="text-2xl font-bold">Excel Promociones</h1>
        @livewire('admin.promociones.promocion-detalle')
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
    <div class="overflow-x-auto rounded border border-gray-200 dark:border-zinc-700 shadow">
        <table class="w-full table-auto text-sm text-left text-gray-700 dark:text-zinc-300">
            <thead class="text-xs text-gray-600 uppercase bg-gray-100 dark:bg-zinc-700">
                <tr>
                    <th class="px-4 py-2">id_prom</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">Descripción</th>
                    <th class="px-4 py-2">Acumulado</th>
                    <th class="px-4 py-2">Modelo</th>
                    <th class="px-4 py-2">Desde</th>
                    <th class="px-4 py-2">Hasta</th>
                    <th class="px-4 py-2">% Descuento</th>
                    <th class="px-4 py-2">Creado por</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalles as $detalle)
                    <tr class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                        <td class="px-4 py-2">{{ $detalle->promocion_id }}</td>
                        <td class="px-4 py-2">{{ $detalle->tipo }}</td>
                        <td class="px-4 py-2">{{ $detalle->descripcion }}</td>
                        <td class="px-4 py-2">{{ $detalle->acumulado }}</td>
                        <td class="px-4 py-2">{{ $detalle->modelo }}</td>
                        <td class="px-4 py-2">{{ round($detalle->desde) }}</td>
                        <td class="px-4 py-2">{{ round($detalle->hasta) }}</td>
                        <td class="px-4 py-2">{{ round($detalle->descuento) }}%</td>
                        <td class="px-4 py-2">{{ $detalle->creado_por }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
