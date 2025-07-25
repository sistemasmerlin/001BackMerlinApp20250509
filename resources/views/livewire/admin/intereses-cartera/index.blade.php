<div class="space-y-6">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Cartera - Intereses por mora</h1>
    </div>

    <a href="{{ route('cartera.intereses.calcular') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
        Procesar intereses manualmente
    </a> <br>

    <br>
    @if (session()->has('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800">
        {{ session('error') }}
    </div>
    @endif

    <div class="overflow-x-auto rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 p-6">
        <div wire:ignore>
            <table id="tabla" class="w-full table-auto text-sm text-left text-gray-700 dark:text-zinc-300" style="padding-top: 10px;">
                <thead class="text-xs text-zinc-50 bg-zinc-950 dark:text-zinc-50 uppercase dark:bg-zinc-700">
                    <tr>
                        <th>Factura</th>
                        <th>NIT</th>
                        <th>Razón Social</th>
                        <th>Valor Base</th>
                        <th>Impuestos</th>
                        <th>Valor Factura</th>
                        <th>Abono</th>
                        <th>Saldo</th>
                        <th>Fecha Factura</th>
                        <th>Fecha Hoy</th>
                        <th>Días Transcurridos</th>
                        <th>Asesor</th>
                        <th>Condición de Pago</th>
                        <th>Valor Diario Interés</th>
                        <th>Valor Acumulado Interés</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($facturas as $factura)
                    <tr>
                        <td>{{ $factura['prefijo'] }}{{ $factura['consecutivo'] }}</td>
                        <td>{{ $factura['nit'] }}</td>
                        <td>{{ $factura['razon_social'] }}</td>
                        <td style="text-align: right;">{{ number_format($factura['valor_base'], 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($factura['impuestos'], 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($factura['valor_factura'], 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($factura['abono'], 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($factura['saldo'], 0, ',', '.') }}</td>
                        <td style="text-align: center;">{{ $factura['fecha_factura'] }}</td>
                        <td style="text-align: center;">{{ $factura['fecha_hoy'] }}</td>
                        <td style="text-align: center;">{{ $factura['dias_transcurridos'] }}</td>
                        <td style="text-align: center;">{{ $factura['asesor'] }}</td>
                        <td style="text-align: center;">{{ $factura['condicion_pago'] }}</td>
                        <td style="text-align: right;">{{ number_format($factura['valor_diario_interes'], 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($factura['valor_acumulado_interes'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

        @push('scripts')
        <script>
            function iniciarDataTable() {
                if ($.fn.DataTable.isDataTable('#tabla')) {
                    $('#tabla').DataTable().destroy();
                }

                $('#tabla').DataTable({
                    responsive: false,
                    "lengthMenu": [100, 500, 1000],
                    "language": {
                        "lengthMenu": "Ver _MENU_",
                        "zeroRecords": "Sin datos",
                        "info": "Página _PAGE_ de _PAGES_",
                        "infoEmpty": "No hay datos disponibles",
                        "infoFiltered": "(Filtrado de _MAX_ registros totales)",
                        'search': 'Buscar:',
                        'paginate': {
                            'next': 'Siguiente',
                            'previous': 'Anterior'
                        }
                    }
                });
            }

            //cuando la vista carga por primera vez.
            document.addEventListener("livewire:load", () => {
                iniciarDataTable();
            });
            //cuando se vuelve a la vista.
            document.addEventListener("livewire:navigated", () => {
                setTimeout(() => iniciarDataTable(), 50);
            });
        </script>
        @endpush

    </div>