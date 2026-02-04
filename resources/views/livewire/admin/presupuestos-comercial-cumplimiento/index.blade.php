<div>
    {{-- Filtros --}}
    <div class="card">
        <div class="card-body d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div style="min-width: 260px;">
                <label class="mb-1 text-muted">Periodo</label>
                <select class="form-control" wire:model="periodo">
                    @foreach($periodos as $p)
                        <option value="{{ $p['value'] }}">{{ $p['label'] }} ({{ $p['value'] }})</option>
                    @endforeach
                </select>
            </div>

            <div class="text-right">
                <div class="text-muted">Total venta</div>
                <div style="font-size: 22px; font-weight: 700;">
                    {{ number_format($totalVenta, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla 1: Totales por asesor --}}
    <div class="card">
        <div class="card-header">
            <b>Total por asesor</b>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Vendedor</th>
                        <th class="text-right">Venta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($totalesPorAsesor as $r)
                        <tr>
                            <td>{{ $r['vendedor'] }}</td>
                            <td class="text-right" style="{{ $r['venta'] < 0 ? 'color:#dc3545;font-weight:700;' : '' }}">
                                {{ number_format($r['venta'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted p-3">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabla 2: Totales por asesor y marca --}}
    <div class="card">
        <div class="card-header">
            <b>Total por asesor y marca</b>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Vendedor</th>
                        <th>Marca</th>
                        <th class="text-right">Venta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($totalesPorAsesorMarca as $r)
                        <tr>
                            <td>{{ $r['vendedor'] }}</td>
                            <td>{{ $r['marca'] }}</td>
                            <td class="text-right" style="{{ $r['venta'] < 0 ? 'color:#dc3545;font-weight:700;' : '' }}">
                                {{ number_format($r['venta'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted p-3">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabla 3: Detalle (como tu imagen) --}}
    <div class="card">
        <div class="card-header">
            <b>Detalle</b>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped table-sm mb-0">
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Vendedor</th>
                        <th>Marca</th>
                        <th class="text-right">Venta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r['periodo'] }}</td>
                            <td>{{ $r['vendedor'] }}</td>
                            <td>{{ $r['marca'] }}</td>
                            <td class="text-right" style="{{ $r['venta'] < 0 ? 'color:#dc3545;font-weight:700;' : '' }}">
                                {{ number_format($r['venta'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted p-3">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
