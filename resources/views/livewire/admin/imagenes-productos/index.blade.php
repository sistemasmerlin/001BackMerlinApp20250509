<div class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
            Imágenes de productos
        </h1>

        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Consulte una referencia y administre sus cuatro imágenes.
        </p>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3
                    text-sm text-emerald-800 dark:border-emerald-900
                    dark:bg-emerald-950 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm
                dark:border-zinc-800 dark:bg-zinc-950">
        <form wire:submit="buscar" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="buscar-referencia"
                       class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Referencia del producto
                </label>

                <input id="buscar-referencia"
                       type="text"
                       wire:model.live.debounce.400ms="buscarReferencia"
                       placeholder="Ejemplo: 612000011"
                       maxlength="100"
                       class="mt-1 w-full rounded-lg border border-zinc-300 bg-white
                              px-3 py-2 text-sm text-zinc-900
                              dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">

                @error('buscarReferencia')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    wire:loading.attr="disabled"
                    class="rounded-lg bg-zinc-900 px-5 py-2 text-sm font-medium
                           text-white disabled:opacity-50
                           dark:bg-white dark:text-zinc-900">
                Consultar
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200
            bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

    <div class="flex flex-wrap items-center justify-between gap-3
                border-b border-zinc-200 p-4 dark:border-zinc-800">
        <div>
            <h2 class="font-semibold text-zinc-900 dark:text-white">
                Referencias con imágenes
            </h2>

            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ $totalReferencias }} referencias ·
                {{ $totalImagenes }} archivos de imagen
            </p>
        </div>

        <button type="button"
                wire:click="$refresh"
                wire:loading.attr="disabled"
                class="rounded-lg border border-zinc-300 px-3 py-2
                       text-sm text-zinc-700 disabled:opacity-50
                       dark:border-zinc-700 dark:text-zinc-200">
            Actualizar listado
        </button>
    </div>

    <div class="max-h-96 overflow-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="sticky top-0 bg-zinc-100 text-xs uppercase
                          text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">
                <tr>
                    <th class="px-4 py-3">Referencia</th>
                    <th class="px-4 py-3">Imágenes</th>
                    <th class="px-4 py-3">Posiciones</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acción</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($referenciasListado as $fila)
                    <tr wire:key="referencia-{{ $fila['referencia'] }}"
                        class="text-zinc-700 hover:bg-zinc-50
                               dark:text-zinc-300 dark:hover:bg-zinc-900">
                        <td class="px-4 py-3 font-medium">
                            {{ $fila['referencia'] }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $fila['cantidad'] }} de 4

                            @if ($fila['archivos'] > $fila['cantidad'])
                                <p class="mt-1 text-xs text-amber-600">
                                    {{ $fila['archivos'] }} archivos:
                                    hay posiciones con varias versiones.
                                </p>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            {{ implode(', ', $fila['posiciones']) }}
                        </td>

                        <td class="px-4 py-3">
                            @if ($fila['cantidad'] === 4)
                                <span class="text-emerald-600 dark:text-emerald-400">
                                    Completo
                                </span>
                            @else
                                <span class="text-amber-600 dark:text-amber-400">
                                    Faltan {{ 4 - $fila['cantidad'] }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                    wire:click="administrar(@js($fila['referencia']))"
                                    wire:loading.attr="disabled"
                                    class="rounded-lg border border-zinc-300
                                           px-3 py-1.5 text-xs font-medium
                                           disabled:opacity-50 dark:border-zinc-700">
                                Administrar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"
                            class="px-4 py-8 text-center text-zinc-500">
                            No se encontraron referencias con imágenes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

    @if ($referencia !== '')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-zinc-900 dark:text-white">
                    Referencia: {{ $referencia }}
                </h2>

                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ collect($imagenes)->filter(fn ($archivos) => count($archivos) > 0)->count() }}
                    de 4 posiciones con imagen.
                </p>
            </div>

            <button type="button"
                    wire:click="$refresh"
                    wire:loading.attr="disabled"
                    class="rounded-lg border border-zinc-300 px-3 py-2 text-sm
                           text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                Actualizar
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @for ($posicion = 1; $posicion <= 4; $posicion++)
                @php
                    $existentes = $imagenes[$posicion] ?? [];
                @endphp

                <div wire:key="imagen-{{ $referencia }}-{{ $posicion }}-{{ $versionInputs }}"
                     class="overflow-hidden rounded-xl border border-zinc-200
                            bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

                    <div class="flex items-center justify-between border-b
                                border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <span class="text-sm font-semibold text-zinc-900 dark:text-white">
                            Imagen {{ $posicion }}
                        </span>

                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ count($existentes) ? 'Disponible' : 'Sin imagen' }}
                        </span>
                    </div>

                    <div class="space-y-4 p-4">
                        @forelse ($existentes as $existente)
                            <div>
                                <a href="{{ $existente['url'] }}"
                                   target="_blank"
                                   rel="noopener noreferrer">
                                    <img src="{{ $existente['url'] }}"
                                         alt="Imagen {{ $posicion }} de {{ $referencia }}"
                                         class="h-48 w-full rounded-lg bg-white object-contain">
                                </a>

                                <p class="mt-2 break-all text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $existente['nombre'] }}
                                    · {{ $existente['kb'] }} KB
                                </p>
                            </div>
                        @empty
                            <div class="flex h-48 items-center justify-center rounded-lg
                                        border-2 border-dashed border-zinc-200
                                        text-sm text-zinc-400 dark:border-zinc-700">
                                Sin imagen cargada
                            </div>
                        @endforelse

                        @if (count($existentes) > 1)
                            <p class="text-xs text-amber-600 dark:text-amber-400">
                                Hay varias versiones para esta posición.
                                Al guardar se conservará únicamente la nueva.
                            </p>
                        @endif

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                {{ count($existentes) ? 'Seleccionar reemplazo' : 'Seleccionar imagen' }}
                            </label>

                            <input type="file"
                                   wire:model="archivos.{{ $posicion }}"
                                   accept="image/jpeg,image/png,image/webp"
                                   class="mt-2 block w-full text-xs text-zinc-600
                                          dark:text-zinc-300">

                            <p class="mt-2 text-xs text-zinc-400">
                                JPG, PNG o WebP. Máximo 5 MB.
                            </p>
                        </div>

                        <p wire:loading
                           wire:target="archivos.{{ $posicion }}"
                           class="text-xs text-blue-600">
                            Cargando archivo…
                        </p>

                        @error("archivos.$posicion")
                            <p class="text-xs text-rose-600">{{ $message }}</p>
                        @enderror

                        @if (isset($archivos[$posicion]))
                            <p class="text-xs font-medium text-emerald-600">
                                Archivo seleccionado. Pulse Guardar imagen para aplicarlo.
                            </p>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <button type="button"
                                    wire:click="guardar({{ $posicion }})"
                                    wire:loading.attr="disabled"
                                    @disabled(!isset($archivos[$posicion]))
                                    class="rounded-lg bg-zinc-900 px-3 py-2 text-xs
                                           font-medium text-white disabled:opacity-40
                                           dark:bg-white dark:text-zinc-900">
                                Guardar imagen
                            </button>

                            @if (count($existentes))
                                <button type="button"
                                        wire:click="eliminar({{ $posicion }})"
                                        wire:confirm="¿Desea eliminar la imagen {{ $posicion }} de la referencia {{ $referencia }}?"
                                        wire:loading.attr="disabled"
                                        class="rounded-lg border border-rose-200 px-3 py-2
                                               text-xs font-medium text-rose-600
                                               disabled:opacity-40 dark:border-rose-900
                                               dark:text-rose-400">
                                    Eliminar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <p class="text-xs text-zinc-500 dark:text-zinc-400">
            Los archivos se guardan como {{ $referencia }}-1,
            {{ $referencia }}-2, {{ $referencia }}-3 y {{ $referencia }}-4,
            conservando el formato de la nueva imagen.
        </p>
    @endif

    <div wire:loading
         wire:target="guardar,eliminar,buscar"
         class="text-sm text-zinc-500 dark:text-zinc-400">
        Procesando…
    </div>
</div>