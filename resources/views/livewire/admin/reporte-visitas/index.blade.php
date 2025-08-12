<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Motivos visita</h1>
    </div>

    @if (session()->has('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
        {{ session('success') }}
    </div>
    @endif

    <!-- Filtros -->
    <div class="w-3/4 mx-auto bg-white dark:bg-zinc-800 shadow-lg p-4 rounded-xl mb-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Filtrar visitas</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Vendedor</label>
                <input type="text" wire:model="filtroVendedor" class="w-full mt-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div> -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Vendedor</label>
                <select wire:model="filtroVendedor" 
                        class="w-full mt-1 rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-6 font-medium py-1">
                    <option value="">Selecciona un vendedor</option>
                    @foreach($vendedores as $vendedor)
                        <option value="{{ $vendedor->codigo_asesor }}">
                            {{ $vendedor->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Fecha Inicio</label>
                <input type="date" wire:model="filtroFechaInicio"
                    class="w-full mt-1 rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-medium py-1">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300">Fecha Fin</label>
                <input type="date" wire:model="filtroFechaFin"
                    class="w-full mt-1 rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-medium py-1">
            </div>
        </div>
        <div class="text-right">
            <button type="submit" wire:click.prevent="cargarVisitas"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Aplicar Filtros</button>
        </div>
    </div>

    @if($visitas->isNotEmpty())

    <!-- Mapa -->
    <div id="map" style="height: 400px; width:800px" class="my-6 rounded-xl overflow-hidden shadow mx-auto"></div>

    <!-- Tabla -->
        <div class="w-4/5 overflow-x-auto mx-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-6">
        <button onclick="exportarTabla('tablas')" class="bg-green-500 hover:bg-green-600 text-zinc-100 px-2 py-1 rounded mb-2">Exportar</button>

            <table id="tablas" class="w-3/4 mx-auto table-auto text-sm text-left text-gray-700 dark:text-zinc-300 pt-2">
                <thead class="text-xs text-zinc-50 bg-zinc-950 uppercase dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-center">ID</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Nit</th>
                        <th class="px-4 py-3">Razón Social</th>
                        <th class="px-4 py-3">Sucursal</th>
                        <th class="px-4 py-3">Vendedor</th>
                        <th class="px-4 py-3">Latitud</th>
                        <th class="px-4 py-3">Longitud</th>
                        <th class="px-4 py-3">Ciudad</th>
                        <th class="px-4 py-3">Notas</th>
                        <th class="px-4 py-3">Motivos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visitas as $visita)
                    <tr class="border-b border-gray-200">
                        <td class="text-center">{{ $visita->id }}</td>
                        <td>{{ $visita->created_at }}</td>
                        <td>{{ $visita->nit }}</td>
                        <td>{{ $visita->razon_social }}</td>
                        <td>{{ $visita->sucursal }}</td>
                        <td>{{ $visita->vendedor }}</td>
                        <td>{{ $visita->latitud }}</td>
                        <td>{{ $visita->longitud }}</td>
                        <td>{{ $visita->ciudad }}</td>
                        <td>{{ $visita->notas }}</td>
                        <td>
                            @foreach($visita->motivos as $motivos)
                            <ul><li>{{ $motivos->motivo }}</li></ul>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Debug -->
        @if($mostrarDebug)
        <div class="bg-gray-100 p-4 rounded-lg">
            <h3 class="font-bold mb-2">Datos de Debug:</h3>
            <pre class="text-xs text-gray-500 overflow-auto max-h-60">
                @if ($visitas)
                    Visitas: {{ $visitas->count() }} registros
                    Última visita: @if($visitas->isNotEmpty()) {{ $visitas->first()->created_at }} @else N/A @endif
                    
                    Primeros 3 registros:
                    @foreach($visitas->take(3) as $visita)
                        ID: {{ $visita->id }}
                        Latitud: {{ $visita->latitud }}
                        Longitud: {{ $visita->longitud }}
                        ---------------------------------
                    @endforeach
                @else
                    NO HAY DATOS DE VISITAS
                @endif
            </pre>
        </div>
        @endif
    @else
    @endif

    

    
</div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
    crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>

<script>
    Livewire.on('visitasActualizadas', ( visitas ) => {
        // Espera al siguiente ciclo del DOM para asegurar que #map está disponible
        setTimeout(() => {
            const datos = Array.isArray(visitas[0]) ? visitas[0] : visitas;
            console.log("✅ Actualizando mapa con nuevas visitas:", datos);
            initMap(datos);
        }, 100);
    });
</script>

<script>
    let mapInstance;

    document.addEventListener('livewire:init', function () {
        // Al recibir nuevas visitas desde Livewire
        Livewire.on('visitasActualizadas', (visitas) => {
            console.log("✅ Actualizando mapa con nuevas visitas:", visitas);
            initMap(visitas);
        });
    });

    function initMap(visitas) {
        if (mapInstance) {
            mapInstance.remove();
        }

        const mapElement = document.getElementById('map');
        if (!mapElement) return;

        const defaultCoords = [4.5709, -74.2973];
        mapInstance = L.map('map').setView(defaultCoords, 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapInstance);

        const path = [];
        const markers = [];

        visitas.forEach((visita) => {

            console.log('visitas', visita);
            const lat = parseFloat(visita.latitud);
            const lng = parseFloat(visita.longitud);

            if (!isNaN(lat) && !isNaN(lng)) {
                const marker = L.marker([lat, lng]).addTo(mapInstance);
                marker.bindPopup(`
                    <div class="text-sm">
                        <strong>Cliente:</strong> ${visita.razon_social || 'N/A'}<br>
                        <small><strong>Vendedor:</strong> ${visita.vendedor || 'N/A'}</small><br>
                        <small><strong>Sucursal:</strong> ${visita.sucursal || 'N/A'}</small><br>
                        <small><strong>Motivo:</strong> ${visita.motivo || 'N/A'}</small><br>
                        <small><strong>Fecha:</strong> ${visita.created_at || 'N/A'}</small>
                    </div>
                `);
                markers.push(marker);
                path.push([lat, lng]);
            }
        });

        if (path.length > 1) {
            L.polyline(path, {
                color: 'blue',
                weight: 3,
                opacity: 0.8,
                smoothFactor: 1
            }).addTo(mapInstance);
        }

        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            mapInstance.fitBounds(group.getBounds().pad(0.2));
        } else {
            L.popup()
                .setLatLng(defaultCoords)
                .setContent("No se encontraron ubicaciones válidas")
                .openOn(mapInstance);
        }
    }
</script>

<script>
    let dataTableInstance = null;

    function iniciarDataTable() {
        if (dataTableInstance) {
            dataTableInstance.destroy();
        }

        dataTableInstance = $('#tablas').DataTable({
            responsive: false,
            fixedHeader: true,
            scrollX: true,
            "lengthMenu": [10, 500, 10000],
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

    // Inicializar cuando Livewire carga
    document.addEventListener("livewire:init", () => {
        Livewire.on('visitasActualizadas', (visitas) => {
            setTimeout(() => iniciarDataTable(), 50);
        });
        
        iniciarDataTable();
    });

    // Reiniciar al navegar (para Turbolinks/Livewire Navigation)
    document.addEventListener("livewire:navigated", () => {
        setTimeout(() => iniciarDataTable(), 50);
    });

    function exportarTabla(idTabla) {
            let tabla = document.getElementById(idTabla);
            let wb = XLSX.utils.table_to_book(tabla, {sheet: "Sheet JS"});
            XLSX.writeFile(wb, "ReporteMotivosVentas.xlsx");
        }
</script>

@endpush
