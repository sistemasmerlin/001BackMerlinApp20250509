<div class="space-y-6">

    {{-- Mensajes --}}
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
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
                Recibos de caja
            </h1>

            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Consulta, revisión y aprobación de recaudos.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">

            <button type="button" wire:click="cruzarRecibosConMovimientos" wire:loading.attr="disabled"
                wire:target="cruzarRecibosConMovimientos"
                class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-500 disabled:cursor-not-allowed disabled:opacity-50">
                <span wire:loading.remove wire:target="cruzarRecibosConMovimientos">
                    Cruzar recibos con movimientos
                </span>

                <span wire:loading wire:target="cruzarRecibosConMovimientos">
                    Cruzando recibos...
                </span>
            </button>

            <button type="button" wire:click="limpiarFiltros"
                class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                Limpiar filtros
            </button>

        </div>
    </div>

    {{-- Indicadores --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Cantidad de recibos</p>

            <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                {{ number_format($resumen['cantidad'], 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Total recibido</p>

            <p class="mt-2 text-2xl font-semibold text-green-700">
                ${{ number_format($resumen['total_recibido'], 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500">Total sin aplicar</p>

            <p class="mt-2 text-2xl font-semibold text-amber-700">
                ${{ number_format($resumen['total_restante'], 0, ',', '.') }}
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
                    placeholder="Código, NIT, cliente o soporte"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Estado
                </label>

                <select wire:model.live="estado"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    <option value="">Todos</option>
                    <option value="RECIBIDO">Recibido</option>
                    <option value="EN_REVISION">En revisión</option>
                    <option value="APROBADO">Aprobado</option>
                    <option value="RECHAZADO">Rechazado</option>
                    <option value="EXPORTADO">Exportado</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    Asesor
                </label>

                <input type="text" wire:model.live.debounce.500ms="asesor" placeholder="Código o nombre"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
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
    </div>

    {{-- Tabla --}}
    <div
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Recibo
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Cliente
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Asesor
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Banco
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-zinc-600">
                            Recibido
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-zinc-600">
                            Pendiente
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-zinc-600">
                            Facturas
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-zinc-600">
                            Estado
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-zinc-600">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($recibos as $recibo)
                        <tr wire:key="recibo-{{ $recibo->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                            <td class="whitespace-nowrap px-4 py-3">
                                <p class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $recibo->codigo_recibo }}
                                </p>

                                <p class="text-xs text-zinc-500">
                                    {{ optional($recibo->fecha_recibo)->format('d/m/Y H:i') }}
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $recibo->razon_social ?: 'Sin razón social' }}
                                </p>

                                <p class="text-xs text-zinc-500">
                                    NIT: {{ $recibo->nit_cliente }}
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                <p class="text-sm text-zinc-800 dark:text-zinc-200">
                                    {{ $recibo->nombre_asesor ?: 'Sin nombre' }}
                                </p>

                                <p class="text-xs text-zinc-500">
                                    {{ $recibo->id_vendedor }}
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                <p class="text-sm text-zinc-800 dark:text-zinc-200">
                                    {{ $recibo->bancoRecaudo?->descripcion_banco ?: 'Sin banco' }}
                                </p>

                                <p class="text-xs text-zinc-500">
                                    {{ $recibo->bancoRecaudo?->numero_cuenta }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-green-700">
                                ${{ number_format($recibo->total_recibido, 0, ',', '.') }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-amber-700">
                                ${{ number_format($recibo->total_restante, 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">
                                    {{ $recibo->cxcs_count }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                @php
                                    $claseEstado = match ($recibo->estado) {
                                        'APROBADO' => 'bg-green-100 text-green-800',
                                        'RECHAZADO' => 'bg-red-100 text-red-800',
                                        'EN_REVISION' => 'bg-amber-100 text-amber-800',
                                        'EXPORTADO' => 'bg-blue-100 text-blue-800',
                                        default => 'bg-zinc-100 text-zinc-700',
                                    };
                                @endphp

                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $claseEstado }}">
                                    {{ str_replace('_', ' ', $recibo->estado) }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.recibos-caja.show', $recibo) }}" wire:navigate
                                        class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        Ver detalle
                                    </a>

                                    <button type="button" wire:click="abrirModalEstado({{ $recibo->id }})"
                                        class="rounded-lg bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-200">
                                        Estado
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-sm text-zinc-500">
                                No se encontraron recibos de caja.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 px-4 py-4 dark:border-zinc-700">
            {{ $recibos->links() }}
        </div>
    </div>

</div>
