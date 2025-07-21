<div>
    <input type="text" wire:model="idTercero">
    <button wire:click="buscar" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold px-4 py-2 rounded">Buscar</button>

    @if (!empty($clientes))
    <div wire:ignore>
        <table class="table-auto w-full border mt-4">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2">Tercero</th>
                    <th class="px-4 py-2">NIT</th>
                    <th class="px-4 py-2">Última Factura</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clientes as $cliente)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $cliente['razon_social'] }}</td>
                        <td class="px-4 py-2">{{ $cliente['nit'] }}</td>
                        <td class="px-4 py-2">{{ $cliente['ultima_factura'] ?? 'Sin datos' }}</td>
                        <td class="px-4 py-2">
                            <button wire:click="verSucursales('{{ $cliente['tercero_id'] }}')" class="bg-indigo-500 text-white px-3 py-1 rounded">
                                Ver Sucursales
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Modal fuera del foreach -->
    @if ($modalSucursales)
        <div wire:key="modal-sucursales" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-lg w-full max-w-3xl border border-gray-300 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-800 dark:text-white mb-4">Sucursales del Cliente</h2>

                @forelse ($sucursalesSeleccionadas as $sucursal)
                    <div class="mb-3 border-b pb-3">
                        <p><strong>{{ $sucursal['descripcion_sucursal'] }}</strong></p>
                        <p>Cartera: {{ $sucursal['cartera'] }} | Pedidos: {{ $sucursal['pedidos'] }}</p>
                        <p>Cond. Pago: {{ $sucursal['cond_pago'] }} | Cupo: {{ $sucursal['cupo_credito'] }}</p>
                    </div>
                @empty
                    <p class="text-zinc-600 dark:text-zinc-300">No hay sucursales disponibles.</p>
                @endforelse

                <div class="text-end mt-4">
                    <button wire:click="$set('modalSucursales', false)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
