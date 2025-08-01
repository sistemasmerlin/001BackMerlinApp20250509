<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Motivos visita</h1>
        <button wire:click="crear" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
            + Nuevo motivo
        </button>
    </div>

    @if (session()->has('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
        {{ session('success') }}
    </div
    @endif

    <div class="bg-white dark:bg-zinc-800 shadow p-4 rounded-xl mb-6 space-y-4">
    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Filtrar visitas</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm text-gray-600 dark:text-gray-300">Vendedor</label>
            <input type="text" wire:model="filtroVendedor"
                class="w-full mt-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm text-gray-600 dark:text-gray-300">Fecha Inicio</label>
            <input type="date" wire:model="filtroFechaInicio"
                class="w-full mt-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm text-gray-600 dark:text-gray-300">Fecha Fin</label>
            <input type="date" wire:model="filtroFechaFin"
                class="w-full mt-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
    </div>

    <div class="text-right">
        <button type="submit" wire:click.prevent="cargarVisitas"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Aplicar Filtros</button>
    </div>
</div>


    <!-- Tabla -->
    <div class="w-4/5 overflow-x-auto mx-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700 p-3">
        <div wire:ignore>
            <table class="w-full mx-auto table-auto text-sm text-left text-gray-700 dark:text-zinc-300">
                <thead class="text-xs text-zinc-50 bg-zinc-950 uppercase dark:bg-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-center">ID</th>
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

    <!-- Mapa -->
    <div wire:ignore id="map" style="height: 500px;" class="my-6 rounded-xl overflow-hidden shadow"></div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
    crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>

<script>
    document.addEventListener('livewire:init', function () {
        initMap();
    });

    function initMap() {
        // Eliminar mapa existente si hay uno
        if (window.mapInstance) {
            window.mapInstance.remove();
        }

        const path = [];

        const mapElement = document.getElementById('map');
        if (!mapElement) return;

        // Coordenadas por defecto (Centro de Colombia)
        const defaultCoords = [4.5709, -74.2973];
        const map = L.map('map').setView(defaultCoords, 6);
        window.mapInstance = map; // Guardar referencia global

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Cargar datos desde Livewire
        const visitas = @json($visitasOrdenadas ?? []);

        if (visitas && visitas.length > 0) {
            const markers = [];
            let hasValidPoints = false;

            visitas.forEach((visita, index) => {
                if (visita.latitud && visita.longitud) {
                    const lat = parseFloat(visita.latitud);
                    const lng = parseFloat(visita.longitud);
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        hasValidPoints = true;
                        const marker = L.marker([lat, lng]).addTo(map);
                        
                        let popupContent = `
                            <div class="text-sm">
                                <strong>Cliente:</strong> ${visita.razon_social || 'Visita sin nombre'}<br>
                                <small><strong>Nit: </strong>${visita.vendedor || 'Vendedor no especificado'}</small><br>
                                <small><strong>Sucursal: </strong>${visita.sucursal || 'Vendedor no especificado'}</small><br>
                                <small><strong>Visita:</strong>${visita.motivo}</small>
                                <small><strong>Fecha:</strong>${visita.created_at}</small>
                            </div>
                        `;
                        
                        marker.bindPopup(popupContent);
                        markers.push(marker);

                        path.push([lat, lng]);
                    }
                }
            });


        // 👉 Dibujamos la línea entre los puntos
        if (path.length > 1) {
            const polyline = L.polyline(path, {
                color: 'blue',
                weight: 3,
                opacity: 0.8,
                smoothFactor: 1
            }).addTo(map);
        }
            if (markers.length > 0) {
                // Ajustar vista para mostrar todos los marcadores
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.2));
            } else {
                // Mostrar mensaje si no hay puntos válidos
                L.popup()
                    .setLatLng(defaultCoords)
                    .setContent("No se encontraron ubicaciones válidas")
                    .openOn(map);
            }
        } else {
            // Mostrar mensaje si no hay datos
            L.popup()
                .setLatLng(defaultCoords)
                .setContent("No hay datos de visitas para mostrar")
                .openOn(map);
        }
    }

    // Reiniciar el mapa cuando Livewire actualice el DOM
    Livewire.hook('morph.updated', () => {
        initMap();
    });
</script>
@endpush