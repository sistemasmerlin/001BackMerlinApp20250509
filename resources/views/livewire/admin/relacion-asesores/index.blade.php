

<div class="overflow-x-auto rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-700 ">
    <div wire:ignore>
        <table class="min-w-full table-auto text-sm text-left text-zinc-800 dark:text-zinc-100">
            <thead class="bg-neutral-900 dark:bg-zinc-800 ">
                <tr >
                    <th class="px-6 py-3 font-semibold text-center text-zinc-50  border-gray-900">COORDINADOR - TELEVENTAS</th>
                    <th class="px-6 py-3 font-semibold text-center text-zinc-50  border-gray-900">ASESORES ASIGNADOS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($usuarios as $usuario)
                    @if (in_array($usuario->getRoleNames()->first(), ['Televentas', 'Coordinador Comercial']))
                        <tr>
                            <td class="px-6 py-4 border border-gray-900 bg-sky-200">
                                <div class="text-base font-medium"><strong>{{ $usuario->name }}</strong></div>
                                <div class="text-base text-zinc-900"><strong>{{ $usuario->email }}</strong></div>
                            </td>
                            <td class="px-6 py-4 border border-gray-900">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach ($asesoresDisponibles as $asesor)
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox"
                                                wire:click="toggleRelacion({{ $usuario->id }}, {{ $asesor->id }})"
                                                @if(in_array($asesor->id, $relaciones[$usuario->id] ?? [])) checked @endif
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-300">
                                            <span>{{ $asesor->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>


