<div class="space-y-6">

    @if (session()->has('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.recibos-caja.index') }}" wire:navigate
                class="mb-3 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                ← Volver a recibos
            </a>

            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
                {{ $recibo->codigo_recibo }}
            </h1>

            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Detalle completo del recibo de caja.
            </p>


            <button wire:click="exportarExcel" wire:loading.attr="disabled"
                class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">
                <span wire:loading.remove>Exportar Plano Excel</span>

                <span wire:loading>
                    Generando...
                </span>
            </button>

            @if ($esBancolombia)
                <button type="button" wire:click="buscarComprobanteBancolombia" wire:loading.attr="disabled"
                    class="btn btn-primary">
                    <span wire:loading.remove wire:target="buscarComprobanteBancolombia">
                        Buscar comprobante
                    </span>

                    <span wire:loading wire:target="buscarComprobanteBancolombia">
                        Buscando...
                    </span>
                </button>
            @endif

        </div>


        @if($esBancolombia)

    <div class="card mt-4">
        <div class="card-header">
            <strong>Validación Bancolombia</strong>
        </div>

        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-4">
                    <small class="text-muted d-block">
                        Fecha del comprobante
                    </small>

                    <strong>
                        {{ $recibo->fecha_comprobante?->format('d/m/Y') ?? 'Sin fecha' }}
                    </strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block">
                        Número de soporte
                    </small>

                    <strong>
                        {{ $recibo->numero_soporte ?: 'Sin número' }}
                    </strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block">
                        Total aplicado a facturas
                    </small>

                    <strong>
                        ${{ number_format($totalAplicadoRecibo, 0, ',', '.') }}
                    </strong>
                </div>

            </div>

            @if($comprobanteValido)

                <div class="alert alert-success mb-3">
                    <strong>Comprobante validado correctamente.</strong><br>
                    {{ $mensajeValidacionComprobante }}
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr>
                                <th>Fecha movimiento</th>
                                <td>
                                    {{ $movimientoBancario->fecha_movimiento?->format('d/m/Y') }}
                                </td>
                            </tr>

                            <tr>
                                <th>Valor movimiento</th>
                                <td>
                                    ${{ number_format(
                                        $movimientoBancario->valor,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>
                            </tr>

                            <tr>
                                <th>Referencia 1</th>
                                <td>
                                    {{ $movimientoBancario->referencia_1 ?: '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Referencia 2</th>
                                <td>
                                    {{ $movimientoBancario->referencia_2 ?: '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Referencia 3</th>
                                <td>
                                    {{ $movimientoBancario->referencia_3 ?: '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            @elseif($busquedaComprobanteRealizada)

                <div class="alert alert-danger mb-0">
                    <strong>El comprobante no pudo ser validado.</strong><br>
                    {{ $mensajeValidacionComprobante }}
                </div> 91179952

            @endif

        </div>
    </div>

@endif

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="abrirModalEstado"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                Cambiar estado
            </button>

            @if ($recibo->ubicacion)
                <button type="button" wire:click="descargarComprobante"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                    Descargar comprobante
                </button>
            @endif
        </div>
    </div>

    {{-- Estado --}}
    @php
        $claseEstado = match ($recibo->estado) {
            'APROBADO' => 'bg-green-100 text-green-800 border-green-200',
            'RECHAZADO' => 'bg-red-100 text-red-800 border-red-200',
            'EN_REVISION' => 'bg-amber-100 text-amber-800 border-amber-200',
            'EXPORTADO' => 'bg-blue-100 text-blue-800 border-blue-200',
            default => 'bg-zinc-100 text-zinc-700 border-zinc-200',
        };
    @endphp

    <div class="flex items-center justify-between rounded-xl border px-5 py-4 {{ $claseEstado }}">
        <div>
            <p class="text-xs font-semibold uppercase">
                Estado actual
            </p>

            <p class="mt-1 text-lg font-bold">
                {{ str_replace('_', ' ', $recibo->estado) }}
            </p>
        </div>

        <div class="text-right text-sm">
            @if ($recibo->usuarioAsignado)
                <p>
                    Gestionado por:
                    <strong>{{ $recibo->usuarioAsignado }}</strong>
                </p>
            @endif

            @if ($recibo->fecha_revision)
                <p>
                    Revisión:
                    {{ $recibo->fecha_revision->format('d/m/Y H:i') }}
                </p>
            @endif
        </div>
    </div>

    {{-- Resumen --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase text-zinc-500">
                Cliente
            </p>

            <p class="mt-2 font-semibold text-zinc-900 dark:text-white">
                {{ $recibo->razon_social ?: 'Sin razón social' }}
            </p>

            <p class="text-sm text-zinc-500">
                NIT: {{ $recibo->nit_cliente }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase text-zinc-500">
                Banco
            </p>

            <p class="mt-2 font-semibold text-zinc-900 dark:text-white">
                {{ $recibo->bancoRecaudo?->descripcion_banco ?: 'Sin banco' }}
            </p>

            <p class="text-sm text-zinc-500">
                {{ $recibo->bancoRecaudo?->numero_cuenta }}
            </p>
        </div>

        <div class="rounded-xl border border-green-200 bg-green-50 p-5">
            <p class="text-xs font-semibold uppercase text-green-700">
                Total recibido
            </p>

            <p class="mt-2 text-2xl font-bold text-green-800">
                ${{ number_format($recibo->total_recibido, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-xs font-semibold uppercase text-amber-700">
                Pendiente por aplicar
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-800">
                ${{ number_format($recibo->total_restante, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Datos generales --}}
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                Información del comprobante
            </h2>

            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase text-zinc-500">
                        Fecha comprobante
                    </dt>

                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                        {{ optional($recibo->fecha_comprobante)->format('d/m/Y') ?: 'Sin fecha' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-zinc-500">
                        Fecha registro
                    </dt>

                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                        {{ optional($recibo->fecha_recibo)->format('d/m/Y H:i') ?: 'Sin fecha' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-zinc-500">
                        Número soporte
                    </dt>

                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                        {{ $recibo->numero_soporte ?: 'Sin número' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-zinc-500">
                        Correo cliente
                    </dt>

                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                        {{ $recibo->email_cliente ?: 'Sin correo' }}
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">
                        Archivo
                    </dt>

                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                        {{ $recibo->adjunto_nombre_archivo ?: 'Sin archivo' }}
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                Información del asesor
            </h2>

            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase text-zinc-500">
                        Código
                    </dt>

                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                        {{ $recibo->id_vendedor ?: 'Sin código' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold uppercase text-zinc-500">
                        Nombre
                    </dt>

                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                        {{ $recibo->nombre_asesor ?: 'Sin información' }}
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase text-zinc-500">
                        Correo
                    </dt>

                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                        {{ $recibo->email_asesor ?: 'Sin información' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Facturas --}}
    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                Facturas aplicadas
            </h2>

            <p class="mt-1 text-sm text-zinc-500">
                {{ $recibo->cxcs->count() }} factura(s) relacionadas.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Documento
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Consecutivo
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">
                            Sucursal
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-zinc-600">
                            Valor aplicado
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-zinc-600">
                            Retención
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($recibo->cxcs as $cxc)
                        <tr>
                            <td class="px-4 py-3 text-sm">
                                {{ $cxc->F353_ID_TIPO_DOCTO_CRUCE }}
                            </td>

                            <td class="px-4 py-3 text-sm">
                                {{ $cxc->F353_CONSEC_DOCTO_CRUCE }}
                            </td>

                            <td class="px-4 py-3 text-sm">
                                {{ $cxc->F353_ID_SUCURSAL_DOCTO_CRUCE }}
                            </td>

                            <td class="px-4 py-3 text-right text-sm font-semibold">
                                ${{ number_format($cxc->F354_VALOR_CR, 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-right text-sm">
                                ${{ number_format($cxc->F354_VALOR_RETENCION, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500">
                                No existen facturas aplicadas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($recibo->cxcs->isNotEmpty())
                    <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold">
                                Total aplicado
                            </td>

                            <td class="px-4 py-3 text-right text-sm font-bold text-green-700">
                                ${{ number_format($recibo->cxcs->sum('F354_VALOR_CR'), 0, ',', '.') }}
                            </td>

                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Observaciones --}}
    @if ($recibo->notas || $recibo->notas_pendiente || $recibo->notas_rechazo)
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                Observaciones
            </h2>

            <div class="mt-4 space-y-4 text-sm">
                @if ($recibo->notas)
                    <div>
                        <p class="font-semibold text-zinc-700">
                            Notas generales
                        </p>

                        <p class="mt-1 text-zinc-600">
                            {{ $recibo->notas }}
                        </p>
                    </div>
                @endif

                @if ($recibo->notas_pendiente)
                    <div class="rounded-lg bg-amber-50 p-4 text-amber-800">
                        <p class="font-semibold">
                            Nota de revisión
                        </p>

                        <p class="mt-1">
                            {{ $recibo->notas_pendiente }}
                        </p>
                    </div>
                @endif

                @if ($recibo->notas_rechazo)
                    <div class="rounded-lg bg-red-50 p-4 text-red-800">
                        <p class="font-semibold">
                            Motivo de rechazo
                        </p>

                        <p class="mt-1">
                            {{ $recibo->notas_rechazo }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Modal estado --}}
    @if ($modalEstado)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                    Actualizar estado
                </h2>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Nuevo estado
                        </label>

                        <select wire:model.live="nuevoEstado"
                            class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="">Seleccionar</option>
                            <option value="RECIBIDO">Recibido</option>
                            <option value="EN_REVISION">En revisión</option>
                            <option value="APROBADO">Aprobado</option>
                            <option value="RECHAZADO">Rechazado</option>
                            <option value="EXPORTADO">Exportado</option>
                        </select>

                        @error('nuevoEstado')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Observación
                        </label>

                        <textarea wire:model="notasEstado" rows="4"
                            class="w-full rounded-lg border border-zinc-300 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            placeholder="Escribe una observación"></textarea>

                        @error('notasEstado')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="cerrarModalEstado"
                        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700">
                        Cancelar
                    </button>

                    <button type="button" wire:click="actualizarEstado" wire:loading.attr="disabled"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 disabled:opacity-50">
                        Guardar estado
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
