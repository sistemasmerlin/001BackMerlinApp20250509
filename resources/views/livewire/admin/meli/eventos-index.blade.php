<div>
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="m-0">Mercado Libre</h1>
                    <p class="text-muted mb-0">
                        Callback y notificaciones recibidas
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="$refresh"
                    class="btn btn-outline-primary"
                >
                    <i class="fas fa-sync-alt mr-1"></i>
                    Actualizar
                </button>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-5 mb-2">
                            <label>Buscar</label>

                            <input
                                type="text"
                                wire:model.live.debounce.500ms="buscar"
                                class="form-control"
                                placeholder="Topic, recurso, usuario o contenido..."
                            >
                        </div>

                        <div class="col-md-3 mb-2">
                            <label>Tipo</label>

                            <select wire:model.live="tipo" class="form-control">
                                <option value="">Todos</option>
                                <option value="CALLBACK">Callback</option>
                                <option value="WEBHOOK">Webhook</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label>Estado</label>

                            <select wire:model.live="estado" class="form-control">
                                <option value="">Todos</option>
                                <option value="RECIBIDO">Recibido</option>
                                <option value="PROCESADO">Procesado</option>
                                <option value="ERROR">Error</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button
                                type="button"
                                wire:click="limpiarFiltros"
                                class="btn btn-outline-secondary btn-block"
                            >
                                Limpiar
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">
                        Eventos recibidos
                    </h3>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Topic</th>
                                <th>Recurso</th>
                                <th>Usuario</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th class="text-center">Detalle</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($eventos as $evento)
                                <tr wire:key="meli-evento-{{ $evento->id }}">
                                    <td>{{ $evento->id }}</td>

                                    <td>
                                        @if ($evento->tipo === 'WEBHOOK')
                                            <span class="badge badge-primary">
                                                WEBHOOK
                                            </span>
                                        @else
                                            <span class="badge badge-info">
                                                CALLBACK
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $evento->topic ?: '—' }}
                                    </td>

                                    <td style="max-width: 320px;">
                                        <span
                                            class="d-inline-block text-truncate"
                                            style="max-width: 300px;"
                                            title="{{ $evento->resource }}"
                                        >
                                            {{ $evento->resource ?: '—' }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $evento->user_id ?: '—' }}
                                    </td>

                                    <td>
                                        @switch($evento->estado)
                                            @case('RECIBIDO')
                                                <span class="badge badge-success">
                                                    RECIBIDO
                                                </span>
                                                @break

                                            @case('PROCESADO')
                                                <span class="badge badge-primary">
                                                    PROCESADO
                                                </span>
                                                @break

                                            @case('ERROR')
                                                <span class="badge badge-danger">
                                                    ERROR
                                                </span>
                                                @break

                                            @default
                                                <span class="badge badge-secondary">
                                                    {{ $evento->estado }}
                                                </span>
                                        @endswitch
                                    </td>

                                    <td>
                                        {{ $evento->created_at?->format('d/m/Y h:i:s A') }}
                                    </td>

                                    <td class="text-center">
                                        <button
                                            type="button"
                                            wire:click="verEvento({{ $evento->id }})"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        No se han recibido eventos de Mercado Libre.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($eventos->hasPages())
                    <div class="card-footer">
                        {{ $eventos->links() }}
                    </div>
                @endif
            </div>

            @if ($eventoSeleccionado)
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            Detalle del evento #{{ $eventoSeleccionado->id }}
                        </h3>

                        <div class="card-tools">
                            <button
                                type="button"
                                wire:click="cerrarDetalle"
                                class="btn btn-tool"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-6">
                                <strong>Tipo:</strong>
                                {{ $eventoSeleccionado->tipo }}
                            </div>

                            <div class="col-md-6">
                                <strong>Método:</strong>
                                {{ $eventoSeleccionado->metodo }}
                            </div>

                            <div class="col-md-6 mt-2">
                                <strong>Topic:</strong>
                                {{ $eventoSeleccionado->topic ?: '—' }}
                            </div>

                            <div class="col-md-6 mt-2">
                                <strong>User ID:</strong>
                                {{ $eventoSeleccionado->user_id ?: '—' }}
                            </div>

                            <div class="col-md-12 mt-2">
                                <strong>Recurso:</strong>
                                {{ $eventoSeleccionado->resource ?: '—' }}
                            </div>

                            <div class="col-md-6 mt-2">
                                <strong>IP:</strong>
                                {{ $eventoSeleccionado->ip ?: '—' }}
                            </div>

                            <div class="col-md-6 mt-2">
                                <strong>Fecha:</strong>
                                {{ $eventoSeleccionado->created_at?->format('d/m/Y h:i:s A') }}
                            </div>

                        </div>

                        <hr>

                        <h5>Payload recibido</h5>

                        <pre class="p-3 rounded"
                             style="background:#111827; color:#f8fafc; max-height:500px; overflow:auto;">{{ json_encode(
    $eventoSeleccionado->payload,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) }}</pre>

                        <h5 class="mt-4">Parámetros de la URL</h5>

                        <pre class="p-3 rounded"
                             style="background:#111827; color:#f8fafc; max-height:300px; overflow:auto;">{{ json_encode(
    $eventoSeleccionado->query_params,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) }}</pre>

                        <h5 class="mt-4">Encabezados</h5>

                        <pre class="p-3 rounded"
                             style="background:#111827; color:#f8fafc; max-height:300px; overflow:auto;">{{ json_encode(
    $eventoSeleccionado->headers,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) }}</pre>
                    </div>
                </div>
            @endif

        </div>
    </section>
</div>