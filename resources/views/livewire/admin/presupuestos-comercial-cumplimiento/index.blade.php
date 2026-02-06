<div class="space-y-3">

    {{-- Header --}}
    <div class="card shadow-sm">
        <div class="card-body d-flex flex-wrap align-items-end justify-content-between gap-3">

            <div style="min-width: 280px;">
                <label class="form-label text-muted mb-1">Periodo</label>
                <select class="form-control" wire:model.live="periodo">
                    @foreach($periodos as $p)
                        <option value="{{ $p['value'] }}">{{ $p['label'] }} ({{ $p['value'] }})</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex flex-wrap gap-3 justify-content-end">

                {{-- KPI Presupuesto --}}
                <div class="text-end">
                    <div class="text-muted small">Presupuesto total</div>
                    <div class="h5 mb-0 fw-bold">
                        {{ number_format($totalPresupuesto, 2, ',', '.') }}
                    </div>
                </div>

                {{-- KPI Venta --}}
                <div class="text-end">
                    <div class="text-muted small">Venta total</div>
                    <div class="h5 mb-0 fw-bold">
                        {{ number_format($totalVenta, 2, ',', '.') }}
                    </div>
                </div>

                {{-- KPI Cumplimiento --}}
                <div class="text-end" style="min-width: 180px;">
                    <div class="text-muted small">Cumplimiento</div>
                    <div class="h5 mb-1 fw-bold">
                        {{ number_format($cumplimientoTotal, 2, ',', '.') }}%
                    </div>

                    {{-- Barra --}}
                    <div class="progress" style="height:8px;">
                        @php
                            $bar = max(0, min(100, $cumplimientoTotal));
                            $cls = $cumplimientoTotal >= 100 ? 'bg-success' : ($cumplimientoTotal >= 80 ? 'bg-primary' : 'bg-warning');
                        @endphp
                        <div class="progress-bar {{ $cls }}" role="progressbar" style="width: {{ $bar }}%"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- Venta por marca --}}
        <div class="col-12 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-bold">Venta por marca (global)</div>
                    <span class="badge bg-light text-dark">{{ count($ventaPorMarca) }} marcas</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Marca</th>
                                <th class="text-end">Venta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventaPorMarca as $r)
                                <tr>
                                    <td class="fw-semibold">{{ $r['marca'] }}</td>
                                    <td class="text-end {{ $r['venta'] < 0 ? 'text-danger fw-bold' : 'fw-semibold' }}">
                                        {{ number_format($r['venta'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">Sin datos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        {{-- Venta por asesor (acordeón Livewire) --}}
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="fw-bold">Venta por asesor</div>
                    <span class="text-muted small">Click en un asesor para ver marcas</span>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($asesores as $a)
                        @php
                            $isOpen = ($openAsesor === $a['vendedor']);
                        @endphp

                        {{-- Fila asesor --}}
                        <div class="list-group-item">
                            <button
                                type="button"
                                class="btn w-100 p-0 text-start d-flex justify-content-between align-items-center"
                                wire:click="toggleAsesor('{{ $a['vendedor'] }}')"
                            >
                                <div>
                                    <div class="fw-bold">
                                        {{ $a['nombre'] }}
                                        <span class="text-muted fw-semibold">({{ $a['vendedor'] }})</span>
                                    </div>
                                    <div class="text-muted small">
                                        {{ $isOpen ? 'Ocultar detalle' : 'Ver detalle por marca' }}
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <div class="text-end {{ $a['venta'] < 0 ? 'text-danger fw-bold' : 'fw-bold' }}">
                                        {{ number_format($a['venta'], 2, ',', '.') }}
                                    </div>
                                    <span class="text-muted">
                                        {!! $isOpen ? '▾' : '▸' !!}
                                    </span>
                                </div>
                            </button>

                            {{-- Detalle marcas --}}
                            @if($isOpen)
                                <div class="mt-3 border rounded p-2 bg-light">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0 align-middle">
                                            <thead>
                                                <tr class="text-muted">
                                                    <th>Marca</th>
                                                    <th class="text-end">Venta</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($a['marcas'] as $m)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $m['marca'] }}</td>
                                                        <td class="text-end {{ $m['venta'] < 0 ? 'text-danger fw-bold' : 'fw-semibold' }}">
                                                            {{ number_format($m['venta'], 2, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">Sin datos</div>
                    @endforelse
                </div>

            </div>
        </div>

    </div>

    {{-- Detalle crudo (déjalo oculto por defecto) --}}
    <details class="card shadow-sm">
        <summary class="card-header bg-white fw-bold" style="cursor:pointer;">
            Detalle (crudo) — abrir/cerrar
        </summary>
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Periodo</th>
                        <th>Vendedor</th>
                        <th>Marca</th>
                        <th class="text-end">Venta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r['periodo'] }}</td>
                            <td>{{ $r['vendedor'] }}</td>
                            <td>{{ $r['marca'] }}</td>
                            <td class="text-end {{ $r['venta'] < 0 ? 'text-danger fw-bold' : '' }}">
                                {{ number_format($r['venta'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted p-3">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </details>

</div>
