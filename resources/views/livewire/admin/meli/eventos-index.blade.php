<div class="min-h-screen bg-white p-4 sm:p-6">

    {{-- Encabezado --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">
                Mercado Libre
            </h1>

            <p class="mt-1 text-sm text-zinc-600">
                Callback y notificaciones recibidas desde Mercado Libre.
            </p>
        </div>

        <button
            type="button"
            wire:click="$refresh"
            wire:loading.attr="disabled"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <flux:icon name="arrow-path" class="h-5 w-5" />

            <span wire:loading.remove wire:target="$refresh">
                Actualizar
            </span>

            <span wire:loading wire:target="$refresh">
                Actualizando...
            </span>
        </button>
    </div>

    {{-- Filtros --}}
    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">

            <div class="md:col-span-5">
                <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                    Buscar
                </label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <flux:icon
                            name="magnifying-glass"
                            class="h-5 w-5 text-zinc-400"
                        />
                    </div>

                    <input
                        type="text"
                        wire:model.live.debounce.500ms="buscar"
                        placeholder="Topic, recurso, usuario o contenido"
                        class="block w-full rounded-lg border border-zinc-300 bg-white py-2.5 pl-10 pr-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    >
                </div>
            </div>

            <div class="md:col-span-3">
                <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                    Tipo
                </label>

                <select
                    wire:model.live="tipo"
                    class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                >
                    <option value="">Todos</option>
                    <option value="CALLBACK">Callback</option>
                    <option value="WEBHOOK">Webhook</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                    Estado
                </label>

                <select
                    wire:model.live="estado"
                    class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                >
                    <option value="">Todos</option>
                    <option value="RECIBIDO">Recibido</option>
                    <option value="PROCESADO">Procesado</option>
                    <option value="ERROR">Error</option>
                </select>
            </div>

            <div class="flex items-end md:col-span-2">
                <button
                    type="button"
                    wire:click="limpiarFiltros"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-400 hover:bg-zinc-50"
                >
                    <flux:icon name="x-mark" class="h-5 w-5" />
                    Limpiar
                </button>
            </div>

        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-zinc-900">
                    Eventos recibidos
                </h2>

                <p class="mt-0.5 text-sm text-zinc-500">
                    {{ $eventos->total() }}
                    {{ $eventos->total() === 1 ? 'registro encontrado' : 'registros encontrados' }}
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            ID
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Tipo
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Topic
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Recurso
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Usuario
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Estado
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Fecha
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Detalle
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 bg-white">
                    @forelse ($eventos as $evento)
                        <tr
                            wire:key="meli-evento-{{ $evento->id }}"
                            class="transition hover:bg-blue-50/40"
                        >
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-zinc-900">
                                #{{ $evento->id }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm">
                                @if ($evento->tipo === 'WEBHOOK')
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        WEBHOOK
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                        CALLBACK
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-700">
                                {{ $evento->topic ?: '—' }}
                            </td>

                            <td class="max-w-xs px-4 py-4 text-sm text-zinc-700">
                                <div
                                    class="truncate"
                                    title="{{ $evento->resource }}"
                                >
                                    {{ $evento->resource ?: '—' }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-700">
                                {{ $evento->user_id ?: '—' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm">
                                @switch($evento->estado)
                                    @case('RECIBIDO')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            RECIBIDO
                                        </span>
                                        @break

                                    @case('PROCESADO')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                            PROCESADO
                                        </span>
                                        @break

                                    @case('ERROR')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            ERROR
                                        </span>
                                        @break

                                    @default
                                        <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">
                                            {{ $evento->estado }}
                                        </span>
                                @endswitch
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-700">
                                <div>
                                    {{ $evento->created_at?->format('d/m/Y') }}
                                </div>

                                <div class="text-xs text-zinc-500">
                                    {{ $evento->created_at?->format('h:i:s A') }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-center">
                                <button
                                    type="button"
                                    wire:click="verEvento({{ $evento->id }})"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-blue-600 transition hover:border-blue-300 hover:bg-blue-100"
                                    title="Ver detalle"
                                >
                                    <flux:icon name="eye" class="h-5 w-5" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                                    <flux:icon
                                        name="inbox"
                                        class="h-6 w-6 text-zinc-400"
                                    />
                                </div>

                                <p class="mt-3 text-sm font-medium text-zinc-700">
                                    No hay eventos para mostrar
                                </p>

                                <p class="mt-1 text-sm text-zinc-500">
                                    Los callback y webhook aparecerán aquí.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($eventos->hasPages())
            <div class="border-t border-zinc-200 px-5 py-4">
                {{ $eventos->links() }}
            </div>
        @endif
    </div>

    {{-- Modal de detalle --}}
    @if ($eventoSeleccionado)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            wire:key="modal-evento-{{ $eventoSeleccionado->id }}"
        >
            <button
                type="button"
                wire:click="cerrarDetalle"
                class="absolute inset-0 h-full w-full bg-zinc-950/50"
                aria-label="Cerrar modal"
            ></button>

            <div class="relative z-10 flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">

                <div class="flex items-start justify-between border-b border-zinc-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">
                            Detalle del evento #{{ $eventoSeleccionado->id }}
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Información recibida desde Mercado Libre
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cerrarDetalle"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800"
                    >
                        <flux:icon name="x-mark" class="h-6 w-6" />
                    </button>
                </div>

                <div class="overflow-y-auto p-5">
                    <div class="grid grid-cols-1 gap-4 rounded-xl bg-zinc-50 p-4 sm:grid-cols-2 lg:grid-cols-4">

                        <div>
                            <span class="block text-xs font-medium uppercase text-zinc-500">
                                Tipo
                            </span>

                            <span class="mt-1 block text-sm font-semibold text-zinc-900">
                                {{ $eventoSeleccionado->tipo }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-medium uppercase text-zinc-500">
                                Método
                            </span>

                            <span class="mt-1 block text-sm font-semibold text-zinc-900">
                                {{ $eventoSeleccionado->metodo }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-medium uppercase text-zinc-500">
                                Topic
                            </span>

                            <span class="mt-1 block text-sm font-semibold text-zinc-900">
                                {{ $eventoSeleccionado->topic ?: '—' }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-medium uppercase text-zinc-500">
                                Usuario
                            </span>

                            <span class="mt-1 block text-sm font-semibold text-zinc-900">
                                {{ $eventoSeleccionado->user_id ?: '—' }}
                            </span>
                        </div>

                        <div class="sm:col-span-2">
                            <span class="block text-xs font-medium uppercase text-zinc-500">
                                Recurso
                            </span>

                            <span class="mt-1 block break-all text-sm font-semibold text-zinc-900">
                                {{ $eventoSeleccionado->resource ?: '—' }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-medium uppercase text-zinc-500">
                                IP
                            </span>

                            <span class="mt-1 block text-sm font-semibold text-zinc-900">
                                {{ $eventoSeleccionado->ip ?: '—' }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-medium uppercase text-zinc-500">
                                Fecha
                            </span>

                            <span class="mt-1 block text-sm font-semibold text-zinc-900">
                                {{ $eventoSeleccionado->created_at?->format('d/m/Y h:i:s A') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="mb-2 text-sm font-semibold text-zinc-900">
                            Payload recibido
                        </h3>

                        <pre class="max-h-96 overflow-auto whitespace-pre-wrap break-all rounded-xl bg-zinc-950 p-4 text-xs leading-5 text-zinc-100">{{ json_encode(
    $eventoSeleccionado->payload,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) }}</pre>
                    </div>

                    <div class="mt-6">
                        <h3 class="mb-2 text-sm font-semibold text-zinc-900">
                            Parámetros de la URL
                        </h3>

                        <pre class="max-h-72 overflow-auto whitespace-pre-wrap break-all rounded-xl bg-zinc-950 p-4 text-xs leading-5 text-zinc-100">{{ json_encode(
    $eventoSeleccionado->query_params,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) }}</pre>
                    </div>

                    <div class="mt-6">
                        <h3 class="mb-2 text-sm font-semibold text-zinc-900">
                            Encabezados
                        </h3>

                        <pre class="max-h-72 overflow-auto whitespace-pre-wrap break-all rounded-xl bg-zinc-950 p-4 text-xs leading-5 text-zinc-100">{{ json_encode(
    $eventoSeleccionado->headers,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) }}</pre>
                    </div>
                </div>

                <div class="flex justify-end border-t border-zinc-200 px-5 py-4">
                    <button
                        type="button"
                        wire:click="cerrarDetalle"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>