<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Efectividad Clientes</h1>
            <p class="text-sm text-gray-500 dark:text-zinc-300">Clientes con venta / total clientes por asesor</p>
        </div>

        <div class="flex items-end gap-2">
            <div>
                <label class="text-xs text-gray-500">Periodo</label>
                <select class="border rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-800 dark:text-white dark:border-zinc-700"
                        wire:model.live="periodo">
                    @foreach($periodos as $p)
                        <option value="{{ $p['value'] }}">{{ $p['label'] }} ({{ $p['value'] }})</option>
                    @endforeach
                </select>
            </div>

            <button type="button"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg"
                    wire:click="exportar"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="exportar">📥 Exportar</span>
                <span wire:loading wire:target="exportar">Exportando...</span>
            </button>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-800">
            <div class="text-xs text-gray-500 dark:text-zinc-300">Total clientes (global)</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ number_format($totalClientes, 0, ',', '.') }}
            </div>
        </div>

        <div class="rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-800">
            <div class="text-xs text-gray-500 dark:text-zinc-300">Clientes con venta (global)</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ number_format($totalClientesConVenta, 0, ',', '.') }}
            </div>
        </div>

        <div class="rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-800">
            <div class="text-xs text-gray-500 dark:text-zinc-300">Efectividad global</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ number_format($efectividadGlobal, 2, ',', '.') }}%
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-800">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-900 text-white">
                    <tr>
                        <th class="px-3 py-2 text-left w-[120px]">Código</th>
                        <th class="px-3 py-2 text-left">Asesor</th>
                        <th class="px-3 py-2 text-right w-[140px]">Total clientes</th>
                        <th class="px-3 py-2 text-right w-[160px]">Clientes con venta</th>
                        <th class="px-3 py-2 text-right w-[140px]">Cumplimiento</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $r)
                        <tr class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700/30">
                            <td class="px-3 py-2 text-gray-800 dark:text-white">{{ $r['codigo_asesor'] }}</td>

                            <td class="px-3 py-2">
                                <div class="font-semibold text-gray-800 dark:text-white">{{ $r['nombre'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-zinc-300">{{ $r['email'] }}</div>
                            </td>

                            <td class="px-3 py-2 text-right text-gray-800 dark:text-white">
                                {{ number_format($r['total_clientes'], 0, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 text-right text-gray-800 dark:text-white">
                                {{ number_format($r['clientes_con_venta'], 0, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 text-right">
                                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-600 text-white text-xs font-semibold">
                                    {{ number_format($r['cumplimiento'], 2, ',', '.') }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-zinc-300">
                                No hay datos para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if(!empty($rows))
                    <tfoot class="bg-gray-50 dark:bg-zinc-900/30">
                        <tr>
                            <th colspan="2" class="px-3 py-2 text-right text-gray-700 dark:text-zinc-200">Totales</th>
                            <th class="px-3 py-2 text-right text-gray-800 dark:text-white">
                                {{ number_format($totalClientes, 0, ',', '.') }}
                            </th>
                            <th class="px-3 py-2 text-right text-gray-800 dark:text-white">
                                {{ number_format($totalClientesConVenta, 0, ',', '.') }}
                            </th>
                            <th class="px-3 py-2 text-right text-gray-800 dark:text-white">
                                {{ number_format($efectividadGlobal, 2, ',', '.') }}%
                            </th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
