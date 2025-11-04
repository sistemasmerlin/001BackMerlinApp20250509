<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-4 mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Reporte Visitas y Ventas</h1>
    </div>

    @if (session()->has('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
        {{ session('success') }}
    </div>
    @endif

    <!-- Filtros -->

    <form wire:submit.prevent="cargarVisitas"
      class="sticky top-2 z-20">
  <fieldset class="w-full max-w-5xl mx-auto bg-white/80 dark:bg-zinc-900/70 backdrop-blur
                   border border-zinc-200/70 dark:border-zinc-800 shadow-md
                   rounded-2xl p-4 md:p-5 space-y-4 relative">

    <!-- Loading overlay -->
    <div wire:loading.delay
         class="absolute inset-0 rounded-2xl bg-white/50 dark:bg-black/30 grid place-items-center">
      <div class="animate-spin h-6 w-6 border-2 border-current border-t-transparent rounded-full"></div>
    </div>

    <div class="flex items-center justify-between">
      <legend class="text-base font-semibold text-zinc-800 dark:text-zinc-100">
        Filtrar visitas
      </legend>

      <div class="flex items-center gap-2">
        <!-- RANGOS RÁPIDOS -->
        <button type="button" wire:click="rangoRapido('hoy')"
          class="text-xs md:text-sm px-2.5 py-1 rounded-lg border border-zinc-200 dark:border-zinc-700
                 hover:bg-zinc-50 dark:hover:bg-zinc-800">Hoy</button>
        <button type="button" wire:click="rangoRapido('semana')"
          class="text-xs md:text-sm px-2.5 py-1 rounded-lg border border-zinc-200 dark:border-zinc-700
                 hover:bg-zinc-50 dark:hover:bg-zinc-800">Esta semana</button>
        <button type="button" wire:click="rangoRapido('mes')"
          class="text-xs md:text-sm px-2.5 py-1 rounded-lg border border-zinc-200 dark:border-zinc-700
                 hover:bg-zinc-50 dark:hover:bg-zinc-800">Este mes</button>

        <!-- LIMPIAR -->
        <button type="button" wire:click="limpiarFiltros"
          class="text-xs md:text-sm px-2.5 py-1 rounded-lg border border-transparent
                 hover:bg-zinc-100 dark:hover:bg-zinc-800">Limpiar</button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
      <!-- Vendedor -->
      <div>
        <label class="block text-sm text-zinc-600 dark:text-zinc-300 font-medium">Vendedor</label>
        <select wire:model.live.debounce.200ms="filtroVendedor"
                class="w-full mt-1 rounded-xl border-zinc-300 dark:border-zinc-700
                       bg-white dark:bg-zinc-900 text-sm md:text-base
                       focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 px-3 py-2">
          <option value="">— Selecciona un vendedor —</option>
          @foreach($vendedores as $vendedor)
            <option value="{{ $vendedor->codigo_asesor }}">{{ $vendedor->nombre }}</option>
          @endforeach
        </select>
      </div>

      <!-- Fecha inicio -->
      <div>
        <label class="block text-sm text-zinc-600 dark:text-zinc-300 font-medium">Fecha inicio</label>
        <input type="date"
               wire:model.live.debounce.200ms="filtroFechaInicio"
               @if($filtroFechaFin) max="{{ $filtroFechaFin }}" @endif
               class="w-full mt-1 rounded-xl border-zinc-300 dark:border-zinc-700
                      bg-white dark:bg-zinc-900 text-sm md:text-base
                      focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 px-3 py-2">
      </div>

      <!-- Fecha fin -->
      <div>
        <label class="block text-sm text-zinc-600 dark:text-zinc-300 font-medium">Fecha fin</label>
        <input type="date"
               wire:model.live.debounce.200ms="filtroFechaFin"
               @if($filtroFechaInicio) min="{{ $filtroFechaInicio }}" @endif
               class="w-full mt-1 rounded-xl border-zinc-300 dark:border-zinc-700
                      bg-white dark:bg-zinc-900 text-sm md:text-base
                      focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 px-3 py-2">
      </div>
    </div>

    <!-- Errores -->
    @error('filtroFechaInicio')
      <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('filtroFechaFin')
      <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div class="flex items-center justify-end gap-2 pt-1">
      <button type="submit"
              wire:loading.attr="disabled"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                     bg-blue-600 text-white hover:bg-blue-700
                     disabled:opacity-60 disabled:cursor-not-allowed">
        <span wire:loading.remove>Aplicar filtros</span>
        <span wire:loading class="flex items-center gap-2">
          <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8v3A5 5 0 009 12H4z"></path>
          </svg>
          Filtrando…
        </span>
      </button>
    </div>
  </fieldset>
</form>

    @if($visitas->isNotEmpty())

    <!-- Mapa -->
    <div id="map" style="height: 700px; width:1200px" class="my-6 rounded-xl overflow-hidden shadow mx-auto"></div>

    <div wire:ignore>
  <div class="w-[92%] mx-auto overflow-x-auto rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow">
    <div class="flex items-center justify-between p-3">
      <button onclick="exportarTabla('tablas')"
              class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-zinc-100 px-2 py-1 rounded mb-2">
        Exportar
      </button>

      <span class="text-xs text-zinc-500">{{ number_format($visitas->count()) }} registros</span>
    </div>

    <table id="tablas" name="tablas" class="min-w-[900px] w-full text-sm text-left text-zinc-700 dark:text-zinc-300">
      <thead class="sticky top-0 z-10 text-xs uppercase bg-zinc-950 text-white dark:bg-zinc-800">
        <tr>
          <th class="px-3 py-3 text-center w-20">ID</th>
          <th class="px-3 py-3 w-40">Fecha</th>
          <th class="px-3 py-3 w-40">Nit</th>
          <th class="px-3 py-3 w-64">Razón Social</th>
          <th class="px-3 py-3 w-48">Sucursal</th>
          <th class="px-3 py-3 w-44">Vendedor</th>
          <th class="px-3 py-3 w-52">Ubicación</th>
          <th class="px-3 py-3 w-40">Ciudad</th>
          <th class="px-3 py-3 w-[28rem]">Notas</th>
          <th class="px-3 py-3 w-72">Motivos</th>
          <th class="px-2 py-3 text-center w-16">▾</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
        @foreach ($visitas as $visita)
          @php
            $fecha = optional($visita->created_at)->timezone('America/Bogota')->format('Y-m-d H:i');
            $coords = trim(($visita->latitud ?? '').','.( $visita->longitud ?? ''));
            $maps  = $coords ? "https://maps.google.com/?q={$coords}" : null;
          @endphp

          <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/40">
            <!-- ID -->
            <td class="px-3 py-2 text-center font-medium text-zinc-800 dark:text-zinc-100">
              {{ $visita->id }}
            </td>

            <!-- Fecha -->
            <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300 whitespace-nowrap">
              <span title="{{ $visita->created_at }}">{{ $fecha }}</span>
            </td>

            <!-- Nit -->
            <td class="px-3 py-2 font-medium text-zinc-800 dark:text-zinc-100">
              {{ $visita->nit }}
            </td>

            <!-- Razón social -->
            <td class="px-3 py-2">
              <div class="max-w-[18rem] truncate" title="{{ $visita->razon_social }}">
                {{ $visita->razon_social }}
              </div>
            </td>

            <!-- Sucursal -->
            <td class="px-3 py-2">
              <div class="max-w-[14rem] truncate" title="{{ $visita->sucursal }}">
                {{ $visita->sucursal }}
              </div>
            </td>

            <!-- Vendedor -->
            <td class="px-3 py-2">
              <span class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5">
                <span class="i-lucide-user w-3.5 h-3.5"></span>{{ $visita->vendedor }}
              </span>
            </td>

            <!-- Ubicación -->
            <td class="px-3 py-2">
              @if($coords)
                <div class="flex items-center gap-2">
                  <a href="{{ $maps }}" target="_blank"
                     class="text-blue-600 hover:underline"
                     title="Abrir en Google Maps">
                    {{ $coords }}
                  </a>
                  <!-- <button type="button"
                          class="text-xs px-2 py-0.5 rounded bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                          onclick="navigator.clipboard.writeText('{{ $coords }}')">
                    Copiar
                  </button> -->
                </div>
              @else
                <span class="text-zinc-400">—</span>
              @endif
            </td>

            <!-- Ciudad -->
            <td class="px-3 py-2">
              <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200 px-2 py-0.5">
                {{ $visita->ciudad ?: '—' }}
              </span>
            </td>

            <!-- Notas (truncadas en fila) -->
            <td class="px-3 py-2">
              @php $nota = (string)($visita->notas ?? ''); @endphp
              @if(strlen($nota) > 0)
                <div class="max-w-[24rem] truncate" title="{{ $nota }}">
                  {{ $nota }}
                </div>
              @else
                <span class="text-zinc-400">Sin notas</span>
              @endif
            </td>

            <!-- Motivos (chips) -->
            <td class="px-3 py-2">
              @if($visita->motivos && $visita->motivos->count())
                <div class="flex flex-wrap gap-1.5">
                  @foreach($visita->motivos as $m)
                    <span class="inline-flex items-center rounded-full border border-zinc-200 dark:border-zinc-700
                                 bg-white dark:bg-zinc-900 px-2 py-0.5">
                      {{ $m->motivo }}
                    </span>
                  @endforeach
                </div>
              @else
                <span class="text-zinc-400">—</span>
              @endif
            </td>

            <!-- Expand -->
            <td class="px-2 py-2 text-center">
              <button type="button"
                      class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200"
                      onclick="toggleDetalle(this)">
                ▾
              </button>
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

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js" 
defer></script>

<script>
  let dataTableInstance = null;

  function formatChildHTML(data) {
    const nota    = data.nota || 'Sin notas';
    const maps    = data.maps || '';
    const coords  = data.coords || '';
    const motivos = data.motivos || '—';

    return `
      <div class="px-2 py-3">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
          <div>
            <h4 class="font-semibold text-zinc-700 dark:text-zinc-200 mb-1">Notas</h4>
            <div class="whitespace-pre-line bg-white dark:bg-zinc-950 rounded-lg border border-zinc-200 dark:border-zinc-800 p-3">
              ${_.escape(nota)}
            </div>
          </div>
          <div>
            <h4 class="font-semibold text-zinc-700 dark:text-zinc-200 mb-1">Motivos</h4>
            <div>${_.escape(motivos)}</div>
          </div>
          <div>
            <h4 class="font-semibold text-zinc-700 dark:text-zinc-200 mb-1">Mapa</h4>
            ${maps ? `<a class="text-blue-600 hover:underline" target="_blank" href="${maps}">Ver en Google Maps (${_.escape(coords)})</a>` : '<span class="text-zinc-400">Sin coordenadas</span>'}
          </div>
        </div>
      </div>
    `;
  }

  // Pequeño escape (usa lodash si lo tienes; si no, quita _.escape o implementa tu propio escape)
  window._ = window._ || { escape: s => String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])) };

  
  // Livewire hooks: reinit después de actualizar datos
  document.addEventListener("livewire:init", () => {
    Livewire.on('visitasActualizadas', () => {
      setTimeout(() => iniciarDataTable(), 50);
    });
    iniciarDataTable();
  });

  document.addEventListener("livewire:navigated", () => {
    setTimeout(() => iniciarDataTable(), 50);
  });

  // Export a Excel
  function exportarTabla(idTabla) {
    const tabla = document.getElementById(idTabla);
    if (!tabla) return alert('No se encontró la tabla');

    // Para que no se meta el contenido de child rows (que DataTables inserta fuera del <tbody>),
    // exportamos la tabla base del DOM:
    const clone = tabla.cloneNode(true);
    // si tuvieras una columna de acciones, podrías removerla aquí del clone

    const wb = XLSX.utils.table_to_book(clone, { sheet: "Visitas" });
    XLSX.writeFile(wb, "ReporteVisitas.xlsx");
  }
</script>

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
            "lengthMenu": [200, 500, 1000],
            "order": [ [0, "desc"] ],
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
