<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Transportadoras</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Crea, edita y gestiona transportadoras (Soft Delete).</p>
        </div>

        <button
            wire:click="openCreate"
            class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
            + Nueva transportadora
        </button>
    </div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="md:col-span-2">
            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Buscar</label>
            <input
                wire:model.live.debounce.300ms="q"
                type="text"
                placeholder="Buscar por NIT, razón social, ciudad..."
                class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none focus:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            />
        </div>

        <div>
            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Mostrar eliminadas</label>
            <div class="mt-2 flex items-center gap-2">
                <input wire:model.live="showDeleted" type="checkbox" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-700 dark:border-zinc-600 dark:bg-zinc-900" />
                <span class="text-sm text-zinc-600 dark:text-zinc-300">Sí</span>
            </div>
        </div>

        <div class="flex items-end">
            <select wire:model.live="perPage" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                <option value="10">10 por página</option>
                <option value="25">25 por página</option>
                <option value="50">50 por página</option>
                <option value="100">100 por página</option>
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-950/40 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">NIT</th>
                        <th class="px-4 py-3">Razón social</th>
                        <th class="px-4 py-3">Ciudad</th>
                        <th class="px-4 py-3">Departamento</th>
                        <th class="px-4 py-3">Dirección</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($items as $t)
                        <tr class="{{ $t->deleted_at ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">
                                {{ $t->nit }}
                                @if($t->deleted_at)
                                    <span class="ml-2 rounded-full bg-rose-100 px-2 py-0.5 text-xs text-rose-700 dark:bg-rose-900/30 dark:text-rose-200">Eliminada</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $t->razon_social }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $t->ciudad ?? '-' }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $t->departamento ?? '-' }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $t->direccion ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="openEdit({{ $t->id }})"
                                        class="rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:hover:bg-zinc-800">
                                        Editar
                                    </button>

                                    @if(!$t->deleted_at)
                                        <button wire:click="confirmDelete({{ $t->id }})"
                                            class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-200 dark:hover:bg-rose-900/30">
                                            Eliminar
                                        </button>
                                    @else
                                        <button wire:click="restore({{ $t->id }})"
                                            class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200 dark:hover:bg-emerald-900/30">
                                            Restaurar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                No hay transportadoras para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
            {{ $items->links() }}
        </div>
    </div>

    {{-- Modal Create/Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" wire:click="closeModal"></div>

            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white">
                            {{ $editingId ? 'Editar transportadora' : 'Nueva transportadora' }}
                        </h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Completa la información básica.</p>
                    </div>
                    <button wire:click="closeModal" class="rounded-lg px-2 py-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800">✕</button>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">NIT</label>
                        <input wire:model.defer="form.nit" type="text"
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @error('form.nit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-1">
                        <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Razón social</label>
                        <input wire:model.defer="form.razon_social" type="text"
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @error('form.razon_social') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Dirección</label>
                        <input wire:model.defer="form.direccion" type="text"
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @error('form.direccion') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Departamento</label>
                        <input wire:model.defer="form.departamento" type="text"
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @error('form.departamento') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Ciudad</label>
                        <input wire:model.defer="form.ciudad" type="text"
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @error('form.ciudad') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <button wire:click="closeModal"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:hover:bg-zinc-800">
                        Cancelar
                    </button>

                    <button wire:click="save"
                        class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirm Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" wire:click="closeDeleteModal"></div>

            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Eliminar transportadora</h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    ¿Seguro que deseas eliminarla? (Se puede restaurar luego).
                </p>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <button wire:click="closeDeleteModal"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:hover:bg-zinc-800">
                        Cancelar
                    </button>

                    <button wire:click="delete"
                        class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
