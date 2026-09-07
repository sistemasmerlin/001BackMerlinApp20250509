<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
                Cumplimiento Presupuesto Comercial
            </h1>
            <p class="text-sm text-zinc-700 dark:text-zinc-200">
                Presupuesto vs venta del periodo, con detalle por marca, asesor y comprometidos.
            </p>
        </div>

        {{-- Periodo --}}
        <div class="w-full sm:w-[320px]">
            <label class="text-xs font-medium text-zinc-800 dark:text-zinc-200">Periodo</label>
            <select
                wire:model.live="periodo"
                class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none
                       focus:ring-2 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
            >
                @foreach($periodos as $p)
                    <option value="{{ $p['value'] }}">{{ $p['label'] }} ({{ $p['value'] }})</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- KPIs (centrados y bonitos) --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">Periodo seleccionado</div>
                    <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ $periodoLabel ?: $periodo }}
                        <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">({{ $periodo }})</span>
                    </div>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:w-auto sm:grid-cols-4">
                    {{-- Presupuesto --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Presupuesto</div>
                        <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($totalPresupuesto, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                            Unidades vendidas
                        </div>
                        <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($totalUnidades, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Venta --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Venta</div>
                        <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($totalVenta, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Cumplimiento --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Cumplimiento</div>
                        <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($cumplimientoTotal, 2, ',', '.') }}%
                        </div>

                        @php
                            $bar = max(0, min(100, (float) $cumplimientoTotal));
                        @endphp
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-300 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-zinc-900 dark:bg-white" style="width: {{ $bar }}%"></div>
                        </div>

                        <div class="mt-1 text-[11px] font-medium text-zinc-800 dark:text-zinc-200">
                            {{ $cumplimientoTotal >= 100 ? 'Meta superada' : 'Progreso a meta' }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Grid: Marca + Asesor --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Venta por marca --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <div>
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Venta por marca</div>
                    <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">{{ count($ventaPorMarca) }} marcas</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-zinc-800 dark:text-zinc-100">
                    <thead class="text-xs uppercase bg-zinc-100 text-zinc-900 dark:bg-zinc-900/60 dark:text-zinc-100">
                        <tr>
                            <th class="px-5 py-3">Marca</th>
                            <th class="px-5 py-3 text-right">Unidades</th>
                            <th class="px-5 py-3 text-right">Venta</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($ventaPorMarca as $r)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30">
                                <td class="px-5 py-3 font-semibold text-zinc-900 dark:text-white">
                                    {{ $r['marca'] }}
                                </td>
                                <td class="px-5 py-3 text-right font-semibold">
                                    {{ number_format($r['unidades'], 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-right font-semibold {{ $r['venta'] < 0 ? 'text-rose-600' : 'text-zinc-900 dark:text-white' }}">
                                    {{ number_format($r['venta'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-10 text-center text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    Sin datos
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Venta por asesor (ACORDEÓN PRO) --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <div>
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Venta por asesor</div>
                    <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">Click para ver detalle por marca</div>
                </div>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($asesores as $a)
                    @php $isOpen = ($openAsesor === $a['vendedor']); @endphp

                    <div class="p-4">
                        <button
                            type="button"
                            wire:click="toggleAsesor('{{ $a['vendedor'] }}')"
                            class="w-full text-left"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate font-semibold text-zinc-900 dark:text-white">
                                        {{ $a['nombre'] }}
                                        <span class="text-zinc-800 dark:text-zinc-200">({{ $a['vendedor'] }})</span>
                                    </div>
                                    <div class="mt-0.5 text-xs font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $isOpen ? 'Ocultar marcas' : 'Ver marcas' }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="text-sm font-semibold {{ $a['venta'] < 0 ? 'text-rose-600' : 'text-zinc-900 dark:text-white' }}">
                                        {{ number_format($a['venta'], 0, ',', '.') }}
                                    </div>

                                    <div class="text-zinc-800 dark:text-zinc-200">
                                        <svg class="h-4 w-4 transition-transform {{ $isOpen ? 'rotate-180' : '' }}" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </button>

                        {{-- Detalle marcas --}}
                        @if($isOpen)
                            <div class="mt-4 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/30">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm text-left text-zinc-800 dark:text-zinc-100">
                                        <thead class="text-xs uppercase bg-white/70 text-zinc-900 dark:bg-zinc-950/40 dark:text-zinc-100">
                                            <tr>
                                                <th class="px-4 py-2">Marca</th>
                                                <th class="px-4 py-2 text-right">Unidades</th>
                                                <th class="px-4 py-2 text-right">Venta</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800">
                                            @foreach($a['marcas'] as $m)
                                                <tr class="hover:bg-white/70 dark:hover:bg-zinc-950/40">
                                                    <td class="px-4 py-2 font-semibold text-zinc-900 dark:text-white">
                                                        {{ $m['marca'] }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right font-semibold">
                                                        {{ number_format($m['unidades'] ?? 0, 0, ',', '.') }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right font-semibold {{ $m['venta'] < 0 ? 'text-rose-600' : 'text-zinc-900 dark:text-white' }}">
                                                        {{ number_format($m['venta'], 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>

                @empty
                    <div class="px-6 py-10 text-center text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        Sin datos
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- PRODUCTOS COMPROMETIDOS --}}
<div class="overflow-hidden rounded-xl border border-red-200 bg-white shadow-sm">

    {{-- Encabezado general --}}
    <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

        {{-- Título: abre y cierra el detalle --}}
        <button
            type="button"
            wire:click="toggleComprometidos"
            class="min-w-0 flex-1 text-left"
        >
            <div class="text-base font-bold text-black">
                Productos comprometidos
            </div>

            <div class="mt-1 text-xs font-medium text-black">
                Detalle por marca y referencia
            </div>
        </button>

        {{-- Acciones --}}
        <div class="flex items-center gap-3">

            {{-- Exportar --}}
            <button
                type="button"
                wire:click="exportarComprometidos"
                wire:loading.attr="disabled"
                wire:target="exportarComprometidos"
                class="inline-flex items-center justify-center gap-2 rounded-lg
                       border border-red-600 bg-white px-3 py-2
                       text-xs font-bold text-red-600 transition
                       hover:bg-red-600 hover:text-white
                       disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg
                    wire:loading.remove
                    wire:target="exportarComprometidos"
                    class="h-4 w-4"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        d="M10.75 2.75a.75.75 0 0 0-1.5 0v7.69L6.53
                           7.72a.75.75 0 0 0-1.06 1.06l4 4a.75.75
                           0 0 0 1.06 0l4-4a.75.75 0 0 0-1.06-1.06
                           l-2.72 2.72V2.75Z"
                    />

                    <path
                        d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75
                           2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0
                           18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5
                           c0 .69-.56 1.25-1.25 1.25H4.75c-.69
                           0-1.25-.56-1.25-1.25v-2.5Z"
                    />
                </svg>

                <svg
                    wire:loading
                    wire:target="exportarComprometidos"
                    class="h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    />

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                    />
                </svg>

                <span wire:loading.remove wire:target="exportarComprometidos">
                    Exportar Excel
                </span>

                <span wire:loading wire:target="exportarComprometidos">
                    Generando...
                </span>
            </button>

            {{-- Abrir/cerrar --}}
            <button
                type="button"
                wire:click="toggleComprometidos"
                class="inline-flex items-center gap-2 rounded-lg
                       bg-red-600 px-3 py-2 text-xs font-bold
                       text-white transition hover:bg-red-700"
            >
                <span>
                    {{ $openComprometidos ? 'Ocultar' : 'Ver detalle' }}
                </span>

                <svg
                    class="h-4 w-4 transition-transform duration-200
                           {{ $openComprometidos ? 'rotate-180' : '' }}"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10
                           10.94l3.71-3.71a.75.75 0 1 1 1.06
                           1.06l-4.24 4.24a.75.75 0 0 1-1.06
                           0L5.21 8.29a.75.75 0 0 1 .02-1.08Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>

        </div>
    </div>

    @if($openComprometidos)
        {{-- Aquí continúa el contenido que ya tienes --}}
        <div class="divide-y divide-zinc-200 border-t border-zinc-200
                    dark:divide-zinc-800 dark:border-zinc-800">

            @forelse($comprometidos as $indice => $marca)
                @php
                    $marcaAbierta = $openMarcaComprometida === $indice;
                @endphp

                <div wire:key="marca-comprometida-{{ $indice }}">

                    {{-- Resumen de la marca --}}
                    <button
                        type="button"
                        wire:click="toggleMarcaComprometida({{ $indice }})"
                        class="w-full px-5 py-4 text-left hover:bg-zinc-50
                               dark:hover:bg-zinc-900/30"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                            <div class="min-w-0">
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $marca['marca'] }}
                                </div>

                                <div class="mt-1 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    {{ number_format($marca['cantidad_referencias'], 0, ',', '.') }}
                                    referencias
                                </div>
                            </div>

                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <div class="text-[11px] font-medium uppercase text-zinc-500">
                                        Unidades
                                    </div>

                                    <div class="font-semibold text-zinc-900 dark:text-white">
                                        {{ number_format($marca['unidades'], 0, ',', '.') }}
                                    </div>
                                </div>

                                <div class="text-right">
                                    <div class="text-[11px] font-medium uppercase text-zinc-500">
                                        Valor
                                    </div>

                                    <div class="font-semibold text-zinc-900 dark:text-white">
                                        ${{ number_format($marca['valor'], 0, ',', '.') }}
                                    </div>
                                </div>

                                <svg
                                    class="h-4 w-4 text-zinc-600 transition-transform
                                           dark:text-zinc-300 {{ $marcaAbierta ? 'rotate-180' : '' }}"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10
                                           10.94l3.71-3.71a.75.75 0 1 1 1.06
                                           1.06l-4.24 4.24a.75.75 0 0 1-1.06
                                           0L5.21 8.29a.75.75 0 0 1 .02-1.08z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                        </div>
                    </button>

                    {{-- Referencias de la marca --}}
                    @if($marcaAbierta)
                        <div class="border-t border-zinc-200 bg-zinc-50
                                    dark:border-zinc-800 dark:bg-zinc-900/30">

                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm text-zinc-800
                                              dark:text-zinc-100">

                                    <thead class="bg-zinc-100 text-xs uppercase text-zinc-900
                                                  dark:bg-zinc-900 dark:text-zinc-100">
                                        <tr>
                                            <th class="px-5 py-3">Referencia</th>
                                            <th class="px-5 py-3">Descripción</th>
                                            <th class="px-5 py-3 text-right">Unidades</th>
                                            <th class="px-5 py-3 text-right">Valor</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                        @forelse($marca['referencias'] as $producto)
                                            <tr class="hover:bg-white dark:hover:bg-zinc-950/40">

                                                <td class="whitespace-nowrap px-5 py-3 font-semibold
                                                           text-zinc-900 dark:text-white">
                                                    {{ $producto['referencia'] ?: 'Sin referencia' }}
                                                </td>

                                                <td class="min-w-[300px] px-5 py-3">
                                                    {{ $producto['descripcion'] ?: 'Sin descripción' }}
                                                </td>

                                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold">
                                                    {{ number_format($producto['unidades'], 0, ',', '.') }}
                                                </td>

                                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold
                                                    {{ $producto['valor'] < 0
                                                        ? 'text-rose-600'
                                                        : 'text-zinc-900 dark:text-white' }}">
                                                    ${{ number_format($producto['valor'], 0, ',', '.') }}
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td
                                                    colspan="4"
                                                    class="px-6 py-8 text-center text-zinc-600
                                                           dark:text-zinc-300"
                                                >
                                                    No hay referencias comprometidas para esta marca.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

            @empty
                <div class="px-6 py-10 text-center text-sm font-medium
                            text-zinc-700 dark:text-zinc-300">
                    No existen productos comprometidos.
                </div>
            @endforelse
        </div>
    @endif
</div>
</div>
