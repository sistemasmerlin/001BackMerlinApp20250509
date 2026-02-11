<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
                Ventas por mes (Unidades y Valor) — Pivot por asesor y marca
            </h1>
            <p class="text-sm text-zinc-700 dark:text-zinc-200">
                Tabla horizontal: filas = asesor, columnas = marcas. Incluye totales por fila y por marca.
            </p>
        </div>

        {{-- Periodo --}}
        <div class="w-full sm:w-[320px]">
            <label class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Periodo</label>
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

    {{-- KPIs --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="p-5 sm:p-6">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/30">
                    <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Periodo</div>
                    <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ $periodoLabel ?: $periodo }}
                        <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">({{ $periodo }})</span>
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/30">
                    <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Total unidades</div>
                    <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($totalUnidades ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/30">
                    <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Total valor</div>
                    <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($totalValor ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- UNIDADES --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
            <div>
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Pivot — Unidades</div>
                <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">
                    Filas: asesor · Columnas: marca · Total por fila y por marca
                </div>
            </div>

            <button
                type="button"
                wire:click="descargarUnidades"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-900
                       hover:bg-zinc-50 disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-900/40"
            >
                <span wire:loading.remove wire:target="descargarUnidades">Descargar Excel</span>
                <span wire:loading wire:target="descargarUnidades">Generando...</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-zinc-800 dark:text-zinc-100">
                <thead class="text-xs uppercase bg-zinc-100 text-zinc-900 dark:bg-zinc-900/60 dark:text-zinc-100">
                    <tr>
                        <th class="px-4 py-3 sticky left-0 bg-zinc-100 dark:bg-zinc-900/60 z-10">Asesor</th>
                        @foreach($marcas as $marca)
                            <th class="px-4 py-3 text-right whitespace-nowrap">{{ $marca }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-right whitespace-nowrap">TOTAL</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($tablaUnidades as $row)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30">
                            <td class="px-4 py-3 sticky left-0 bg-white dark:bg-zinc-950 z-10">
                                <div class="font-semibold text-zinc-900 dark:text-white">{{ $row['nombre'] }}</div>
                                <div class="text-xs font-semibold text-zinc-700 dark:text-zinc-200">{{ $row['vendedor'] }}</div>
                            </td>

                            @foreach($marcas as $marca)
                                <td class="px-4 py-3 text-right font-semibold">
                                    {{ number_format($row['cells'][$marca] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach

                            <td class="px-4 py-3 text-right font-bold">
                                {{ number_format($row['total'] ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + count($marcas) }}" class="px-6 py-10 text-center text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                Sin datos
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if(!empty($tablaUnidades))
                    <tfoot class="bg-zinc-50 dark:bg-zinc-900/30 border-t border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-4 py-3 sticky left-0 bg-zinc-50 dark:bg-zinc-900/30 z-10 text-zinc-900 dark:text-white">
                                TOTAL
                            </th>

                            @foreach($marcas as $marca)
                                <th class="px-4 py-3 text-right text-zinc-900 dark:text-white">
                                    {{ number_format($totalesUnidadesPorMarca[$marca] ?? 0, 0, ',', '.') }}
                                </th>
                            @endforeach

                            <th class="px-4 py-3 text-right text-zinc-900 dark:text-white">
                                {{ number_format($totalUnidades ?? 0, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- VALOR --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
            <div>
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Pivot — Valor</div>
                <div class="text-xs font-medium text-zinc-800 dark:text-zinc-200">
                    Filas: asesor · Columnas: marca · Total por fila y por marca
                </div>
            </div>

            <button
                type="button"
                wire:click="descargarValor"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-900
                       hover:bg-zinc-50 disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-900/40"
            >
                <span wire:loading.remove wire:target="descargarValor">Descargar Excel</span>
                <span wire:loading wire:target="descargarValor">Generando...</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-zinc-800 dark:text-zinc-100">
                <thead class="text-xs uppercase bg-zinc-100 text-zinc-900 dark:bg-zinc-900/60 dark:text-zinc-100">
                    <tr>
                        <th class="px-4 py-3 sticky left-0 bg-zinc-100 dark:bg-zinc-900/60 z-10">Asesor</th>
                        @foreach($marcas as $marca)
                            <th class="px-4 py-3 text-right whitespace-nowrap">{{ $marca }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-right whitespace-nowrap">TOTAL</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($tablaValor as $row)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30">
                            <td class="px-4 py-3 sticky left-0 bg-white dark:bg-zinc-950 z-10">
                                <div class="font-semibold text-zinc-900 dark:text-white">{{ $row['nombre'] }}</div>
                                <div class="text-xs font-semibold text-zinc-700 dark:text-zinc-200">{{ $row['vendedor'] }}</div>
                            </td>

                            @foreach($marcas as $marca)
                                <td class="px-4 py-3 text-right font-semibold">
                                    {{ number_format($row['cells'][$marca] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach

                            <td class="px-4 py-3 text-right font-bold">
                                {{ number_format($row['total'] ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + count($marcas) }}" class="px-6 py-10 text-center text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                Sin datos
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if(!empty($tablaValor))
                    <tfoot class="bg-zinc-50 dark:bg-zinc-900/30 border-t border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="px-4 py-3 sticky left-0 bg-zinc-50 dark:bg-zinc-900/30 z-10 text-zinc-900 dark:text-white">
                                TOTAL
                            </th>

                            @foreach($marcas as $marca)
                                <th class="px-4 py-3 text-right text-zinc-900 dark:text-white">
                                    {{ number_format($totalesValorPorMarca[$marca] ?? 0, 0, ',', '.') }}
                                </th>
                            @endforeach

                            <th class="px-4 py-3 text-right text-zinc-900 dark:text-white">
                                {{ number_format($totalValor ?? 0, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    
    {{-- CUMPLIMIENTO MIX (UNIDADES + VALOR SEGÚN MARCA) --}}
<div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

    <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
        <div class="text-sm font-semibold text-zinc-900 dark:text-white">
            Cumplimiento por asesor y marca
        </div>
        <div class="text-xs text-zinc-600 dark:text-zinc-300">
            Llantas / Pirelli = Unidades · Repuestos = Valor
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-xs">

            <thead class="bg-zinc-100 dark:bg-zinc-900/60">
                <tr>
                    <th class="px-3 py-2 sticky left-0 bg-zinc-100 dark:bg-zinc-900/60 z-20">
                        Asesor
                    </th>

                    @foreach($marcas as $marca)
                        <th colspan="3" class="px-3 py-2 text-center whitespace-nowrap">
                            {{ $marca }}
                        </th>
                    @endforeach

                    <th class="px-3 py-2 text-right">TOTAL %</th>
                </tr>

                <tr class="bg-zinc-50 dark:bg-zinc-900/30">
                    <th class="px-3 py-2 sticky left-0 bg-zinc-50 dark:bg-zinc-900/30"></th>

                    @foreach($marcas as $marca)
                        <th class="px-3 py-2 text-right">Presu</th>
                        <th class="px-3 py-2 text-right">Real</th>
                        <th class="px-3 py-2 text-right">%</th>
                    @endforeach

                    <th></th>
                </tr>
            </thead>

<tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
    @foreach($tablaCumplMix as $row)
        <tr>
            <td class="px-3 py-2 sticky left-0 bg-white dark:bg-zinc-950 z-10">
                <div class="font-semibold">{{ $row['nombre'] }}</div>
                <div class="text-[11px] text-zinc-500">{{ $row['vendedor'] }}</div>
            </td>

            @foreach($marcas as $marca)
                @php
                    $c = $row['cells'][$marca] ?? ['presu'=>0,'real'=>0,'pct'=>0,'modo'=>''];
                @endphp

                <td class="px-3 py-2 text-right">
                    {{ number_format($c['presu'], 0, ',', '.') }}
                </td>

                <td class="px-3 py-2 text-right">
                    {{ number_format($c['real'], 0, ',', '.') }}
                </td>

                <td class="px-3 py-2 text-right font-semibold
                    {{ $c['pct'] >= 100 ? 'text-emerald-600' : ($c['pct'] >= 80 ? 'text-amber-500' : 'text-rose-600') }}">
                    {{ number_format($c['pct'], 2, ',', '.') }}%
                </td>
            @endforeach

            <td class="px-3 py-2 text-right font-bold">
                {{ number_format($row['tot_pct'], 2, ',', '.') }}%
            </td>
        </tr>
    @endforeach
</tbody>

        </table>
    </div>
</div>


</div>
