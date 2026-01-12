<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Usuarios</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Gestiona usuarios, roles y categoría de asesor.</p>
        </div>

        <button
            type="button"
            wire:click="abrirModal"
            class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800
                   dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-zinc-400"
        >
            <span class="text-base leading-none">+</span>
            Nuevo usuario
        </button>
    </div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800
                    dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800
                    dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="p-4 sm:p-6">
            <div wire:ignore>
                <table id="tabla" class="min-w-full text-sm text-left text-zinc-700 dark:text-zinc-300">
                    <thead class="text-xs uppercase bg-zinc-50 text-zinc-600 dark:bg-zinc-900/40 dark:text-zinc-300">
                        <tr>
                            <th class="px-4 py-3">Id Asesor</th>
                            <th class="px-4 py-3">Id Recibos</th>
                            <th class="px-4 py-3">Cédula</th>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3">Categoría</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($usuarios as $usuario)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30">
                                <td class="px-4 py-3">{{ $usuario->codigo_asesor }}</td>
                                <td class="px-4 py-3">{{ $usuario->codigo_recibos }}</td>
                                <td class="px-4 py-3">{{ $usuario->cedula }}</td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                            {{ $usuario->initials() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-white">{{ $usuario->name }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">ID: {{ $usuario->id }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">{{ $usuario->email }}</td>

                                <td class="px-4 py-3">
                                    @forelse ($usuario->roles as $rol)
                                        <span class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-xs font-medium text-zinc-700
                                                    dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 mr-1">
                                            {{ $rol->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-zinc-400">Sin rol</span>
                                    @endforelse
                                </td>

                                <td class="px-4 py-3">
                                    @if($usuario->categoria_asesor)
                                        <span class="inline-flex items-center rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-xs font-medium text-zinc-700
                                                    dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200">
                                            {{ ucfirst($usuario->categoria_asesor) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="editarUsuario({{ $usuario->id }})"
                                            class="rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50
                                                   dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-900"
                                        >
                                            Editar
                                        </button>

                                        <button
                                            type="button"
                                            onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                                            wire:click="eliminarUsuario({{ $usuario->id }})"
                                            class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50
                                                   dark:border-rose-900/40 dark:text-rose-200 dark:hover:bg-rose-900/20"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>


    {{-- Modal --}}
    @if ($openModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="$set('openModal', false)"></div>

            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ $modoEditar ? 'Editar usuario' : 'Nuevo usuario' }}
                    </h2>

                    <button
                        type="button"
                        wire:click="$set('openModal', false)"
                        class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200"
                        aria-label="Cerrar"
                    >
                        ✕
                    </button>
                </div>

                {{-- Errors summary --}}
                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-200">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="{{ $modoEditar ? 'actualizarUsuario' : 'guardarUsuario' }}" class="mt-5 space-y-4">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        {{-- Nombre --}}
                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Nombre</label>
                            <input type="text" wire:model.defer="name"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Email</label>
                            <input type="email" wire:model.defer="email"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                            @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Password --}}
                        <div class="sm:col-span-2">
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                {{ $modoEditar ? 'Nueva contraseña (opcional)' : 'Contraseña' }}
                            </label>
                            <div class="relative mt-1">
                                <input
                                    type="{{ $mostrarPassword ? 'text' : 'password' }}"
                                    wire:model.defer="{{ $modoEditar ? 'nuevaPassword' : 'password' }}"
                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-16 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                           dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                                >
                                <button
                                    type="button"
                                    wire:click="$toggle('mostrarPassword')"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-medium text-zinc-600 hover:text-zinc-900
                                           dark:text-zinc-300 dark:hover:text-white"
                                >
                                    {{ $mostrarPassword ? 'Ocultar' : 'Ver' }}
                                </button>
                            </div>
                            @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            @error('nuevaPassword') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Cédula --}}
                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Cédula</label>
                            <input type="text" wire:model.defer="cedula"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                            @error('cedula') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Código asesor --}}
                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Código asesor</label>
                            <input type="text" wire:model.defer="codigo_asesor"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                            @error('codigo_asesor') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Código recibos --}}
                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Código recibos</label>
                            <input type="text" wire:model.defer="codigo_recibos"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                            @error('codigo_recibos') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Categoría asesor (NUEVO) --}}
                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Categoría asesor</label>
                            <select wire:model.defer="categoria_asesor"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                                <option value="">Seleccione...</option>
                                <option value="senior">Senior</option>
                                <option value="master">Master</option>
                            </select>
                            @error('categoria_asesor') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Roles --}}
                        <div class="sm:col-span-2">
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Roles</label>
                            <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($roles as $rol)
                                    <label class="flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-700
                                                  dark:border-zinc-800 dark:text-zinc-200">
                                        <input
                                            type="checkbox"
                                            wire:model.defer="rolesSeleccionados"
                                            value="{{ $rol->id }}"
                                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-400 dark:bg-zinc-900 dark:border-zinc-700"
                                        >
                                        <span>{{ $rol->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    {{-- Actions --}}
                    <div class="mt-6 flex justify-end gap-2">
                        <button
                            type="button"
                            wire:click="$set('openModal', false)"
                            class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50
                                   dark:border-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-900"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800
                                   dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            {{ $modoEditar ? 'Guardar cambios' : 'Crear usuario' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    {{-- DataTable --}}
    @push('scripts')
        <script>
            function iniciarDataTable() {
                if ($.fn.DataTable.isDataTable('#tabla')) {
                    $('#tabla').DataTable().destroy();
                }

                $('#tabla').DataTable({
                    responsive: false,
                    fixedHeader: true,
                    scrollX: true,
                    lengthMenu: [10, 50, 100],
                    language: {
                        lengthMenu: "Ver _MENU_",
                        zeroRecords: "Sin datos",
                        info: "Página _PAGE_ de _PAGES_",
                        infoEmpty: "No hay datos disponibles",
                        infoFiltered: "(Filtrado de _MAX_ registros totales)",
                        search: "Buscar:",
                        paginate: { next: "Siguiente", previous: "Anterior" }
                    }
                });
            }

            document.addEventListener("livewire:load", () => {
                iniciarDataTable();
            });

            document.addEventListener("livewire:navigated", () => {
                setTimeout(() => iniciarDataTable(), 50);
            });

            // Si creas/editar y quieres reinit sin recargar, luego lo conectamos con events.
        </script>
    @endpush

</div>
