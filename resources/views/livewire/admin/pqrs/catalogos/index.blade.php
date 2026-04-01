<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Catálogo PQRS</h1>
            <p class="text-sm text-gray-500 dark:text-zinc-300">Motivos → Submotivos → Causales</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- ================= Motivos ================= --}}
        <div class="rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-800">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-800 dark:text-white">Motivos</h2>
                <button type="button"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded"
                        wire:click="nuevoMotivo">
                    + Nuevo
                </button>
            </div>

            <input type="text"
                   class="w-full border rounded px-3 py-2 text-sm mb-3"
                   placeholder="Buscar motivo..."
                   wire:model.live="qMotivos">

            <div class="space-y-2">
                @forelse($motivos as $m)
                    <div
                        class="w-full px-3 py-2 rounded border cursor-pointer
                               {{ $motivoId === $m->id ? 'bg-blue-50 border-blue-300' : 'bg-white border-gray-200' }}"
                        wire:click="seleccionarMotivo({{ $m->id }})"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-medium text-gray-800">{{ $m->nombre }}</div>
                                <div class="text-xs text-gray-500">
                                    Orden: {{ $m->orden }} · {{ $m->activo ? 'Activo' : 'Inactivo' }}
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button type="button"
                                        class="text-xs bg-gray-200 px-2 py-1 rounded"
                                        wire:click.stop="editarMotivo({{ $m->id }})">
                                    Editar
                                </button>
                                <button type="button"
                                        class="text-xs bg-red-500 text-white px-2 py-1 rounded"
                                        wire:click.stop="confirmarDelete('motivo', {{ $m->id }})">
                                    X
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Sin motivos</div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $motivos->links() }}
            </div>
        </div>

        {{-- ================= Submotivos ================= --}}
        <div class="rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-800">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-800 dark:text-white">Submotivos</h2>
                <button type="button"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded"
                        wire:click="nuevoSubmotivo">
                    + Nuevo
                </button>
            </div>

            <div class="text-xs text-gray-500 mb-2">
                Motivo seleccionado: <span class="font-semibold">{{ $motivoId ? $motivoId : '—' }}</span>
            </div>

            <input type="text"
                   class="w-full border rounded px-3 py-2 text-sm mb-3"
                   placeholder="Buscar submotivo..."
                   wire:model.live="qSubmotivos">

            <div class="space-y-2">
                @forelse($submotivos as $s)
                    <div
                        class="w-full px-3 py-2 rounded border cursor-pointer
                               {{ $submotivoId === $s->id ? 'bg-blue-50 border-blue-300' : 'bg-white border-gray-200' }}"
                        wire:click="seleccionarSubmotivo({{ $s->id }})"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-medium text-gray-800">{{ $s->nombre }}</div>
                                <div class="text-xs text-gray-500">
                                    Orden: {{ $s->orden }} · {{ $s->activo ? 'Activo' : 'Inactivo' }}
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button type="button"
                                        class="text-xs bg-gray-200 px-2 py-1 rounded"
                                        wire:click.stop="editarSubmotivo({{ $s->id }})">
                                    Editar
                                </button>
                                <button type="button"
                                        class="text-xs bg-red-500 text-white px-2 py-1 rounded"
                                        wire:click.stop="confirmarDelete('submotivo', {{ $s->id }})">
                                    X
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">
                        {{ $motivoId ? 'Sin submotivos' : 'Selecciona un motivo' }}
                    </div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $submotivos->links() }}
            </div>
        </div>

        {{-- ================= Causales ================= --}}
        <div class="rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-800">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-800 dark:text-white">Causales</h2>
                <button type="button"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded"
                        wire:click="nuevaCausal">
                    + Nuevo
                </button>
            </div>

            <div class="text-xs text-gray-500 mb-2">
                Submotivo seleccionado: <span class="font-semibold">{{ $submotivoId ? $submotivoId : '—' }}</span>
            </div>

            <input type="text"
                   class="w-full border rounded px-3 py-2 text-sm mb-3"
                   placeholder="Buscar causal..."
                   wire:model.live="qCausales">

            <div class="space-y-2">
                @forelse($causales as $c)
                    <div class="w-full px-3 py-2 rounded border bg-white border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-medium text-gray-800">{{ $c->nombre }}</div>
                                <div class="text-xs text-gray-500">
                                    Orden: {{ $c->orden }} · {{ $c->activo ? 'Activo' : 'Inactivo' }}
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button type="button"
                                        class="text-xs bg-gray-200 px-2 py-1 rounded"
                                        wire:click="editarCausal({{ $c->id }})">
                                    Editar
                                </button>
                                <button type="button"
                                        class="text-xs bg-red-500 text-white px-2 py-1 rounded"
                                        wire:click="confirmarDelete('causal', {{ $c->id }})">
                                    X
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">
                        {{ $submotivoId ? 'Sin causales' : 'Selecciona un submotivo' }}
                    </div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $causales->links() }}
            </div>
        </div>

    </div>

    {{-- =================== MODALES (Tailwind) =================== --}}

    {{-- ✅ clases reusables --}}
    @php
        $in = "w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500";
        $sel = "w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500";
        $btn = "px-4 py-2 rounded-lg text-sm font-semibold";
    @endphp

    {{-- Modal Motivo --}}
    @if($showMotivoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="cerrarModales"></div>

            <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-xl border overflow-hidden">
                <div class="p-6 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold">
                        {{ $editMotivoId ? 'Editar' : 'Nuevo' }} Motivo
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-800 text-xl"
                            wire:click="cerrarModales">✕</button>
                </div>

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div>
                        <label class="text-sm font-semibold">Nombre</label>
                        <input class="{{ $in }}" wire:model.defer="motivo_nombre">
                        @error('motivo_nombre') <small class="text-red-600">{{ $message }}</small> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-semibold">Orden</label>
                            <input type="number" class="{{ $in }}" wire:model.defer="motivo_orden">
                            @error('motivo_orden') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Activo</label>
                            <select class="{{ $sel }}" wire:model.defer="motivo_activo">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                            @error('motivo_activo') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t flex justify-end gap-2">
                    <button type="button" class="{{ $btn }} border" wire:click="cerrarModales">Cancelar</button>
                    <button type="button" class="{{ $btn }} bg-blue-600 text-white hover:bg-blue-700"
                            wire:click="guardarMotivo">Guardar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- =================== RELACIÓN COMPLETA =================== --}}
