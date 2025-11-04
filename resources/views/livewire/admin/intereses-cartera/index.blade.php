<div class="space-y-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Cartera - Intereses por mora</h1>
  </div>

  <a href="{{ route('cartera.intereses.calcular') }}"
     class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
    Procesar intereses manualmente
  </a>

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

  {{-- Filtros --}}
  <div class="w-full max-w-5xl mx-auto bg-white/80 dark:bg-zinc-900/70 backdrop-blur
              border border-zinc-200/70 dark:border-zinc-700 rounded-2xl shadow p-4">
    <div class="flex flex-wrap items-end gap-3">
      <div>
        <label class="block text-xs text-gray-600 dark:text-gray-300">Inicio</label>
        <input type="date" wire:model.live.debounce.200ms="inicio"
               @if($fin) max="{{ $fin }}" @endif
               class="mt-1 rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900
                      focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 px-3 py-1.5 text-sm">
      </div>
      <div>
        <label class="block text-xs text-gray-600 dark:text-gray-300">Fin</label>
        <input type="date" wire:model.live.debounce.200ms="fin"
               @if($inicio) min="{{ $inicio }}" @endif
               class="mt-1 rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900
                      focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 px-3 py-1.5 text-sm">
      </div>
      <div class="flex items-center gap-2">
        <button type="button" wire:click="aplicarFiltros"
                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
          Aplicar
        </button>
        <button type="button" wire:click="limpiarFiltros"
                class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700
                       text-zinc-800 dark:text-zinc-200 rounded-lg text-sm">
          Hoy
        </button>
      </div>
    </div>
    @error('inicio') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    @error('fin')    <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
  </div>

  {{-- Tabla --}}
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
        <tbody><!-- lo llena DataTables via JS --></tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
  const fmt = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 });

  let dt = null;

  function iniciarDataTable() {
    if (dt) { dt.destroy(); dt = null; }

    dt = $('#tabla').DataTable({
      responsive: false,
      lengthMenu: [100, 500, 1000],
      language: {
        lengthMenu: "Ver _MENU_",
        zeroRecords: "Sin datos",
        info: "Página _PAGE_ de _PAGES_",
        infoEmpty: "No hay datos disponibles",
        infoFiltered: "(Filtrado de _MAX_ registros totales)",
        search: 'Buscar:',
        paginate: { next: 'Siguiente', previous: 'Anterior' }
      },
      columns: [
        { data: 'factura' },
        { data: 'nit' },
        { data: 'razon_social' },
        { data: 'valor_base_fmt', className: 'text-right' },
        { data: 'impuestos_fmt', className: 'text-right' },
        { data: 'valor_factura_fmt', className: 'text-right' },
        { data: 'abono_fmt', className: 'text-right' },
        { data: 'saldo_fmt', className: 'text-right' },
        { data: 'fecha_factura', className: 'text-center' },
        { data: 'fecha_hoy', className: 'text-center' },
        { data: 'dias_transcurridos', className: 'text-center' },
        { data: 'asesor', className: 'text-center' },
        { data: 'condicion_pago', className: 'text-center' },
        { data: 'valor_diario_interes_fmt', className: 'text-right' },
        { data: 'valor_acumulado_interes_fmt', className: 'text-right' },
      ],
      data: [] // se llena al recibir el evento
    });
  }

  function mapearFilas(facturas) {
    return facturas.map(f => ({
      factura: `${f.prefijo ?? ''}${f.consecutivo ?? ''}`,
      nit: f.nit ?? '',
      razon_social: f.razon_social ?? '',
      valor_base_fmt: fmt.format(f.valor_base ?? 0),
      impuestos_fmt: fmt.format(f.impuestos ?? 0),
      valor_factura_fmt: fmt.format(f.valor_factura ?? 0),
      abono_fmt: fmt.format(f.abono ?? 0),
      saldo_fmt: fmt.format(f.saldo ?? 0),
      fecha_factura: f.fecha_factura ?? '',
      fecha_hoy: f.fecha_hoy ?? '',
      dias_transcurridos: f.dias_transcurridos ?? 0,
      asesor: f.asesor ?? '',
      condicion_pago: f.condicion_pago ?? '',
      valor_diario_interes_fmt: fmt.format(f.valor_diario_interes ?? 0),
      valor_acumulado_interes_fmt: fmt.format(f.valor_acumulado_interes ?? 0),
    }));
  }

  function actualizarTabla(facturas) {
    if (!dt) iniciarDataTable();
    const rows = mapearFilas(facturas || []);
    dt.clear();
    dt.rows.add(rows);
    dt.draw(false);
  }

  // Inicializar al cargar Livewire
  document.addEventListener('livewire:load', () => {
    iniciarDataTable();
  });

  // 🔊 Escuchar el evento de Livewire v3
  window.addEventListener('facturas-actualizadas', (ev) => {
    actualizarTabla(ev.detail.facturas);
  });

  // Si usas wire:navigate
  document.addEventListener('livewire:navigated', () => {
    setTimeout(() => iniciarDataTable(), 50);
  });
</script>
@endpush
