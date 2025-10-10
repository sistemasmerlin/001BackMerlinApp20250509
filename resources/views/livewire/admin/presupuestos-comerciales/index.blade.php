<div class="space-y-6">


    {{-- Mensaje éxito --}}
    @if (session()->has('success'))
        <div class="mb-2 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (!empty($erroresImport))
        <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold mb-1">Errores de importación:</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($erroresImport as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Header + Botones (siempre fuera de IFs) --}}
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Presupuestos comerciales</h1>

        <div class="flex gap-2">
            <a href="{{ route('presupuestos.plantilla') }}"
               class="px-4 py-2 bg-zinc-600 hover:bg-zinc-700 text-white rounded-lg">Plantilla</a>

            <button wire:click="abrirImport"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg"
                    wire:loading.attr="disabled" wire:target="abrirImport">
                Importar
            </button>

            <button wire:click="crear"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                + Nuevo
            </button>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex gap-3">
        <input type="text" maxlength="6" wire:model.live="fPeriodo" placeholder="Periodo YYYYMM"
               class="flex-1 border rounded px-3 py-2">
        <input type="text" wire:model.live="fAsesor" placeholder="Código asesor"
               class="flex-1 border rounded px-3 py-2">
    </div>

    <!-- Tabla -->
    <div class="w-full overflow-x-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700">
        <table class="min-w-full text-sm text-left text-gray-700 dark:text-zinc-300">
            <thead class="text-xs text-zinc-50 bg-zinc-950 uppercase dark:bg-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-center">#</th>
                    <th class="px-4 py-3">Periodo</th>
                    <th class="px-4 py-3">Asesor</th>
                    <th class="px-4 py-3">Marca</th>
                    <th class="px-4 py-3">Categoría</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Clasificación</th>
                    <th class="px-4 py-3 text-right">Presupuesto</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($presupuestos as $i => $p)
                <tr class="border-b border-gray-200">
                    <td class="px-4 py-2 text-center">{{ $presupuestos->firstItem() + $i }}</td>
                    <td class="px-4 py-2">{{ $p->periodo }}</td>
                    <td class="px-4 py-2">{{ $p->codigo_asesor }}</td>
                    <td class="px-4 py-2">{{ $p->marca ?? '—' }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded bg-gray-100 dark:bg-zinc-800">
                            {{ $p->categoria }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $p->tipo_presupuesto }}</td>
                    <td class="px-4 py-2">{{ $p->clasificacion_asesor ?? '—' }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($p->presupuesto, 0) }}</td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-2">
                            <button wire:click="editar({{ $p->id }})"
                                class="px-3 py-1 bg-blue-500 hover:bg-blue-700 text-white rounded-lg"
                                title="Editar">
                                ✏️
                            </button>
                            <button wire:click="confirmarEliminar({{ $p->id }})"
                                class="px-3 py-1 bg-red-500 hover:bg-red-700 text-white rounded-lg"
                                title="Eliminar">
                                🗑️
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">Sin resultados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $presupuestos->links() }}
    </div>

    <!-- Modal Crear/Editar -->
    @if($modal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-5 w-full max-w-2xl border border-zinc-200 dark:border-zinc-700">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">
                    {{ $modoEditar ? 'Editar presupuesto' : 'Nuevo presupuesto' }}
                </h2>
                <button class="text-gray-500" wire:click="cerrarModal">✖</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium">Periodo (YYYYMM)</label>
                    <input type="text" maxlength="6" wire:model.defer="periodo" class="w-full border rounded px-3 py-2">
                    @error('periodo') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium">Código asesor</label>
                    <input type="text" wire:model.defer="codigo_asesor" class="w-full border rounded px-3 py-2">
                    @error('codigo_asesor') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium">Tipo presupuesto</label>
                    <input type="text" wire:model.defer="tipo_presupuesto" class="w-full border rounded px-3 py-2" placeholder="valor | unidades">
                    @error('tipo_presupuesto') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium">Presupuesto</label>
                    <input type="number" step="0.01" min="0" wire:model.defer="presupuesto" class="w-full border rounded px-3 py-2">
                    @error('presupuesto') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium">Marca (opcional)</label>
                    <input type="text" wire:model.defer="marca" class="w-full border rounded px-3 py-2">
                    @error('marca') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium">Categoría</label>
                    <select wire:model.defer="categoria" class="w-full border rounded px-3 py-2">
                        <option value="llantas">llantas</option>
                        <option value="repuestos">repuestos</option>
                    </select>
                    @error('categoria') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium">Clasificación asesor (opcional)</label>
                    <input type="text" wire:model.defer="clasificacion_asesor" class="w-full border rounded px-3 py-2">
                    @error('clasificacion_asesor') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div class="md:col-span-3 text-xs text-gray-500">
                    * No se permiten duplicados para <b>asesor + periodo + marca + categoría + tipo</b>.
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded"
                    wire:click="cerrarModal">
                    Cancelar
                </button>
                <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded"
                    wire:click="guardar">
                    Guardar
                </button>
            </div>
        </div>
    </div>

        @endif

    {{-- ✅ Modal Importación (independiente) --}}
    @if($modalImport)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-5 w-full max-w-lg border border-zinc-200 dark:border-zinc-700">
                <h2 class="text-xl font-bold mb-3">Importar presupuestos</h2>

                <p class="text-sm text-gray-600 mb-3">
                    Formato: <b>.xlsx</b> o <b>.csv</b>.<br>
                    Encabezados:
                    <code>periodo, codigo_asesor, tipo_presupuesto, presupuesto, marca, categoria, clasificacion_asesor</code>
                </p>

                <div class="space-y-2">
                    <input type="file" wire:model="archivo" accept=".xlsx,.csv,.txt"
                           class="w-full border rounded px-3 py-2">
                    @error('archivo') <small class="text-red-600">{{ $message }}</small> @enderror
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded"
                            wire:click="$set('modalImport', false)">Cancelar</button>
                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded"
                            wire:click="procesarImport">Importar</button>
                </div>
            </div>
        </div>
    @endif



    <!-- Modal Confirmación Eliminar -->
    @if($confirmarBorrar)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-5 w-full max-w-md border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-semibold mb-3">¿Eliminar presupuesto?</h3>
            <p class="text-sm text-gray-600 mb-5">Esta acción no se puede deshacer.</p>
            <div class="flex justify-end gap-2">
                <button class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded"
                    wire:click="$set('confirmarBorrar', false)">
                    Cancelar
                </button>
                <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded"
                    wire:click="eliminar">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>