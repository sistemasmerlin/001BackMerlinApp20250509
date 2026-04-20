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

    @if ($tipoResultado === 'ventas' && !empty($resultadoGenerado))
        <button
            wire:click="exportarVentas"
            class="inline-flex items-center justify-center rounded-xl bg-green-600 px-4 py-2 text-white font-semibold hover:bg-green-700 transition"
        >
            Exportar ventas a Excel
        </button>
    @endif

    @if ($tipoResultado === 'cartera' && !empty($resultadoGenerado))
        <button
            wire:click="exportarCartera"
            class="inline-flex items-center justify-center rounded-xl bg-green-600 px-4 py-2 text-white font-semibold hover:bg-green-700 transition"
        >
            Exportar cartera a Excel
        </button>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-slate-800">Generar comisiones</h2>
            <p class="text-sm text-slate-500">
                Selecciona el periodo y genera el tipo de comisión que deseas consultar.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-white font-semibold hover:bg-red-700 transition w-full disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="generarComisionesVenta">
                        Generar comisiones venta
                    </span>

                    <span wire:loading wire:target="generarComisionesVenta">
                        Generando ventas...
                    </span>
                </button>
            </div>

            <div class="flex items-end">
                <button
                    wire:click.prevent="generarComisionesCartera"
                    wire:loading.attr="disabled"
                    wire:target="generarComisionesCartera"
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-white font-semibold hover:bg-blue-700 transition w-full disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="generarComisionesCartera">
                        Generar comisiones cartera
                    </span>

                    <span wire:loading wire:target="generarComisionesCartera">
                        Generando cartera...
                    </span>
                </button>
            </div>
        </div>
    </div>

    @if (!empty($resultadoGenerado) && $tipoResultado === 'ventas')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Comisiones de ventas</h2>
                    <p class="text-sm text-slate-500">Periodo: {{ $periodoGenerar }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Asesor</th>
                            <th class="px-4 py-3 text-left">Código</th>
                            <th class="px-4 py-3 text-left">Categoría</th>

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
                                    {{ $fila['nombre_asesor'] ?? '' }}
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    {{ $fila['codigo_asesor'] ?? '' }}
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    {{ ucfirst($fila['categoria_asesor'] ?? '') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['llantas']['cumplimiento'] ?? 0, 0, ',', '.') }}%
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['llantas']['comision'] ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['repuestos']['cumplimiento'] ?? 0, 0, ',', '.') }}%
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['repuestos']['comision'] ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['pirelli']['cumplimiento'] ?? 0, 0, ',', '.') }}%
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['pirelli']['comision'] ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['total']['cumplimiento'] ?? 0, 0, ',', '.') }}%
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                    {{ number_format(($fila['repuestos']['comision'] ?? 0) + ($fila['llantas']['comision'] ?? 0) + ($fila['pirelli']['comision'] ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="10" class="px-4 py-3 text-right font-semibold">
                                Total general
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                {{ number_format(collect($resultadoGenerado)->sum(fn($i) => ($i['repuestos']['comision'] ?? 0) + ($i['llantas']['comision'] ?? 0) + ($i['pirelli']['comision'] ?? 0)), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

@if (!empty($resultadoGenerado) && $tipoResultado === 'cartera')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Comisiones de cartera</h2>
                <p class="text-sm text-slate-500">Periodo: {{ $periodoGenerar }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Asesor</th>
                        <th class="px-4 py-3 text-left">Código</th>
                        <th class="px-4 py-3 text-left">Categoría</th>

                        <th class="px-4 py-3 text-right">Presupuesto</th>
                        <th class="px-4 py-3 text-right">Recaudo Presupuesto</th>
                        <th class="px-4 py-3 text-right">% Cumplimiento</th>
                        <th class="px-4 py-3 text-right">% Clientes</th>

                        <th class="px-4 py-3 text-right">% Comision</th>

                        <th class="px-4 py-3 text-right">1-15</th>
                        <th class="px-4 py-3 text-right">Com. 1-15</th>

                        <th class="px-4 py-3 text-right">16-30</th>
                        <th class="px-4 py-3 text-right">Com. 16-30</th>

                        <th class="px-4 py-3 text-right">31-45</th>
                        <th class="px-4 py-3 text-right">Com. 31-45</th>

                        <th class="px-4 py-3 text-right">46-65</th>
                        <th class="px-4 py-3 text-right">Com. 46-65</th>

                        <th class="px-4 py-3 text-right">66-80</th>
                        <th class="px-4 py-3 text-right">Com. 66-80</th>

                        <th class="px-4 py-3 text-right">>81</th>
                        <th class="px-4 py-3 text-right">Com. >81</th>

                        <th class="px-4 py-3 text-right">Total recuadado</th>

                        <th class="px-4 py-3 text-right">Comisión a pagar</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach ($resultadoGenerado as $fila)
                        @php
                            $detalle = data_get($fila, 'catera.recuadoPorDias.data_asesores.0', []);
                        @endphp

                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $fila['nombre_asesor'] ?? '' }}
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                {{ $fila['codigo_asesor'] ?? '' }}
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                {{ ucfirst($fila['categoria_asesor'] ?? '') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($fila, 'catera.totalPresupuesto', 0), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($fila, 'catera.recaudoPresupuesto', 0), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($fila, 'catera.cumplimiento', 0), 2, ',', '.') }}%
                            </td> 

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($fila, 'catera.porcentajeClientes', 0), 2, ',', '.') }}%
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($fila, 'catera.comisionRecaudoPresupuesto', 0), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'recaudo_1_15', 0), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'comision_1_a_15', 0), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'recaudo_16_30', 0), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'comision_16_a_30', 0), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'recaudo_31_45', 0), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'comision_31_a_45', 0), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'recaudo_46_65', 0), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'comision_46_a_65', 0), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'recaudo_66_80', 0), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'comision_66_a_80', 0), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'recaudo_mayor_81', 0), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($detalle, 'comision_mayor_81', 0), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ number_format(data_get($fila, 'catera.totalRecaudoSinFlete', 0), 0, ',', '.') }}
                            </td> 

                            <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                {{ number_format((data_get($detalle, 'comision_a_pagar', 0)+data_get($fila, 'catera.comisionRecaudoPresupuesto', 0)), 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <td colspan="7" class="px-4 py-3 text-right font-semibold">
                            Totales generales
                        </td>

                        <td class="px-4 py-3 text-right font-semibold">
                            {{ number_format(collect($resultadoGenerado)->sum(fn($i) => data_get($i, 'catera.recuadoPorDias.totales.total_recaudo_dias', 0)), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>

                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>

                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>

                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>

                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>

                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            —
                        </td>

                        <td class="px-4 py-3 text-right font-bold text-emerald-700">
                            {{ number_format(collect($resultadoGenerado)->sum(fn($i) => data_get($i, 'catera.recuadoPorDias.totales.total_comision_dias', 0)), 0, ',', '.') }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="19" class="px-4 py-3 text-right font-semibold text-slate-700">
                            Total recaudo días sin flete:
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800">
                            {{ number_format(collect($resultadoGenerado)->sum(fn($i) => data_get($i, 'catera.recuadoPorDias.totales.total_recaudo_dias_sin_flete', 0)), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif

</div>