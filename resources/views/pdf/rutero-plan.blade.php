<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Rutero comercial {{ $mes }}</title>
    <style>
        @page { margin: 32px; }
        body { font: 11px "DejaVu Sans", sans-serif; color: #222; }
        h1 { font-size: 22px; margin: 0 0 6px; color: #a8000e; }
        h2 { margin: 20px 0 8px; font-size: 15px; color: #a8000e; }
        h3 { margin: 14px 0 5px; font-size: 12px; }
        p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0 10px; }
        th, td { border: 1px solid #eadcdd; padding: 6px; text-align: left; }
        th { background: #fff0f1; }
        .valor { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Rutero comercial</h1>
    <p><strong>Asesor</strong> {{ $asesor->nombre_asesor ?: $asesor->name }} · {{ $asesor->codigo_asesor }}</p>
    <p><strong>Mes</strong> {{ $mes }}</p>
    <p><strong>Estado</strong> {{ ucfirst(str_replace('_', ' ', $plan->estado)) }}</p>

    @if ($plan->enviado_revision_at)
        <p><strong>Enviado a revisión</strong> {{ $plan->enviado_revision_at }}</p>
    @endif

    <h2>Visitas programadas</h2>

    @forelse ($ruta as $fecha => $filas)
        <h3>{{ \Carbon\CarbonImmutable::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM') }}</h3>

        <table>
            <thead>
                <tr>
                    <th>Ciudad</th>
                    <th>Cliente</th>
                    <th>Sucursal y punto</th>
                    <th>Dirección</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    <tr>
                        <td>{{ $fila->ciudad }} · {{ $fila->departamento }}</td>
                        <td>
                            @if ($fila->parada_id)
                                {{ $fila->razon_social }}<br>NIT {{ $fila->nit }}
                            @else
                                Ciudad sin visitas seleccionadas
                            @endif
                        </td>
                        <td>{{ $fila->parada_id ? $fila->id_sucursal . ' / ' . $fila->punto_envio_id : '—' }}</td>
                        <td>{{ $fila->direccion ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>Este plan todavía no tiene ciudades programadas.</p>
    @endforelse

    <h2>Gastos estimados por semana</h2>

    @forelse ($gastosPorSemana as $lunes => $dias)
        <h3>Semana del {{ \Carbon\CarbonImmutable::parse($lunes)->format('d/m/Y') }}</h3>

        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hospedaje</th>
                    <th>Alimentación</th>
                    <th>Transp. intermunicipal</th>
                    <th>Urbano o combustible</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dias as $dia)
                    <tr>
                        <td>{{ \Carbon\CarbonImmutable::parse($dia['fecha'])->format('d/m') }}</td>
                        @foreach (['hospedaje', 'alimentacion', 'transporte_intermunicipal', 'transporte_urbano_combustible', 'total'] as $campo)
                            <td class="valor">${{ number_format($dia[$campo], 0, ',', '.') }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Total de la semana</strong> ${{ number_format($dias->sum('total'), 0, ',', '.') }}</p>
    @empty
        <p>El plan no tiene gastos estimados con valor mayor a cero.</p>
    @endforelse

    <p><strong>Total estimado del mes</strong> ${{ number_format($totalMes, 0, ',', '.') }} COP</p>
</body>
</html>