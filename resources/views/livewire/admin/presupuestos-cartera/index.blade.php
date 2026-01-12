<div class="space-y-6">

    {{-- Mensaje éxito --}}
    @if (session()->has('success'))
        <div class="mb-2 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (!empty($erroresImport))
        <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold mb-1">Errores de importación:</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($erroresImport as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header + Botones --}}
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Presupuesto Cartera (Recaudos)</h1>

        <div class="flex gap-2">
            <button wire:click="abrirImport"
                    class="px-4 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-900 text-white font-medium shadow"
                    wire:loading.attr="disabled" wire:target="abrirImport">
                Importar
            </button>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" wire:model.live="search" placeholder="Buscar asesor, cliente, nit, doc..."
               class="border rounded px-3 py-2">

        <input type="text" maxlength="6" wire:model.live="fPeriodo" placeholder="Periodo YYYYMM"
               class="border rounded px-3 py-2">

        <input type="text" wire:model.live="fAsesor" placeholder="Asesor (0306)"
               class="border rounded px-3 py-2">

        <input type="text" wire:model.live="fCondPago" placeholder="Cond Pago (30D)"
               class="border rounded px-3 py-2">
    </div>

    {{-- Tabla --}}
    <div class="w-full overflow-x-auto rounded-xl shadow border border-gray-200 dark:border-zinc-700">
        <table class="min-w-full text-sm text-left text-gray-700 dark:text-zinc-300">
            <thead class="text-xs text-zinc-50 bg-zinc-950 uppercase dark:bg-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-center">#</th>
                    <th class="px-4 py-3">Periodo</th>
                    <th class="px-4 py-3">Asesor</th>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">NIT</th>
                    <th class="px-4 py-3">Doc</th>
                    <th class="px-4 py-3">Cond</th>
                    <th class="px-4 py-3">Fecha doc</th>
                    <th class="px-4 py-3">Fecha corte</th>
                    <th class="px-4 py-3 text-right">Saldo</th>
                    <th class="px-4 py-3 text-right">Días</th>
                </tr>
            </thead>

            <tbody>
                @forelse($recaudos as $i => $r)
                    <tr class="border-b border-gray-200">
                        <td class="px-4 py-2 text-center">{{ $recaudos->firstItem() + $i }}</td>
                        <td class="px-4 py-2">{{ $r->periodo }}</td>
                        <td class="px-4 py-2">{{ $r->asesor }}</td>
                        <td class="px-4 py-2">{{ $r->nombre_asesor }}</td>
                        <td class="px-4 py-2">{{ $r->cliente }}</td>
                        <td class="px-4 py-2">{{ $r->nit_cliente }}</td>
                        <td class="px-4 py-2">{{ $r->prefijo }}-{{ $r->consecutivo }}</td>
                        <td class="px-4 py-2">{{ $r->cond_pago }}</td>
                        <td class="px-4 py-2">{{ $r->fecha_doc }}</td>
                        <td class="px-4 py-2">{{ $r->fecha_corte }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($r->saldo, 0) }}</td>
                        <td class="px-4 py-2 text-right">{{ $r->dias }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-6 text-center text-gray-500">Sin resultados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $recaudos->links() }}
    </div>

    {{-- Modal Importación --}}
@if($modalImport)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-3">
  <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 shadow-2xl border border-zinc-200 dark:border-zinc-700">
    
    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
      <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Importar presupuesto cartera</h2>
      <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
        Formato: <b>.xlsx</b> o <b>.csv</b>
      </p>
    </div>

    <div class="px-6 py-4 space-y-3">
      <p class="text-sm text-zinc-600 dark:text-zinc-300">
        Encabezados esperados:
      </p>

      <code class="block text-xs bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100
                   rounded-lg p-3 leading-relaxed whitespace-normal break-words">
asesor,nombre_asesor,prefijo,consecutivo,saldo,nit_cliente,cliente,fecha_doc,fecha_corte,dias,cond_pago,periodo
      </code>

      <input type="file" wire:model="archivo" accept=".xlsx,.csv,.txt"
             class="w-full text-sm file:mr-3 file:px-4 file:py-2 file:rounded-lg
                    file:border-0 file:bg-zinc-900 file:text-white
                    hover:file:bg-zinc-800
                    border rounded-lg px-3 py-2" />

      @error('archivo') <small class="text-red-600">{{ $message }}</small> @enderror
    </div>

    <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-2">
      <button class="px-4 py-2 rounded-lg bg-zinc-500 hover:bg-zinc-600 text-white"
              wire:click="$set('modalImport', false)">
        Cancelar
      </button>

      <button   class="px-4 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-900 text-white font-medium shadow"
              wire:click="procesarImport">
        Importar
      </button>
    </div>

  </div>
</div>
@endif


</div>
