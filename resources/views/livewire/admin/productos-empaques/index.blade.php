<div class="space-y-6 p-4 sm:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
                Unidades por empaque
            </h1>

            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Consulte e importe la cantidad de unidades de cada referencia.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button
                wire:click="plantilla"
                wire:loading.attr="disabled"
            >
                Descargar plantilla
            </flux:button>

            <flux:button
                variant="primary"
                wire:click="exportar"
                wire:loading.attr="disabled"
            >
                Exportar Excel
            </flux:button>
        </div>
    </div>

    @if ($mensaje)
        <div
            role="status"
            class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800"
        >
            {{ $mensaje }}
        </div>
    @endif

    @if ($errors->any())
        <div
            role="alert"
            class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"
        >
            <p class="font-semibold">Revise lo siguiente:</p>

            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Referencias registradas
            </p>

            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">
                {{ number_format($totalReferencias, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Resultados de la búsqueda
            </p>

            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">
                {{ number_format($productos->total(), 0, ',', '.') }}
            </p>
        </div>
    </div>

    <form
        wire:submit="importar"
        x-data="{ uploading: false }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        x-on:livewire-upload-cancel="uploading = false"
        class="space-y-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div>
            <h2 class="font-semibold text-zinc-900 dark:text-white">
                Importar referencias
            </h2>

            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Las referencias existentes se actualizan y las nuevas se crean.
                No se eliminan registros.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label
                    for="archivo-empaques"
                    class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                >
                    Archivo Excel · máximo 5 MB
                </label>

                <input
                    id="archivo-empaques"
                    type="file"
                    wire:model="archivo"
                    wire:key="archivo-empaques-{{ $archivo ? 'seleccionado' : 'vacio' }}"
                    accept=".xlsx,.xls"
                    class="block w-full rounded-lg border border-zinc-300 p-2 text-sm dark:border-zinc-600 dark:text-white"
                >
            </div>

            <flux:button
                type="submit"
                variant="primary"
                x-bind:disabled="uploading"
                wire:loading.attr="disabled"
                wire:target="archivo,importar"
            >
                Importar archivo
            </flux:button>
        </div>

        <p x-show="uploading" class="text-sm text-blue-600">
            Subiendo archivo…
        </p>

        <p wire:loading wire:target="importar" class="text-sm text-blue-600">
            Validando e importando referencias…
        </p>
    </form>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
            <flux:input
                wire:model.live.debounce.350ms="buscar"
                placeholder="Buscar por referencia o descripción"
                aria-label="Buscar por referencia o descripción"
            />

            <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                La exportación incluye los resultados del filtro actual.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <tr>
                        <th class="px-4 py-3">Referencia</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3 text-right">
                            Unidades por empaque
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($productos as $producto)
                        <tr
                            wire:key="empaque-{{ $producto->id }}"
                            class="text-zinc-700 hover:bg-zinc-50 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        >
                            <td class="whitespace-nowrap px-4 py-3 font-medium">
                                {{ $producto->referencia }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $producto->descripcion }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format($producto->unidades_por_empaque, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="3"
                                class="px-4 py-10 text-center text-zinc-500"
                            >
                                No se encontraron referencias.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
            {{ $productos->links() }}
        </div>
    </div>
</div>