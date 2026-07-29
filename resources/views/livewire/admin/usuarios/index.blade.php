<div class="space-y-6">

    {{-- Header --}}
{{-- Header --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
            Usuarios
        </h1>

        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            Gestiona usuarios, roles y categoría de asesor.
        </p>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2">

        <button
            type="button"
            wire:click="exportarExcel"
            wire:loading.attr="disabled"
            wire:target="exportarExcel"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600
                   px-4 py-2 text-sm font-medium text-white transition
                   hover:bg-emerald-500 disabled:cursor-not-allowed
                   disabled:opacity-60 focus:outline-none
                   focus:ring-2 focus:ring-emerald-400"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 4v12m0 0 4-4m-4 4-4-4M5 20h14"
                />
            </svg>

            <span wire:loading.remove wire:target="exportarExcel">
                Exportar Excel
            </span>

            <span wire:loading wire:target="exportarExcel">
                Generando...
            </span>
        </button>

        <button
            type="button"
            wire:click="abrirModal"
            class="inline-flex items-center gap-2 rounded-lg bg-zinc-900
                   px-4 py-2 text-sm font-medium text-white transition
                   hover:bg-zinc-800 dark:bg-white dark:text-zinc-900
                   dark:hover:bg-zinc-200 focus:outline-none
                   focus:ring-2 focus:ring-zinc-400"
        >
            <span class="text-base leading-none">+</span>
            Nuevo usuario
        </button>

    </div>
</div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800
                    dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800
                    dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- Table --}}
    <div
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="p-4 sm:p-6">
            <div wire:ignore>
                <table id="tabla" class="min-w-full text-sm text-left text-zinc-700 dark:text-zinc-300">
                    <thead class="text-xs uppercase bg-zinc-50 text-zinc-600 dark:bg-zinc-900/40 dark:text-zinc-300">
                        <tr>
                            <th class="px-4 py-3">Id Asesor</th>
                            <th class="px-4 py-3">Nombre asesor</th>
                            <th class="px-4 py-3">Id Recibos</th>
                            <th class="px-4 py-3">Cédula</th>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Celular</th>
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
                                <td class="px-4 py-3">{{ $usuario->nombre_asesor ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $usuario->codigo_recibos }}</td>
                                <td class="px-4 py-3">{{ $usuario->cedula }}</td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                            {{ $usuario->initials() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-white">{{ $usuario->name }}
                                            </div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">ID:
                                                {{ $usuario->id }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">{{ $usuario->celular }}</td>
                                <td class="px-4 py-3">{{ $usuario->email }}</td>

                                <td class="px-4 py-3">
                                    @forelse ($usuario->roles as $rol)
                                        <span
                                            class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-xs font-medium text-zinc-700
                                                    dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 mr-1">
                                            {{ $rol->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-zinc-400">Sin rol</span>
                                    @endforelse
                                </td>

                                <td class="px-4 py-3">
                                    @if ($usuario->categoria_asesor)
                                        <span
                                            class="inline-flex items-center rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-xs font-medium text-zinc-700
                                                    dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200">
                                            {{ ucfirst($usuario->categoria_asesor) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" wire:click="editarUsuario({{ $usuario->id }})"
                                            class="rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50
                                                   dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-900">
                                            Editar
                                        </button>

                                        <button type="button"
                                            onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                                            wire:click="eliminarUsuario({{ $usuario->id }})"
                                            class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50
                                                   dark:border-rose-900/40 dark:text-rose-200 dark:hover:bg-rose-900/20">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8"
                                    class="px-6 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
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
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-hidden p-3 sm:p-4">

            {{-- Fondo --}}
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="$set('openModal', false)"></div>

            {{-- Contenedor del modal --}}
            <div class="relative flex w-full max-w-2xl flex-col overflow-hidden
                   rounded-2xl border border-zinc-200 bg-white shadow-2xl
                   dark:border-zinc-800 dark:bg-zinc-950"
                style="height: calc(100dvh - 24px); max-height: calc(100dvh - 24px);">

                {{-- Encabezado fijo --}}
                <div
                    class="flex shrink-0 items-center justify-between
                       border-b border-zinc-200 px-5 py-4 sm:px-6
                       dark:border-zinc-800">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $modoEditar ? 'Editar usuario' : 'Nuevo usuario' }}
                        </h2>

                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Completa la información y asigna los roles.
                        </p>
                    </div>

                    <button type="button" wire:click="$set('openModal', false)"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                           text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800
                           dark:hover:bg-zinc-900 dark:hover:text-white"
                        aria-label="Cerrar">
                        ✕
                    </button>
                </div>

                {{-- Contenido con scroll --}}
                <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-6">

                    {{-- Resumen de errores --}}
                    @if ($errors->any())
                        <div
                            class="mb-5 rounded-lg border border-rose-200 bg-rose-50 p-3
                               text-sm text-rose-800
                               dark:border-rose-900/40 dark:bg-rose-900/20
                               dark:text-rose-200">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="formUsuario"
                        wire:submit.prevent="{{ $modoEditar ? 'actualizarUsuario' : 'guardarUsuario' }}">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                            {{-- Nombre --}}
                            <div>
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Nombre
                                </label>

                                <input type="text" wire:model.defer="name"
                                    class="mt-1 w-full rounded-lg border border-zinc-200 bg-white
                                       px-3 py-2 text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400 focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                                @error('name')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Nombre asesor --}}
                            <div>
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Nombre asesor
                                </label>

                                <input type="text" wire:model.defer="nombre_asesor"
                                    class="mt-1 w-full rounded-lg border border-zinc-200 bg-white
                                       px-3 py-2 text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400 focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                                @error('nombre_asesor')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Email
                                </label>

                                <input type="email" wire:model.defer="email"
                                    class="mt-1 w-full rounded-lg border border-zinc-200 bg-white
                                       px-3 py-2 text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400 focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                                @error('email')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Cédula --}}
                            <div>
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Cédula
                                </label>

                                <input type="text" wire:model.defer="cedula"
                                    class="mt-1 w-full rounded-lg border border-zinc-200 bg-white
                                       px-3 py-2 text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400 focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                                @error('cedula')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Celular --}}
                            <div>
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Celular
                                </label>

                                <input type="text" wire:model.defer="celular"
                                    class="mt-1 w-full rounded-lg border border-zinc-200 bg-white
                                       px-3 py-2 text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400 focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                                @error('celular')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Código asesor --}}
                            <div>
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Código asesor
                                </label>

                                <input type="text" wire:model.defer="codigo_asesor"
                                    class="mt-1 w-full rounded-lg border border-zinc-200 bg-white
                                       px-3 py-2 text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400 focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                                @error('codigo_asesor')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Código recibos --}}
                            <div>
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Código recibos
                                </label>

                                <input type="text" wire:model.defer="codigo_recibos"
                                    class="mt-1 w-full rounded-lg border border-zinc-200 bg-white
                                       px-3 py-2 text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400 focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                                @error('codigo_recibos')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Categoría asesor --}}
                            <div>
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    Categoría asesor
                                </label>

                                <select wire:model.defer="categoria_asesor"
                                    class="mt-1 w-full rounded-lg border border-zinc-200 bg-white
                                       px-3 py-2 text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400 focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                                    <option value="">Seleccione...</option>
                                    <option value="senior">Senior</option>
                                    <option value="master">Master</option>
                                </select>

                                @error('categoria_asesor')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Contraseña --}}
                            <div class="sm:col-span-2">
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    {{ $modoEditar ? 'Nueva contraseña (opcional)' : 'Contraseña' }}
                                </label>

                                <div class="relative mt-1">
                                    <input type="{{ $mostrarPassword ? 'text' : 'password' }}"
                                        wire:model.defer="{{ $modoEditar ? 'nuevaPassword' : 'password' }}"
                                        class="w-full rounded-lg border border-zinc-200 bg-white
                                           px-3 py-2 pr-16 text-sm text-zinc-900 outline-none
                                           focus:border-zinc-400 focus:ring-2 focus:ring-zinc-300
                                           dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                                    <button type="button" wire:click="$toggle('mostrarPassword')"
                                        class="absolute right-3 top-1/2 -translate-y-1/2
                                           text-xs font-medium text-zinc-600
                                           hover:text-zinc-900
                                           dark:text-zinc-300 dark:hover:text-white">
                                        {{ $mostrarPassword ? 'Ocultar' : 'Ver' }}
                                    </button>
                                </div>

                                @error('password')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                                @error('nuevaPassword')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Roles --}}
                            <div class="sm:col-span-2">
                                <div class="flex items-center justify-between gap-3">
                                    <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                        Roles
                                    </label>

                                    <span class="text-xs text-zinc-400">
                                        {{ count($rolesSeleccionados ?? []) }} seleccionados
                                    </span>
                                </div>

                                <div
                                    class="mt-2 grid grid-cols-1 gap-2 rounded-xl
                                       border border-zinc-200 p-3
                                       sm:grid-cols-2 dark:border-zinc-800">
                                    @forelse ($roles as $rol)
                                        <label wire:key="rol-usuario-{{ $rol->id }}"
                                            class="flex cursor-pointer items-center gap-3 rounded-lg
                                               border border-zinc-200 px-3 py-2 text-sm
                                               text-zinc-700 transition hover:bg-zinc-50
                                               dark:border-zinc-800 dark:text-zinc-200
                                               dark:hover:bg-zinc-900">
                                            <input type="checkbox" wire:model.defer="rolesSeleccionados"
                                                value="{{ $rol->id }}"
                                                class="h-4 w-4 shrink-0 rounded border-zinc-300
                                                   text-blue-600 focus:ring-blue-500
                                                   dark:border-zinc-700 dark:bg-zinc-900">

                                            <span class="break-words">
                                                {{ $rol->name }}
                                            </span>
                                        </label>
                                    @empty
                                        <p class="py-4 text-center text-sm text-zinc-500 sm:col-span-2">
                                            No existen roles disponibles.
                                        </p>
                                    @endforelse
                                </div>

                                @error('rolesSeleccionados')
                                    <p class="mt-1 text-xs text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>
                    </form>
                </div>

                {{-- Botones fijos --}}
                <div
                    class="relative z-10 flex shrink-0 items-center justify-end gap-2
                       border-t border-zinc-200 bg-white px-5 py-4
                       shadow-[0_-4px_12px_rgba(0,0,0,0.05)]
                       sm:px-6 dark:border-zinc-800 dark:bg-zinc-950">
                    <button type="button" wire:click="$set('openModal', false)" wire:loading.attr="disabled"
                        class="rounded-lg border border-zinc-200 px-4 py-2
                           text-sm font-medium text-zinc-700 transition
                           hover:bg-zinc-50 disabled:cursor-not-allowed
                           disabled:opacity-50
                           dark:border-zinc-800 dark:text-zinc-200
                           dark:hover:bg-zinc-900">
                        Cancelar
                    </button>

                    <button type="submit" form="formUsuario" wire:loading.attr="disabled"
                        wire:target="{{ $modoEditar ? 'actualizarUsuario' : 'guardarUsuario' }}"
                        class="inline-flex min-w-36 items-center justify-center rounded-lg
                           bg-zinc-900 px-4 py-2 text-sm font-medium text-white
                           transition hover:bg-zinc-800 disabled:cursor-not-allowed
                           disabled:opacity-60
                           dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <span wire:loading.remove
                            wire:target="{{ $modoEditar ? 'actualizarUsuario' : 'guardarUsuario' }}">
                            {{ $modoEditar ? 'Guardar cambios' : 'Crear usuario' }}
                        </span>

                        <span wire:loading wire:target="{{ $modoEditar ? 'actualizarUsuario' : 'guardarUsuario' }}">
                            Guardando...
                        </span>
                    </button>
                </div>

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
                        paginate: {
                            next: "Siguiente",
                            previous: "Anterior"
                        }
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
