
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


        {{-- BLOQUE SUPERIOR: GENERAR COMISIONES --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Generar comisiones venta</h2>
                <p class="text-sm text-slate-500">
                    Selecciona un asesor y el periodo a procesar.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Asesor
                    </label>
                    <select
                        wire:model.live="asesorSeleccionado"
                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                    >
                        <option value="">Todos los asesores</option>
                        @foreach($asesores as $asesor)
                            <option value="{{ $asesor['codigo_asesor'] }}">
                                {{ $asesor['name'] }} - {{ $asesor['codigo_asesor'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Periodo a generar
                    </label>
                    <input
                        type="text"
                        wire:model="periodoGenerar"
                        placeholder="YYYYMM"
                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                    >
                </div>

                <div class="flex items-end">
                    <button
                        wire:click="generarComisionesVenta"
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-white font-semibold hover:bg-red-700 transition w-full md:w-auto"
                    >
                        Generar comisiones venta
                    </button>
                </div>
            </div>
        </div>

        {{-- BLOQUE INFERIOR: CONSULTAR TABLA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="w-full md:w-56">
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Periodo consulta
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="periodoConsulta"
                        placeholder="YYYYMM"
                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                    >
                </div>

                <div class="text-sm text-slate-500">
                    Mostrando información almacenada en la tabla de comisiones.
                </div>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">Comisiones por asesor</h2>
                <p class="text-sm text-slate-500">
                    Periodo consultado: {{ $periodoConsulta }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Asesor</th>
                            <th class="px-4 py-3 text-left font-semibold">Código</th>

                            <th class="px-4 py-3 text-right font-semibold">% Llantas</th>
                            <th class="px-4 py-3 text-right font-semibold">Comisión Llantas</th>

                            <th class="px-4 py-3 text-right font-semibold">% Pirelli</th>
                            <th class="px-4 py-3 text-right font-semibold">Comisión Pirelli</th>

                            <th class="px-4 py-3 text-right font-semibold">% Repuestos</th>
                            <th class="px-4 py-3 text-right font-semibold">Comisión Repuestos</th>

                            <th class="px-4 py-3 text-right font-semibold">% Total</th>
                            <th class="px-4 py-3 text-right font-semibold">Comisión Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($comisiones as $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-800 font-medium">
                                    {{ $fila['nombre_asesor'] }}
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    {{ $fila['cod_asesor'] }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['llantas_cumplimiento'] ?? 0, 0, ',', '.') }}%
                                </td>
                                <td class="px-4 py-3 text-right">
                                    $ {{ number_format($fila['llantas_comision'] ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['pirelli_cumplimiento'] ?? 0, 0, ',', '.') }}%
                                </td>
                                <td class="px-4 py-3 text-right">
                                    $ {{ number_format($fila['pirelli_comision'] ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['repuestos_cumplimiento'] ?? 0, 0, ',', '.') }}%
                                </td>
                                <td class="px-4 py-3 text-right">
                                    $ {{ number_format($fila['repuestos_comision'] ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ number_format($fila['total_cumplimiento'] ?? 0, 0, ',', '.') }}%
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                    $ {{ number_format($fila['total_comision'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-slate-500">
                                    No hay resultados para mostrar para el periodo {{ $periodoConsulta }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if(count($comisiones))
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="9" class="px-4 py-4 text-right font-semibold text-slate-700">
                                    Total general comisiones
                                </td>
                                <td class="px-4 py-4 text-right font-bold text-emerald-700">
                                    $ {{ number_format(collect($comisiones)->sum('total_comision'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>