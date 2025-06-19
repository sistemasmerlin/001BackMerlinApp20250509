<div class="space-y-6">

    <!-- Encabezado <div>
    {{-- Success is as dangerous as failure. --}}
</div>
 -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">
            Flete por ciudad
        </h2>

        <form wire:submit.prevent="importarCsv" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Flete por ciudad</label>
                <input type="file" wire:model="archivoCsv" class="mt-1 block w-full text-sm text-gray-500" />
                @error('archivoCsv') 
                    <span class="text-red-600 text-xs">{{ $message }}</span> 
                @enderror
            </div>

            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                Importar CSV
            </button>
            
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
    <div class="overflow-x-auto rounded border border-gray-200 dark:border-zinc-700 shadow">
        <table class="w-full table-auto text-sm text-left text-gray-700 dark:text-zinc-300">
            <thead class="text-xs text-gray-600 uppercase bg-gray-100 dark:bg-zinc-700">
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
