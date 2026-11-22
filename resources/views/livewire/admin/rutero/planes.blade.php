<div class="space-y-6 p-4 sm:p-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h1 class="text-xl font-semibold">Planes de ruta</h1>

        <div>
            <label for="mes-rutero" class="mb-1 block text-sm font-medium">
                Mes del plan
            </label>

            <input id="mes-rutero" type="month" wire:model.live="mes"
                   class="rounded-lg border px-3 py-2 dark:bg-zinc-900">
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3">Asesor</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Días programados</th>
                    <th class="px-4 py-3">Visitas programadas</th>
                    <th class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($planes as $fila)
                    <tr wire:key="plan-rutero-{{ $fila->id }}">
                        <td class="px-4 py-3">
                            <span class="font-medium">{{ $fila->asesor }}</span>
                            <span class="block text-xs">{{ $fila->codigo_asesor }}</span>
                        </td>
                        <td class="px-4 py-3">
                            {{ ucfirst(str_replace('_', ' ', $fila->estado)) }}
                        </td>
                        <td class="px-4 py-3">{{ $fila->dias_programados }}</td>
                        <td class="px-4 py-3">{{ $fila->visitas_programadas }}</td>
                        <td class="px-4 py-3">
                            <button type="button"
                                    wire:click="abrirPlan({{ $fila->id }})"
                                    wire:loading.attr="disabled"
                                    class="rounded-lg border px-3 py-1.5 font-medium">
                                Ver ruta
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center">
                            No hay planes disponibles para este mes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $planes->links() }}

    @if ($plan)
        <section class="space-y-4 rounded-lg border p-4" wire:key="detalle-ruta-{{ $plan->id }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold">{{ $plan->asesor }}</h2>
                    <p class="text-sm">Plan de {{ $mes }}</p>
                </div>

                <button type="button" wire:click="cerrarPlan"
                        class="rounded-lg border px-3 py-1.5 text-sm">
                    Cerrar detalle
                </button>
            </div>

            @forelse ($dias as $fecha => $ciudades)
                <details class="rounded-lg border p-3" wire:key="dia-ruta-{{ $fecha }}">
                    <summary class="cursor-pointer font-medium">
                        {{ \Carbon\CarbonImmutable::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM') }}
                        · {{ $ciudades->count() }} {{ $ciudades->count() === 1 ? 'ciudad' : 'ciudades' }}
                    </summary>

                    @foreach ($ciudades as $ciudadId => $filas)
                        @php
                            $ciudad = $filas->first();
                            $paradas = $filas->whereNotNull('parada_id');
                        @endphp

                        <div class="mt-4" wire:key="ciudad-ruta-{{ $ciudadId }}">
                            <h3 class="font-semibold">
                                {{ $ciudad->ciudad }} · {{ $ciudad->departamento }}
                            </h3>

                            <ul class="mt-1 divide-y">
                                @forelse ($paradas as $parada)
                                    <li class="py-2 text-sm">
                                        <strong>{{ $parada->razon_social }}</strong>
                                        <p>
                                            NIT {{ $parada->nit }}
                                            · Sucursal {{ $parada->id_sucursal }}
                                            · Punto {{ $parada->punto_envio_id }}
                                        </p>
                                        <p>{{ $parada->direccion ?: 'Sin dirección registrada' }}</p>
                                    </li>
                                @empty
                                    <li class="py-2 text-sm">Ciudad programada sin visitas seleccionadas.</li>
                                @endforelse
                            </ul>
                        </div>
                    @endforeach
                </details>
            @empty
                <p class="text-sm">Este plan todavía no tiene ciudades programadas.</p>
            @endforelse
        </section>
    @endif
</div>