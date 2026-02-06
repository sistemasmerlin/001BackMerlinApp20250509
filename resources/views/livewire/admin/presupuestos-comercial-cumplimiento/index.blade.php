<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
                Cumplimiento Presupuesto Comercial
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Presupuesto vs venta del periodo, con detalle por marca y asesor.
            </p>
        </div>

        {{-- Periodo --}}
        <div class="w-full sm:w-[320px]">
            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Periodo</label>
            <select
                wire:model.live="periodo"
                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none
                       focus:ring-2 focus:ring-zinc-300 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
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
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Periodo seleccionado</div>
                    <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ $periodoLabel ?? $periodo }}
                        <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">({{ $periodo }})</span>
                    </div>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:w-auto sm:grid-cols-3">
                    {{-- Presupuesto --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Presupuesto</div>
                        <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($totalPresupuesto, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Venta --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Venta</div>
                        <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($totalVenta, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Cumplimiento --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Cumplimiento</div>
                        <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($cumplimientoTotal, 2, ',', '.') }}%
                        </div>

                        @php
                            $bar = max(0, min(100, (float) $cumplimientoTotal));
                        @endphp
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-zinc-900 dark:bg-white" style="width: {{ $bar }}%"></div>
                        </div>

                        <div class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
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
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ count($ventaPorMarca) }} marcas</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-zinc-700 dark:text-zinc-300">
                    <thead class="text-xs uppercase bg-zinc-50 text-zinc-600 dark:bg-zinc-900/40 dark:text-zinc-300">
                        <tr>
                            <th class="px-5 py-3">Marca</th>
                            <th class="px-5 py-3 text-right">Venta</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($ventaPorMarca as $r)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30">
                                <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">
                                    {{ $r['marca'] }}
                                </td>
                                <td class="px-5 py-3 text-right font-semibold {{ $r['venta'] < 0 ? 'text-rose-600' : '' }}">
                                    {{ number_format($r['venta'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
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
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Click para ver detalle por marca</div>
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
                                    <div class="truncate font-medium text-zinc-900 dark:text-white">
                                        {{ $a['nombre'] }}
                                        <span class="text-zinc-500 dark:text-zinc-400">({{ $a['vendedor'] }})</span>
                                    </div>
                                    <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $isOpen ? 'Ocultar marcas' : 'Ver marcas' }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="text-sm font-semibold {{ $a['venta'] < 0 ? 'text-rose-600' : 'text-zinc-900 dark:text-white' }}">
                                        {{ number_format($a['venta'], 0, ',', '.') }}
                                    </div>

                                    <div class="text-zinc-400">
                                        {{-- chevron --}}
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
                                    <table class="min-w-full text-sm text-left text-zinc-700 dark:text-zinc-300">
                                        <thead class="text-xs uppercase bg-white/60 text-zinc-600 dark:bg-zinc-950/40 dark:text-zinc-300">
                                            <tr>
                                                <th class="px-4 py-2">Marca</th>
                                                <th class="px-4 py-2 text-right">Venta</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200/60 dark:divide-zinc-800">
                                            @foreach($a['marcas'] as $m)
                                                <tr class="hover:bg-white/60 dark:hover:bg-zinc-950/40">
                                                    <td class="px-4 py-2 font-medium text-zinc-900 dark:text-white">
                                                        {{ $m['marca'] }}
                                                    </td>
                                                    <td class="px-4 py-2 text-right font-semibold {{ $m['venta'] < 0 ? 'text-rose-600' : '' }}">
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
                    <div class="px-6 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        Sin datos
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Detalle crudo opcional --}}
    <details class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-zinc-900 dark:text-white">
            Ver detalle crudo (debug)
        </summary>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-zinc-700 dark:text-zinc-300">
                <thead class="text-xs uppercase bg-zinc-50 text-zinc-600 dark:bg-zinc-900/40 dark:text-zinc-300">
                    <tr>
                        <th class="px-5 py-3">Periodo</th>
                        <th class="px-5 py-3">Vendedor</th>
                        <th class="px-5 py-3">Marca</th>
                        <th class="px-5 py-3 text-right">Venta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($rows as $r)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30">
                            <td class="px-5 py-3">{{ $r['periodo'] }}</td>
                            <td class="px-5 py-3">{{ $r['vendedor'] }}</td>
                            <td class="px-5 py-3">{{ $r['marca'] }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $r['venta'] < 0 ? 'text-rose-600' : '' }}">
                                {{ number_format($r['venta'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                Sin datos
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </details>

</div>
