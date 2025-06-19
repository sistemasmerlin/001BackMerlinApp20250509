<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">
            Detalle de pedidos: {{ $pedido->id }}
        </h2>
    </div>

    <div class="mb-4">
        <input type="number" step="0.01" wire:model.defer="descuentoGlobal" placeholder="Descuento global" class="rounded border px-3 py-1">
        <button wire:click="aplicarDescuentoGlobal" class="ml-2 px-3 py-1 bg-blue-600 text-white rounded">Aplicar a todos</button>
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
                    <th class="px-4 py-2">Pedido id</th>
                    <th class="px-4 py-2">Referencia</th>
                    <th class="px-4 py-2">Descripción</th>
                    <th class="px-4 py-2">Cantidad</th>
                    <th class="px-4 py-2">Precio Unitario</th>
                    <th class="px-4 py-2">Descuento</th>
                    <th class="px-4 py-2">Cambiar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalles as $index => $detalle)
                    <tr wire:key="detalle-{{ $detalle['id'] }}" class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700">
                        <td class="px-4 py-2">{{ $detalle['pedido_id'] }}</td>
                        <td class="px-4 py-2">{{ $detalle['referencia'] }}</td>
                        <td class="px-4 py-2">{{ $detalle['descripcion'] }}</td>
                        <td class="px-4 py-2">
                            <input type="number" wire:model.defer="detalles.{{ $loop->index }}.cantidad" class="w-20 rounded border px-2 py-1">
                        </td>
                        <td class="px-4 py-2">{{ $detalle['precio_unitario'] }}</td>
                                                <td class="px-4 py-2">
                            <input type="number" step="0.01" wire:model.defer="detalles.{{ $loop->index }}.descuento" class="w-20 rounded border px-2 py-1">
                        </td>
                        <td class="px-4 py-2">
                            <button wire:click="guardarLinea({{ $loop->index }})" class="text-blue-600 hover:underline">Guardar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
