<div class="p-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Bancos recaudo</h1>
            <p class="text-sm text-gray-500">Administración de bancos y cuentas para recibos de caja.</p>
        </div>

        <button wire:click="crear"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold">
            Nuevo banco
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-green-100 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4">
        <input type="text"
            wire:model.live="search"
            placeholder="Buscar banco, cuenta o número..."
            class="w-full rounded-xl border-gray-300 px-4 py-2">
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Banco</th>
                    <th class="px-4 py-3 text-left">Cuenta</th>
                    <th class="px-4 py-3 text-left">Número</th>
                    <th class="px-4 py-3 text-left">Medio pago</th>
                    <th class="px-4 py-3 text-left">Tipo cuenta</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($bancos as $banco)
                    <tr class="border-t">
                        <td class="px-4 py-3">
                            <strong>{{ $banco->id_banco }}</strong><br>
                            {{ $banco->descripcion_banco }}
                        </td>

                        <td class="px-4 py-3">
                            <strong>{{ $banco->id_cuenta }}</strong><br>
                            {{ $banco->descripcion_cuenta }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $banco->numero_cuenta }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $banco->id_medio_pago }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $banco->tipo_cuenta }}
                        </td>

                        <td class="px-4 py-3">
                            <button wire:click="cambiarEstado({{ $banco->id }})"
                                class="px-3 py-1 rounded-full text-xs font-bold
                                {{ $banco->estado ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $banco->estado ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <button wire:click="editar({{ $banco->id }})"
                                class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                Editar
                            </button>

                            <button wire:click="eliminar({{ $banco->id }})"
                                onclick="confirm('¿Eliminar este banco?') || event.stopImmediatePropagation()"
                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                            No hay bancos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $bancos->links() }}
    </div>

    @if ($modal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl p-6">

                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-xl font-bold text-gray-800">
                        {{ $bancoId ? 'Editar banco' : 'Nuevo banco' }}
                    </h2>

                    <button wire:click="cerrarModal" class="text-gray-500 hover:text-red-600 text-xl">
                        ×
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="text-sm font-semibold text-gray-700">ID banco</label>
                        <input type="text" wire:model.defer="id_banco"
                            class="mt-1 w-full rounded-xl border-gray-300">
                        @error('id_banco') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Descripción banco</label>
                        <input type="text" wire:model.defer="descripcion_banco"
                            class="mt-1 w-full rounded-xl border-gray-300">
                        @error('descripcion_banco') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">ID cuenta</label>
                        <input type="text" wire:model.defer="id_cuenta"
                            class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Descripción cuenta</label>
                        <input type="text" wire:model.defer="descripcion_cuenta"
                            class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Número cuenta</label>
                        <input type="text" wire:model.defer="numero_cuenta"
                            class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">ID medio de pago</label>
                        <input type="text" wire:model.defer="id_medio_pago"
                            class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Tipo cuenta</label>
                        <input type="number" wire:model.defer="tipo_cuenta"
                            class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Estado</label>
                        <select wire:model.defer="estado"
                            class="mt-1 w-full rounded-xl border-gray-300">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="cerrarModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-xl">
                        Cancelar
                    </button>

                    <button wire:click="guardar"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold">
                        Guardar
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>