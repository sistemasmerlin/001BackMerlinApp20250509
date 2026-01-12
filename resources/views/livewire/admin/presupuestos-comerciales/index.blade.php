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
                    class="px-4 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-900 text-white font-medium shadow"
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


        {{-- ✅ Modal Importación (estilo limpio tipo "cartera") --}}
        @if($modalImport)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-3"
            x-data
            x-on:keydown.escape.window="$wire.set('modalImport', false)"
        >
            <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
            {{-- Header --}}
            <div class="flex items-start justify-between gap-3 border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    {{-- icono upload --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5 5 5M12 5v11" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Importar presupuestos</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Sube un archivo <b>.xlsx</b> o <b>.csv</b> con el formato correcto.
                    </p>
                </div>
                </div>

                <button
                type="button"
                class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                wire:click="$set('modalImport', false)"
                aria-label="Cerrar"
                >
                ✕
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 py-4 space-y-4">
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800/40 dark:text-zinc-200">
                <div class="font-medium mb-1">Encabezados requeridos</div>
                <code class="block text-xs leading-relaxed text-zinc-700 dark:text-zinc-200">
                    periodo, codigo_asesor, tipo_presupuesto, presupuesto, marca, categoria, clasificacion_asesor
                </code>
                </div>

                {{-- File input --}}
                <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    Archivo
                </label>

                <input
                    type="file"
                    wire:model="archivo"
                    accept=".xlsx,.csv,.txt"
                    class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-800 shadow-sm
                        file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-medium
                        hover:file:bg-zinc-200
                        focus:outline-none focus:ring-2 focus:ring-red-500/40
                        dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100
                        dark:file:bg-zinc-800 dark:hover:file:bg-zinc-700"
                />

                @error('archivo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                {{-- Loading pequeño --}}
                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400" wire:loading wire:target="archivo">
                    Cargando archivo…
                </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 border-t border-zinc-100 bg-white px-5 py-4 dark:border-zinc-800 dark:bg-zinc-900">
                <button
                type="button"
                class="rounded-xl border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50
                        dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                wire:click="$set('modalImport', false)"
                >
                Cancelar
                </button>

                <button
                type="button"
                class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white shadow
                        hover:bg-zinc-800
                        disabled:opacity-60 disabled:cursor-not-allowed
                        dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                wire:click="procesarImport"
                wire:loading.attr="disabled"
                wire:target="procesarImport"
                >
                <span wire:loading.remove wire:target="procesarImport">Importar</span>
                <span wire:loading wire:target="procesarImport">Importando…</span>
                </button>
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