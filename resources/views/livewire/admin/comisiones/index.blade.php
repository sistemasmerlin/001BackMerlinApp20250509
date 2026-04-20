<div class="p-6 space-y-6">

    @if (session()->has('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-slate-800">Generar comisiones venta</h2>
            <p class="text-sm text-slate-500">
                Selecciona el periodo a procesar.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Periodo a generar
                </label>
                <input
                    type="text"
                    wire:model.defer="periodoGenerar"
                    placeholder="YYYYMM"
                    class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                >
            </div>

            <div class="flex items-end">
                <button
                    wire:click.prevent="generarComisionesVenta"
                    wire:loading.attr="disabled"
                    wire:target="generarComisionesVenta"
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-white font-semibold hover:bg-red-700 transition w-full md:w-auto disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="generarComisionesVenta">
                        Generar comisiones venta
                    </span>

                    <span wire:loading wire:target="generarComisionesVenta">
                        Generando...
                    </span>
                </button>
            </div>
        </div>
    </div>

    @if (!empty($resultadoGenerado))
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Resultado generado</h2>
                    <p class="text-sm text-slate-500">Periodo: {{ $periodoGenerar }}</p>
                </div>

                <button
                    wire:click="exportarExcel"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                    Exportar Excel
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Asesor</th>
                            <th class="px-4 py-3 text-left">Código</th>

                            <th class="px-4 py-3 text-right">% Llantas</th>
                            <th class="px-4 py-3 text-right">Comisión Llantas</th>

                            <th class="px-4 py-3 text-right">% Repuestos</th>
                            <th class="px-4 py-3 text-right">Comisión Repuestos</th>

                            <th class="px-4 py-3 text-right">% Pirelli</th>
                            <th class="px-4 py-3 text-right">Comisión Pirelli</th>

                            <th class="px-4 py-3 text-right">% Total</th>
                            <th class="px-4 py-3 text-right">Comisión Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($resultadoGenerado as $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ $fila['nombre_asesor'] }}
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    {{ $fila['codigo_asesor'] }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['llantas']['cumplimiento'], 0) }}%
                                </td>
                                <td class="px-4 py-3 text-right">
                                    $ {{ number_format($fila['llantas']['comision'], 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['repuestos']['cumplimiento'], 0) }}%
                                </td>
                                <td class="px-4 py-3 text-right">
                                    $ {{ number_format($fila['repuestos']['comision'], 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['pirelli']['cumplimiento'], 0) }}%
                                </td>
                                <td class="px-4 py-3 text-right">
                                    $ {{ number_format($fila['pirelli']['comision'], 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['total']['cumplimiento'], 0) }}%
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                    $ {{ number_format(($fila['repuestos']['comision']+$fila['llantas']['comision']+$fila['pirelli']['comision']), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="9" class="px-4 py-3 text-right font-semibold">
                                Total General
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                $ {{ number_format(collect($resultadoGenerado)->sum(fn($i) => $i['total']['comision']), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

</div>