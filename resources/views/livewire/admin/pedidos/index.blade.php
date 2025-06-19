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
    
    <div class="overflow-x-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700">
        <table class="w-full table-auto text-sm text-left text-gray-700 dark:text-zinc-300">
            <thead class="text-xs text-gray-600 uppercase bg-gray-100 dark:bg-zinc-700">
                <tr>
                    <th class="px-4 py-3">Prefijo</th>
                    <th class="px-4 py-3">Id</th>
                    <th class="px-4 py-3">OC</th>
                    <th class="px-4 py-3">Código Asesor</th>
                    <th class="px-4 py-3">Nombre Asesor</th>
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
                    <th class="px-4 py-3">Ver</th>
                    <th class="px-4 py-3">Enviar Siesa</th>
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
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.pedidos.detalle', $pedido->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium transition">
                                Ver
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('pedidos.enviar', $pedido->id) }}"
                            class="inline-block px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Enviar pedido
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody> 
        </table>
    </div>
</div>