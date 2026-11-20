<div class="mx-auto w-full max-w-4xl space-y-5 p-4 sm:p-6">
    <h1 class="text-xl font-semibold">Asignación de asesores</h1>

    <form method="GET"
          action="{{ route('rutero.asignaciones') }}"
          class="space-y-2">
        <label for="rutero-lider" class="block text-sm font-medium">
            Líder de zona
        </label>

        <div class="flex flex-col gap-2 sm:flex-row">
            <select id="rutero-lider"
                    name="lider"
                    wire:model.live="liderId"
                    required
                    @disabled($lideres->isEmpty())
                    class="w-full min-w-0 rounded-md border px-3 py-2 sm:flex-1">
                <option value="">Seleccione un líder</option>

                @foreach ($lideres as $opcion)
                    <option value="{{ $opcion->id }}"
                            @selected($liderId === (string) $opcion->id)>
                        {{ $opcion->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit"
                    @disabled($lideres->isEmpty())
                    class="rounded-md border px-4 py-2 font-medium disabled:opacity-50">
                Consultar
            </button>
        </div>

        @error('liderId')
            <p class="text-sm text-red-700">{{ $message }}</p>
        @enderror

        @if ($lideres->isEmpty())
            <p class="text-sm">No hay usuarios con el rol Coordinador.</p>
        @endif
    </form>

    <p wire:loading wire:target="liderId" role="status" class="text-sm">
        Cargando asesores…
    </p>

    <div wire:loading.remove wire:target="liderId">
        @if ($lider)
            <section wire:key="rutero-lider-{{ $lider->id }}" class="space-y-4">
                <form wire:submit="asignar" class="space-y-2">
                    <label for="rutero-asesor" class="block text-sm font-medium">
                        Agregar asesor
                    </label>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <select id="rutero-asesor"
                                wire:model="asesorId"
                                required
                                @disabled($disponibles->isEmpty())
                                class="w-full min-w-0 rounded-md border px-3 py-2 sm:flex-1">
                            <option value="">Seleccione un asesor</option>

                            @foreach ($disponibles as $asesor)
                                <option value="{{ $asesor->id }}">
                                    {{ $asesor->codigo_asesor }}
                                    {{ $asesor->nombre_asesor ?: $asesor->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit"
                                wire:loading.attr="disabled"
                                @disabled($disponibles->isEmpty())
                                class="rounded-md bg-red-700 px-4 py-2 font-medium text-white disabled:opacity-50">
                            Asignar
                        </button>
                    </div>

                    @error('asesorId')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror

                    @if ($disponibles->isEmpty())
                        <p class="text-sm">No hay asesores disponibles para este líder.</p>
                    @endif
                </form>

                @if ($mensaje)
                    <p role="status" class="text-sm font-medium">{{ $mensaje }}</p>
                @endif

                <h2 class="font-semibold">
                    Asesores asignados ({{ $asignados->count() }})
                </h2>

                <ul class="divide-y">
                    @forelse ($asignados as $asesor)
                        <li wire:key="rutero-asesor-{{ $asesor->id }}"
                            class="flex items-center justify-between gap-3 py-3">
                            <span>
                                {{ $asesor->codigo_asesor }}
                                {{ $asesor->nombre_asesor ?: $asesor->name }}
                            </span>

                            <button type="button"
                                    wire:click="retirar({{ $asesor->id }})"
                                    wire:confirm="¿Retirar este asesor del equipo?"
                                    wire:loading.attr="disabled"
                                    class="rounded-md border px-3 py-1 text-red-700">
                                Retirar
                            </button>
                        </li>
                    @empty
                        <li class="py-3 text-sm">
                            Este líder todavía no tiene asesores asignados.
                        </li>
                    @endforelse
                </ul>
            </section>
        @elseif ($lideres->isNotEmpty())
            <p class="text-sm">
                Seleccione un líder para consultar sus asesores asignados.
            </p>
        @endif
    </div>
</div>