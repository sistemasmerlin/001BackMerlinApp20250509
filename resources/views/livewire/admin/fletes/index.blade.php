<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Excel Fletes Ciudades</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Importa masivamente y gestiona los fletes por ciudad.</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800
                    dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @elseif (session()->has('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800
                    dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- Import --}}
    @can('Subir fletes masivo')
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <form wire:submit.prevent="importarFlete" enctype="multipart/form-data" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Importar archivo Excel</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Formatos permitidos: .xls, .xlsx</p>
                </div>

                <div class="flex items-center gap-3">
                    <input
                        type="file"
                        wire:model="excel_fletes"
                        accept=".xls,.xlsx"
                        class="block w-full max-w-xs rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900
                               file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-xs file:font-medium file:text-white
                               hover:file:bg-zinc-800 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:file:bg-white dark:file:text-zinc-900 dark:hover:file:bg-zinc-200"
                        required
                    >

                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800
                               dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Importar
                    </button>
                </div>
            </form>
        </div>
    @endcan

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="p-4 sm:p-6">
            <div wire:ignore>
                <table id="tabla" class="min-w-full text-sm text-left text-zinc-700 dark:text-zinc-300">
                    <thead class="text-xs uppercase bg-zinc-50 text-zinc-600 dark:bg-zinc-900/40 dark:text-zinc-300">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Departamento</th>
                            <th class="px-4 py-3">Cod Dep</th>
                            <th class="px-4 py-3">Ciudad</th>
                            <th class="px-4 py-3">Cod Ciudad</th>
                            <th class="px-4 py-3">Menor</th>
                            <th class="px-4 py-3">Mayor</th>
                            <th class="px-4 py-3">Mínimo</th>
                            <th class="px-4 py-3">Entrega</th>
                            <th class="px-4 py-3">Monto</th>
                            <th class="px-4 py-3">Monto Min</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($fletes as $flete)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30">
                                <td class="px-4 py-3">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">{{ $flete->depto }}</td>
                                <td class="px-4 py-3">{{ $flete->cod_depto }}</td>
                                <td class="px-4 py-3">{{ $flete->ciudad }}</td>
                                <td class="px-4 py-3">{{ $flete->cod_ciudad }}</td>
                                <td class="px-4 py-3">{{ $flete->menor }}%</td>
                                <td class="px-4 py-3">{{ $flete->mayor }}%</td>
                                <td class="px-4 py-3">${{ number_format($flete->minimo) }}</td>
                                <td class="px-4 py-3">{{ $flete->entrega }}</td>
                                <td class="px-4 py-3">${{ number_format($flete->monto) }}</td>
                                <td class="px-4 py-3">${{ number_format($flete->monto_minimo) }}</td>

                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @can('Editar Flete')
                                            <button
                                                type="button"
                                                wire:click="editarFlete({{ $flete->id }})"
                                                class="rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50
                                                       dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-900"
                                                title="Editar"
                                            >
                                                Editar
                                            </button>
                                        @endcan

                                        @can('Eliminar Flete')
                                            <button
                                                type="button"
                                                wire:click="eliminarFlete({{ $flete->id }})"
                                                onclick="return confirm('¿Estás seguro de eliminar este flete?')"
                                                class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50
                                                       dark:border-rose-900/40 dark:text-rose-200 dark:hover:bg-rose-900/20"
                                                title="Eliminar"
                                            >
                                                Eliminar
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-6 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No hay fletes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    {{-- Modal Edición --}}
    @if ($modalEditar)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="$set('modalEditar', false)"></div>

            <div class="relative w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Editar flete</h2>
                    <button
                        type="button"
                        wire:click="$set('modalEditar', false)"
                        class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200"
                        aria-label="Cerrar"
                    >
                        ✕
                    </button>
                </div>

                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-200">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="actualizarFlete" class="mt-5 space-y-4">
                    <input type="hidden" wire:model="fleteId">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Departamento</label>
                            <input type="text" wire:model.defer="depto" readonly
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-2 text-sm text-zinc-900
                                       dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Código Depto</label>
                            <input type="text" wire:model.defer="cod_depto" readonly
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-2 text-sm text-zinc-900
                                       dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Ciudad</label>
                            <input type="text" wire:model.defer="ciudad" readonly
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-2 text-sm text-zinc-900
                                       dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Código Ciudad</label>
                            <input type="text" wire:model.defer="cod_ciudad" readonly
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-2 text-sm text-zinc-900
                                       dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Menor</label>
                            <input type="text" wire:model.defer="menor"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Mayor</label>
                            <input type="text" wire:model.defer="mayor"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Mínimo</label>
                            <input type="text" wire:model.defer="minimo"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Entrega</label>
                            <input type="text" wire:model.defer="entrega"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Monto</label>
                            <input type="number" wire:model.defer="monto"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Monto mínimo</label>
                            <input type="number" wire:model.defer="monto_minimo"
                                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-300
                                       dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                        </div>

                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button
                            type="button"
                            wire:click="$set('modalEditar', false)"
                            class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50
                                   dark:border-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-900"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800
                                   dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            Guardar cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif


    {{-- DataTable --}}

    @push('scripts')
<script>
    function iniciarDataTable() {
        if (!window.$ || !$.fn || !$.fn.dataTable) return;

        const $tabla = $('#tabla');
        if (!$tabla.length) return;

        if ($.fn.dataTable.isDataTable($tabla)) {
            $tabla.DataTable().destroy(true);
        }

        $tabla.DataTable({
            responsive: false,
            fixedHeader: true,
            scrollX: true,
            lengthMenu: [50, 500, 5000],
            language: {
                lengthMenu: "Ver _MENU_",
                zeroRecords: "Sin datos",
                info: "Página _PAGE_ de _PAGES_",
                infoEmpty: "No hay datos disponibles",
                infoFiltered: "(Filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                paginate: {
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        });
    }

    document.addEventListener('livewire:load', () => {
        iniciarDataTable();

        Livewire.hook('message.processed', () => {
            iniciarDataTable();
        });
    });
</script>
@endpush

</div>
