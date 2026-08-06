<div class="space-y-6">

    @if (session()->has('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Encabezado --}}
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
            Movimientos bancarios
        </h1>

        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Importación y consulta de movimientos del extracto bancario.
        </p>
    </div>

    {{-- Importación --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end">
            <div class="flex-1">
                <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                    Archivo de movimientos bancarios
                </label>

                <input type="file" wire:model="archivo" accept=".xlsx,.xls"
                    class="block w-full rounded-lg border border-zinc-300 bg-white p-2 text-sm text-zinc-700 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">

                @error('archivo')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

                <p class="mt-2 text-xs text-zinc-500">
                    Formatos permitidos: XLSX o XLS. La hoja no necesita encabezados.
                </p>
            </div>

            <button type="button" wire:click="importar" wire:loading.attr="disabled" wire:target="archivo,importar"
                class="inline-flex min-w-44 items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50">
                <span wire:loading.remove wire:target="importar">
                    Importar archivo
                </span>

                <span wire:loading wire:target="importar">
                    Importando...
                </span>
            </button>
        </div>

        <div wire:loading wire:target="archivo" class="mt-3 text-sm text-blue-600">
            Cargando archivo...
        </div>
    </div>

    {{-- Resultado importación --}}
    @if (!empty($resultadoImportacion))
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-sm font-medium text-green-700">
                    Importados
                </p>

                <p class="mt-1 text-2xl font-bold text-green-800">
                    {{ number_format($resultadoImportacion['importados'], 0, ',', '.') }}
                </p>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-medium text-amber-700">
                    Duplicados
                </p>

                <p class="mt-1 text-2xl font-bold text-amber-800">
                    {{ number_format($resultadoImportacion['duplicados'], 0, ',', '.') }}
                </p>
            </div>

            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="text-sm font-medium text-red-700">
                    Omitidos con error
                </p>

                <p class="mt-1 text-2xl font-bold text-red-800">
                    {{ number_format($resultadoImportacion['omitidos'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    @endif

    @if (!empty($erroresImportacion))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <h2 class="font-semibold text-red-800">
                Filas no importadas
            </h2>

            <div class="mt-3 max-h-64 overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-red-200">
                            <th class="px-2 py-2 text-left text-red-800">
                                Fila
                            </th>

                            <th class="px-2 py-2 text-left text-red-800">
                                Error
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($erroresImportacion as $error)
                            <tr class="border-b border-red-100">
                                <td class="px-2 py-2">
                                    {{ $error['fila'] }}
                                </td>

                                <td class="px-2 py-2">
                                    {{ $error['mensaje'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Indicadores --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">
                Movimientos
            </p>

            <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                {{ number_format($resumen['cantidad'], 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-green-200 bg-green-50 p-5">
            <p class="text-sm text-green-700">
                Créditos
            </p>

            <p class="mt-2 text-2xl font-semibold text-green-800">
                ${{ number_format($resumen['creditos'], 2, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-red-200 bg-red-50 p-5">
            <p class="text-sm text-red-700">
                Débitos
            </p>

            <p class="mt-2 text-2xl font-semibold text-red-800">
                ${{ number_format(abs($resumen['debitos']), 2, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-sm text-blue-700">
                Saldo neto
            </p>

            <p class="mt-2 text-2xl font-semibold text-blue-800">
                ${{ number_format($resumen['saldo'], 2, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Buscar
                </label>

                <input type="text" wire:model.live.debounce.500ms="buscar"
                    placeholder="Descripción, referencia o archivo"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Tipo
                </label>

                <select wire:model.live="tipoMovimiento"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    <option value="">Todos</option>
                    <option value="CREDITO">Créditos</option>
                    <option value="DEBITO">Débitos</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Procesado
                </label>

                <select wire:model.live="estadoProcesado"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    <option value="">Todos</option>
                    <option value="0">Pendientes</option>
                    <option value="1">Procesados</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Desde
                </label>

                <input type="date" wire:model.live="fechaDesde"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Hasta
                </label>

                <input type="date" wire:model.live="fechaHasta"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="button" wire:click="limpiarFiltros"
                class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                Limpiar filtros
            </button>
        </div>
    </div>

    {{-- Tabla --}}
    <div
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Fecha
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Oficina
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Descripción
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Referencias
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-zinc-600">
                            Valor
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-zinc-600">
                            Estado
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($movimientos as $movimiento)
                        <tr wire:key="movimiento-{{ $movimiento->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                {{ optional($movimiento->fecha_movimiento)->format('d/m/Y') }}

                                <p class="text-xs text-zinc-400">
                                    Fila {{ $movimiento->fila_origen }}
                                </p>
                            </td>

                            <td class="px-4 py-3 text-sm">
                                {{ $movimiento->oficina_canal ?: 'Sin oficina' }}
                            </td>

                            <td class="max-w-md px-4 py-3 text-sm">
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $movimiento->descripcion_movimiento }}
                                </p>

                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ $movimiento->archivo_origen }}
                                </p>
                            </td>


                            <td class="px-4 py-3 text-xs text-zinc-600">
                                <p>
                                    <strong>Ref. 1:</strong>
                                    {{ $movimiento->referencia_1 ?: 'Sin referencia' }}
                                </p>

                                <p class="mt-1">
                                    <strong>Ref. 2:</strong>
                                    {{ $movimiento->referencia_2 ?: 'Sin referencia' }}
                                </p>

                                <p class="mt-1">
                                    <strong>Ref. 3:</strong>
                                    {{ $movimiento->referencia_3 ?: 'Sin referencia' }}
                                </p>
                            </td>

                            <td
                                class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold
                                {{ $movimiento->tipo_movimiento === 'CREDITO' ? 'text-green-700' : 'text-red-700' }}">
                                {{ $movimiento->tipo_movimiento === 'CREDITO' ? '+' : '-' }}
                                ${{ number_format(abs($movimiento->valor), 2, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($movimiento->procesado)
                                    <span
                                        class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                        Procesado
                                    </span>
                                @else
                                    <span
                                        class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
    @if ($movimiento->recibo)

        <p class="font-semibold text-green-700">
            {{ $movimiento->recibo->codigo_recibo }}
        </p>

        <p class="text-xs text-zinc-500">
            {{ $movimiento->recibo->razon_social }}
        </p>

        <p class="text-xs text-zinc-500">
            NIT: {{ $movimiento->recibo->nit_cliente }}
        </p>

        <a
            href="{{ route('admin.recibos-caja.show', $movimiento->recibo) }}"
            wire:navigate
            class="mt-2 inline-flex rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100"
        >
            Ver recibo
        </a>

    @else

        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
            Sin aplicar
        </span>

    @endif
</td>
                            
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-zinc-500">
                                No existen movimientos bancarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 px-4 py-4 dark:border-zinc-700">
            {{ $movimientos->links() }}
        </div>
    </div>
</div>