<div class="rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-800">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="font-semibold text-gray-800 dark:text-white">Relación completa</h2>
            <p class="text-xs text-gray-500 dark:text-zinc-300">
                Motivo → Submotivo → Causal (Responsable / Requiere adjunto / Días límite)
            </p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-900 text-white">
                <tr>
                    <th class="px-3 py-2 text-left">Motivo</th>
                    <th class="px-3 py-2 text-left">Submotivo</th>
                    <th class="px-3 py-2 text-left">Causal</th>
                    <th class="px-3 py-2 text-left">Responsable</th>
                    <th class="px-3 py-2 text-center">Adjunto</th>
                    <th class="px-3 py-2 text-center">Días</th>
                    <th class="px-3 py-2 text-center">Visible para asesor</th>
                    <th class="px-3 py-2 text-center">Activo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($relaciones as $r)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2">
                            {{ $r->submotivo?->motivo?->nombre ?? '—' }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $r->submotivo?->nombre ?? '—' }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $r->nombre }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $r->responsable?->nombre ?? '—' }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            {{ $r->requiere_adjunto ? 'Sí' : 'No' }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            {{ (int)($r->sla_dias ?? 0) }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            {{ (int)($r->visible_asesor ?? 0) }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            {{ $r->activo ? 'Sí' : 'No' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">
                            No hay registros para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $relaciones->links() }}
    </div>
</div>


    {{-- Modal Submotivo --}}
    @if($showSubmotivoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="cerrarModales"></div>

            <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-xl border overflow-hidden">
                <div class="p-6 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold">
                        {{ $editSubmotivoId ? 'Editar' : 'Nuevo' }} Submotivo
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-800 text-xl"
                            wire:click="cerrarModales">✕</button>
                </div>

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div class="text-sm text-gray-500">
                        Motivo ID: <strong>{{ $motivoId ?? '—' }}</strong>
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Nombre</label>
                        <input class="{{ $in }}" wire:model.defer="submotivo_nombre">
                        @error('submotivo_nombre') <small class="text-red-600">{{ $message }}</small> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-semibold">Orden</label>
                            <input type="number" class="{{ $in }}" wire:model.defer="submotivo_orden">
                            @error('submotivo_orden') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Activo</label>
                            <select class="{{ $sel }}" wire:model.defer="submotivo_activo">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                            @error('submotivo_activo') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t flex justify-end gap-2">
                    <button type="button" class="{{ $btn }} border" wire:click="cerrarModales">Cancelar</button>
                    <button type="button" class="{{ $btn }} bg-blue-600 text-white hover:bg-blue-700"
                            wire:click="guardarSubmotivo">Guardar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Causal --}}
    @if($showCausalModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="cerrarModales"></div>

            <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-xl border overflow-hidden">
                <div class="p-6 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold">
                        {{ $editCausalId ? 'Editar' : 'Nueva' }} Causal
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-800 text-xl"
                            wire:click="cerrarModales">✕</button>
                </div>

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div class="text-sm text-gray-500">
                        Submotivo ID: <strong>{{ $submotivoId ?? '—' }}</strong>
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Responsable</label>
                        <select class="{{ $sel }}" wire:model.defer="causal_responsable_id">
                            <option value="">— Selecciona —</option>
                            @foreach($responsables as $r)
                                <option value="{{ $r->id }}">{{ $r->nombre }}</option>
                            @endforeach
                        </select>
                        @error('causal_responsable_id') <small class="text-red-600">{{ $message }}</small> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold">Requiere adjunto</label>
                            <select class="{{ $sel }}" wire:model.defer="causal_requiere_adjunto">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                            @error('causal_requiere_adjunto') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Permite ORM</label>
                            <select class="{{ $sel }}" wire:model.defer="causal_permite_recogida">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                            @error('causal_permite_recogida') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold">Días límite desde factura</label>
                            <input type="number" min="0" class="{{ $in }}" wire:model.defer="causal_dias_limite_factura"
                                placeholder="0 = sin límite">
                            @error('causal_dias_limite_factura') <small class="text-red-600">{{ $message }}</small> @enderror
                            <p class="text-xs text-gray-500 mt-1">
                                Si pasan estos días desde la fecha de la factura, ya no se permite radicar la PQRS.
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Visible para asesor</label>
                            <select class="{{ $sel }}" wire:model.defer="causal_visible_asesor">
                                <option value="1">Sí (la ve el asesor)</option>
                                <option value="0">No (solo interno)</option>
                            </select>
                            @error('causal_visible_asesor') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Nombre</label>
                        <input class="{{ $in }}" wire:model.defer="causal_nombre">
                        @error('causal_nombre') <small class="text-red-600">{{ $message }}</small> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-semibold">Orden</label>
                            <input type="number" class="{{ $in }}" wire:model.defer="causal_orden">
                            @error('causal_orden') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Activo</label>
                            <select class="{{ $sel }}" wire:model.defer="causal_activo">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                            @error('causal_activo') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t flex justify-end gap-2">
                    <button type="button" class="{{ $btn }} border" wire:click="cerrarModales">Cancelar</button>
                    <button type="button" class="{{ $btn }} bg-blue-600 text-white hover:bg-blue-700"
                            wire:click="guardarCausal">Guardar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Delete --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="cerrarModales"></div>

            <div class="relative w-full max-w-lg bg-white rounded-xl shadow-xl border overflow-hidden">
                <div class="p-6 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Eliminar</h3>
                    <button type="button" class="text-gray-500 hover:text-gray-800 text-xl"
                            wire:click="cerrarModales">✕</button>
                </div>

                <div class="p-6 space-y-3">
                    <p class="text-sm text-gray-700">¿Seguro que deseas eliminar este registro?</p>
                    <div class="text-xs text-gray-500">
                        Tipo: <strong>{{ $deleteType }}</strong> · ID: <strong>{{ $deleteId }}</strong>
                    </div>
                </div>

                <div class="p-6 border-t flex justify-end gap-2">
                    <button type="button" class="{{ $btn }} border" wire:click="cerrarModales">Cancelar</button>
                    <button type="button" class="{{ $btn }} bg-red-600 text-white hover:bg-red-700"
                            wire:click="eliminar">Eliminar</button>
                </div>
            </div>
        </div>
    @endif

</div>
