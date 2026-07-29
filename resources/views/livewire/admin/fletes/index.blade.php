<div class="space-y-6">

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
                Fletes por ciudad
            </h1>

            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Importa, consulta, exporta y administra los fletes por ciudad.
            </p>
        </div>
    </div>


    {{-- ============================================================
        MENSAJES
    ============================================================ --}}
    @if (session()->has('success'))
        <div
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3
                   text-sm text-emerald-800
                   dark:border-emerald-900/40 dark:bg-emerald-900/20
                   dark:text-emerald-200"
        >
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3
                   text-sm text-rose-800
                   dark:border-rose-900/40 dark:bg-rose-900/20
                   dark:text-rose-200"
        >
            {{ session('error') }}
        </div>
    @endif


    {{-- ============================================================
        IMPORTACIÓN DE EXCEL
    ============================================================ --}}
    @can('Subir fletes masivo')
        <div
            class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm
                   dark:border-zinc-800 dark:bg-zinc-950"
        >
            <form
                wire:submit.prevent="importarFlete"
                enctype="multipart/form-data"
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">
                        Importar archivo Excel
                    </p>

                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Formatos permitidos: .xls y .xlsx. Tamaño máximo: 2 MB.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <input
                        type="file"
                        wire:model="excel_fletes"
                        accept=".xls,.xlsx"
                        class="block w-full rounded-lg border border-zinc-200
                               bg-white px-3 py-2 text-sm text-zinc-900
                               file:mr-3 file:rounded-md file:border-0
                               file:bg-zinc-900 file:px-3 file:py-2
                               file:text-xs file:font-medium file:text-white
                               hover:file:bg-zinc-800
                               dark:border-zinc-800 dark:bg-zinc-950
                               dark:text-white dark:file:bg-white
                               dark:file:text-zinc-900
                               dark:hover:file:bg-zinc-200"
                    >

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="importarFlete,excel_fletes"
                        class="inline-flex min-w-32 items-center justify-center
                               rounded-lg bg-zinc-900 px-4 py-2 text-sm
                               font-medium text-white hover:bg-zinc-800
                               disabled:cursor-not-allowed disabled:opacity-60
                               dark:bg-white dark:text-zinc-900
                               dark:hover:bg-zinc-200"
                    >
                        <span
                            wire:loading.remove
                            wire:target="importarFlete"
                        >
                            Importar
                        </span>

                        <span
                            wire:loading
                            wire:target="importarFlete"
                        >
                            Importando...
                        </span>
                    </button>
                </div>
            </form>

            @error('excel_fletes')
                <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">
                    {{ $message }}
                </p>
            @enderror

            <div
                wire:loading
                wire:target="excel_fletes"
                class="mt-2 text-xs text-zinc-500 dark:text-zinc-400"
            >
                Cargando archivo...
            </div>
        </div>
    @endcan


    {{-- ============================================================
        FILTROS Y EXPORTACIÓN
    ============================================================ --}}
    <div
        class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm
               dark:border-zinc-800 dark:bg-zinc-950"
    >
        <div
            class="flex flex-col gap-4 xl:flex-row
                   xl:items-end xl:justify-between"
        >
            <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-end">

                {{-- Buscador --}}
                <div class="w-full max-w-2xl">
                    <label
                        for="buscar"
                        class="mb-1 block text-xs font-medium text-zinc-600
                               dark:text-zinc-300"
                    >
                        Buscar ciudad o departamento
                    </label>

                    <div class="relative">
                        <input
                            id="buscar"
                            type="search"
                            wire:model.live.debounce.400ms="buscar"
                            placeholder="Ejemplo: Bogotá, Antioquia, Medellín..."
                            autocomplete="off"
                            class="w-full rounded-lg border border-zinc-200
                                   bg-white px-3 py-2 pl-10 pr-10 text-sm
                                   text-zinc-900 outline-none
                                   transition focus:border-zinc-400
                                   focus:ring-2 focus:ring-zinc-200
                                   dark:border-zinc-800 dark:bg-zinc-950
                                   dark:text-white dark:placeholder-zinc-500
                                   dark:focus:border-zinc-600
                                   dark:focus:ring-zinc-800"
                        >

                        {{-- Ícono búsqueda --}}
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0
                                   flex items-center pl-3"
                        >
                            <svg
                                class="h-4 w-4 text-zinc-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m21 21-4.35-4.35m2.35-5.65
                                       a8 8 0 1 1-16 0
                                       8 8 0 0 1 16 0Z"
                                />
                            </svg>
                        </div>

                        {{-- Cargando búsqueda --}}
                        <div
                            wire:loading
                            wire:target="buscar"
                            class="absolute inset-y-0 right-0 flex
                                   items-center pr-3"
                        >
                            <svg
                                class="h-4 w-4 animate-spin text-zinc-500"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>

                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 0 1 8-8v4
                                       a4 4 0 0 0-4 4H4Z"
                                ></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Registros por página --}}
                <div class="w-full md:w-40">
                    <label
                        for="perPage"
                        class="mb-1 block text-xs font-medium text-zinc-600
                               dark:text-zinc-300"
                    >
                        Ver registros
                    </label>

                    <select
                        id="perPage"
                        wire:model.live="perPage"
                        class="w-full rounded-lg border border-zinc-200
                               bg-white px-3 py-2 text-sm text-zinc-900
                               outline-none focus:border-zinc-400
                               focus:ring-2 focus:ring-zinc-200
                               dark:border-zinc-800 dark:bg-zinc-950
                               dark:text-white dark:focus:border-zinc-600
                               dark:focus:ring-zinc-800"
                    >
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="500">500</option>
                    </select>
                </div>

                {{-- Limpiar filtro --}}
                @if ($buscar !== '')
                    <button
                        type="button"
                        wire:click="limpiarFiltro"
                        class="inline-flex items-center justify-center gap-2
                               rounded-lg border border-zinc-200 px-4 py-2
                               text-sm font-medium text-zinc-700
                               hover:bg-zinc-50
                               dark:border-zinc-800 dark:text-zinc-200
                               dark:hover:bg-zinc-900"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18 18 6M6 6l12 12"
                            />
                        </svg>

                        Limpiar
                    </button>
                @endif
            </div>

            {{-- Botón exportar --}}
            <button
                type="button"
                wire:click="exportar"
                wire:loading.attr="disabled"
                wire:target="exportar"
                class="inline-flex items-center justify-center gap-2
                       rounded-lg bg-emerald-600 px-4 py-2
                       text-sm font-medium text-white
                       hover:bg-emerald-700
                       disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg
                    wire:loading.remove
                    wire:target="exportar"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 10v6m0 0-3-3m3 3 3-3
                           M6 20h12a2 2 0 0 0 2-2V6
                           a2 2 0 0 0-2-2h-5.586
                           a1 1 0 0 0-.707.293l-1.414 1.414
                           A1 1 0 0 1 9.586 6H6
                           a2 2 0 0 0-2 2v10
                           a2 2 0 0 0 2 2Z"
                    />
                </svg>

                <svg
                    wire:loading
                    wire:target="exportar"
                    class="h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 0 1 8-8v4
                           a4 4 0 0 0-4 4H4Z"
                    ></path>
                </svg>

                <span wire:loading.remove wire:target="exportar">
                    Exportar Excel
                </span>

                <span wire:loading wire:target="exportar">
                    Exportando...
                </span>
            </button>
        </div>

        {{-- Resumen del filtro --}}
        <div
            class="mt-4 flex flex-col gap-1 text-xs text-zinc-500
                   dark:text-zinc-400 sm:flex-row sm:items-center
                   sm:justify-between"
        >
            <div>
                @if ($buscar !== '')
                    Resultados encontrados para:

                    <span class="font-semibold text-zinc-700 dark:text-zinc-200">
                        “{{ $buscar }}”
                    </span>
                @else
                    Mostrando todos los fletes activos.
                @endif
            </div>

            <div>
                Total encontrados:

                <span class="font-semibold text-zinc-700 dark:text-zinc-200">
                    {{ number_format($fletes->total(), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>


    {{-- ============================================================
        TABLA
    ============================================================ --}}
    <div
        class="overflow-hidden rounded-xl border border-zinc-200
               bg-white shadow-sm
               dark:border-zinc-800 dark:bg-zinc-950"
    >
        <div class="overflow-x-auto">
            <table
                class="min-w-full text-left text-sm
                       text-zinc-700 dark:text-zinc-300"
            >
                <thead
                    class="bg-zinc-50 text-xs uppercase text-zinc-600
                           dark:bg-zinc-900/50 dark:text-zinc-300"
                >
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3">
                            #
                        </th>

                        <th class="whitespace-nowrap px-4 py-3">
                            Departamento
                        </th>

                        <th class="whitespace-nowrap px-4 py-3">
                            Cód. departamento
                        </th>

                        <th class="whitespace-nowrap px-4 py-3">
                            Ciudad
                        </th>

                        <th class="whitespace-nowrap px-4 py-3">
                            Cód. ciudad
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right">
                            Menor
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right">
                            Mayor
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right">
                            Mínimo
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-center">
                            Entrega
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right">
                            Monto
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right">
                            Monto mínimo
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-right">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody
                    wire:loading.class="opacity-50"
                    wire:target="buscar,perPage,limpiarFiltro"
                    class="divide-y divide-zinc-100
                           dark:divide-zinc-800"
                >
                    @forelse ($fletes as $flete)
                        <tr
                            wire:key="flete-{{ $flete->id }}"
                            class="transition hover:bg-zinc-50
                                   dark:hover:bg-zinc-900/30"
                        >
                            <td class="whitespace-nowrap px-4 py-3">
                                {{ $fletes->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                {{ $flete->depto }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                {{ $flete->cod_depto }}
                            </td>

                            <td
                                class="whitespace-nowrap px-4 py-3
                                       font-medium text-zinc-900
                                       dark:text-white"
                            >
                                {{ $flete->ciudad }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                {{ $flete->cod_ciudad }}
                            </td>

                            <td
                                class="whitespace-nowrap px-4 py-3
                                       text-right"
                            >
                                {{ number_format((float) $flete->menor, 2, ',', '.') }}%
                            </td>

                            <td
                                class="whitespace-nowrap px-4 py-3
                                       text-right"
                            >
                                {{ number_format((float) $flete->mayor, 2, ',', '.') }}%
                            </td>

                            <td
                                class="whitespace-nowrap px-4 py-3
                                       text-right"
                            >
                                ${{ number_format((float) $flete->minimo, 0, ',', '.') }}
                            </td>

                            <td
                                class="whitespace-nowrap px-4 py-3
                                       text-center"
                            >
                                {{ $flete->entrega }}
                            </td>

                            <td
                                class="whitespace-nowrap px-4 py-3
                                       text-right"
                            >
                                ${{ number_format((float) $flete->monto, 0, ',', '.') }}
                            </td>

                            <td
                                class="whitespace-nowrap px-4 py-3
                                       text-right"
                            >
                                ${{ number_format((float) $flete->monto_minimo, 0, ',', '.') }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">

                                    @can('Editar Flete')
                                        <button
                                            type="button"
                                            wire:click="editarFlete({{ $flete->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="editarFlete({{ $flete->id }})"
                                            class="inline-flex items-center gap-1
                                                   rounded-lg border
                                                   border-zinc-200 px-3 py-1.5
                                                   text-xs font-medium
                                                   text-zinc-700
                                                   hover:bg-zinc-50
                                                   disabled:opacity-60
                                                   dark:border-zinc-700
                                                   dark:text-zinc-200
                                                   dark:hover:bg-zinc-900"
                                        >
                                            <svg
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="m16.862 4.487 1.687-1.688
                                                       a1.875 1.875 0 1 1
                                                       2.652 2.652L10.582 16.07
                                                       a4.5 4.5 0 0 1-1.897
                                                       1.13L6 18l.8-2.685
                                                       a4.5 4.5 0 0 1
                                                       1.13-1.897l8.932-8.931Z"
                                                />
                                            </svg>

                                            Editar
                                        </button>
                                    @endcan

                                    @can('Eliminar Flete')
                                        <button
                                            type="button"
                                            wire:click="eliminarFlete({{ $flete->id }})"
                                            wire:confirm="¿Estás seguro de eliminar el flete de {{ $flete->ciudad }}?"
                                            wire:loading.attr="disabled"
                                            wire:target="eliminarFlete({{ $flete->id }})"
                                            class="inline-flex items-center gap-1
                                                   rounded-lg border
                                                   border-rose-200 px-3 py-1.5
                                                   text-xs font-medium
                                                   text-rose-700
                                                   hover:bg-rose-50
                                                   disabled:opacity-60
                                                   dark:border-rose-900/40
                                                   dark:text-rose-200
                                                   dark:hover:bg-rose-900/20"
                                        >
                                            <svg
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="m14.74 9-.346 9
                                                       m-4.788 0L9.26 9
                                                       m9.968-3.21
                                                       c.342.052.682.107
                                                       1.022.166
                                                       M18.16 5.79 17.824 19.2
                                                       A2.25 2.25 0 0 1
                                                       15.576 21H8.424
                                                       a2.25 2.25 0 0 1
                                                       2.248-1.8L5.84 5.79
                                                       m12.32 0a48.108 48.108
                                                       0 0 0-3.478-.397
                                                       m-12.56.562
                                                       c.34-.059.68-.114
                                                       1.022-.165
                                                       m0 0a48.11 48.11
                                                       0 0 1 3.478-.397
                                                       m7.5 0V4.477
                                                       c0-1.18-.91-2.164
                                                       -2.09-2.201
                                                       a51.964 51.964 0 0 0
                                                       -3.32 0
                                                       c-1.18.037-2.09
                                                       1.022-2.09 2.201v.916
                                                       m7.5 0a48.667 48.667
                                                       0 0 0-7.5 0"
                                                />
                                            </svg>

                                            Eliminar
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="12"
                                class="px-6 py-14 text-center"
                            >
                                <div
                                    class="mx-auto flex max-w-md flex-col
                                           items-center"
                                >
                                    <div
                                        class="mb-3 flex h-12 w-12
                                               items-center justify-center
                                               rounded-full bg-zinc-100
                                               dark:bg-zinc-900"
                                    >
                                        <svg
                                            class="h-6 w-6 text-zinc-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="m21 21-4.35-4.35
                                                   m2.35-5.65
                                                   a8 8 0 1 1-16 0
                                                   8 8 0 0 1 16 0Z"
                                            />
                                        </svg>
                                    </div>

                                    <p
                                        class="font-medium text-zinc-800
                                               dark:text-zinc-200"
                                    >
                                        No se encontraron registros
                                    </p>

                                    <p
                                        class="mt-1 text-sm text-zinc-500
                                               dark:text-zinc-400"
                                    >
                                        @if ($buscar !== '')
                                            No existen ciudades o departamentos
                                            relacionados con “{{ $buscar }}”.
                                        @else
                                            No hay fletes registrados actualmente.
                                        @endif
                                    </p>

                                    @if ($buscar !== '')
                                        <button
                                            type="button"
                                            wire:click="limpiarFiltro"
                                            class="mt-4 rounded-lg border
                                                   border-zinc-200 px-4 py-2
                                                   text-sm font-medium
                                                   text-zinc-700
                                                   hover:bg-zinc-50
                                                   dark:border-zinc-800
                                                   dark:text-zinc-200
                                                   dark:hover:bg-zinc-900"
                                        >
                                            Limpiar búsqueda
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        {{-- ========================================================
            PAGINACIÓN
        ======================================================== --}}
        @if ($fletes->hasPages())
            <div
                class="border-t border-zinc-200 px-4 py-4
                       dark:border-zinc-800"
            >
                {{ $fletes->links() }}
            </div>
        @endif
    </div>


    {{-- ============================================================
        MODAL DE EDICIÓN
    ============================================================ --}}
    @if ($modalEditar)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center
                   overflow-y-auto px-4 py-6"
        >
            {{-- Fondo --}}
            <div
                class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                wire:click="cerrarModal"
            ></div>

            {{-- Contenido --}}
            <div
                class="relative z-10 w-full max-w-3xl rounded-2xl
                       border border-zinc-200 bg-white p-6 shadow-2xl
                       dark:border-zinc-800 dark:bg-zinc-950"
            >
                {{-- Encabezado modal --}}
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2
                            class="text-lg font-semibold text-zinc-900
                                   dark:text-white"
                        >
                            Editar flete
                        </h2>

                        <p
                            class="mt-1 text-sm text-zinc-500
                                   dark:text-zinc-400"
                        >
                            Actualiza los valores del flete seleccionado.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cerrarModal"
                        class="flex h-9 w-9 items-center justify-center
                               rounded-lg text-zinc-500 hover:bg-zinc-100
                               hover:text-zinc-800
                               dark:hover:bg-zinc-900
                               dark:hover:text-zinc-200"
                        aria-label="Cerrar"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18 18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>


                {{-- Errores --}}
                @if ($errors->any())
                    <div
                        class="mt-4 rounded-lg border border-rose-200
                               bg-rose-50 p-3 text-sm text-rose-800
                               dark:border-rose-900/40
                               dark:bg-rose-900/20
                               dark:text-rose-200"
                    >
                        <p class="mb-2 font-medium">
                            Revisa la siguiente información:
                        </p>

                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                {{-- Formulario --}}
                <form
                    wire:submit.prevent="actualizarFlete"
                    class="mt-6 space-y-5"
                >
                    <input
                        type="hidden"
                        wire:model="fleteId"
                    >

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        {{-- Departamento --}}
                        <div>
                            <label
                                for="depto"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Departamento
                            </label>

                            <input
                                id="depto"
                                type="text"
                                wire:model.defer="depto"
                                readonly
                                class="mt-1 w-full cursor-not-allowed
                                       rounded-lg border border-zinc-200
                                       bg-zinc-100 px-3 py-2 text-sm
                                       text-zinc-900
                                       dark:border-zinc-800
                                       dark:bg-zinc-900 dark:text-white"
                            >
                        </div>

                        {{-- Código departamento --}}
                        <div>
                            <label
                                for="cod_depto"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Código departamento
                            </label>

                            <input
                                id="cod_depto"
                                type="text"
                                wire:model.defer="cod_depto"
                                readonly
                                class="mt-1 w-full cursor-not-allowed
                                       rounded-lg border border-zinc-200
                                       bg-zinc-100 px-3 py-2 text-sm
                                       text-zinc-900
                                       dark:border-zinc-800
                                       dark:bg-zinc-900 dark:text-white"
                            >
                        </div>

                        {{-- Ciudad --}}
                        <div>
                            <label
                                for="ciudad"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Ciudad
                            </label>

                            <input
                                id="ciudad"
                                type="text"
                                wire:model.defer="ciudad"
                                readonly
                                class="mt-1 w-full cursor-not-allowed
                                       rounded-lg border border-zinc-200
                                       bg-zinc-100 px-3 py-2 text-sm
                                       text-zinc-900
                                       dark:border-zinc-800
                                       dark:bg-zinc-900 dark:text-white"
                            >
                        </div>

                        {{-- Código ciudad --}}
                        <div>
                            <label
                                for="cod_ciudad"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Código ciudad
                            </label>

                            <input
                                id="cod_ciudad"
                                type="text"
                                wire:model.defer="cod_ciudad"
                                readonly
                                class="mt-1 w-full cursor-not-allowed
                                       rounded-lg border border-zinc-200
                                       bg-zinc-100 px-3 py-2 text-sm
                                       text-zinc-900
                                       dark:border-zinc-800
                                       dark:bg-zinc-900 dark:text-white"
                            >
                        </div>

                        {{-- Menor --}}
                        <div>
                            <label
                                for="menor"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Porcentaje menor
                            </label>

                            <input
                                id="menor"
                                type="number"
                                step="0.01"
                                wire:model.defer="menor"
                                class="mt-1 w-full rounded-lg border
                                       border-zinc-200 bg-white px-3 py-2
                                       text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400
                                       focus:ring-2 focus:ring-zinc-200
                                       dark:border-zinc-800
                                       dark:bg-zinc-950 dark:text-white
                                       dark:focus:border-zinc-600
                                       dark:focus:ring-zinc-800"
                            >

                            @error('menor')
                                <p class="mt-1 text-xs text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Mayor --}}
                        <div>
                            <label
                                for="mayor"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Porcentaje mayor
                            </label>

                            <input
                                id="mayor"
                                type="number"
                                step="0.01"
                                wire:model.defer="mayor"
                                class="mt-1 w-full rounded-lg border
                                       border-zinc-200 bg-white px-3 py-2
                                       text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400
                                       focus:ring-2 focus:ring-zinc-200
                                       dark:border-zinc-800
                                       dark:bg-zinc-950 dark:text-white
                                       dark:focus:border-zinc-600
                                       dark:focus:ring-zinc-800"
                            >

                            @error('mayor')
                                <p class="mt-1 text-xs text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Mínimo --}}
                        <div>
                            <label
                                for="minimo"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Valor mínimo
                            </label>

                            <input
                                id="minimo"
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model.defer="minimo"
                                class="mt-1 w-full rounded-lg border
                                       border-zinc-200 bg-white px-3 py-2
                                       text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400
                                       focus:ring-2 focus:ring-zinc-200
                                       dark:border-zinc-800
                                       dark:bg-zinc-950 dark:text-white
                                       dark:focus:border-zinc-600
                                       dark:focus:ring-zinc-800"
                            >

                            @error('minimo')
                                <p class="mt-1 text-xs text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Entrega --}}
                        <div>
                            <label
                                for="entrega"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Días de entrega
                            </label>

                            <input
                                id="entrega"
                                type="number"
                                min="0"
                                wire:model.defer="entrega"
                                class="mt-1 w-full rounded-lg border
                                       border-zinc-200 bg-white px-3 py-2
                                       text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400
                                       focus:ring-2 focus:ring-zinc-200
                                       dark:border-zinc-800
                                       dark:bg-zinc-950 dark:text-white
                                       dark:focus:border-zinc-600
                                       dark:focus:ring-zinc-800"
                            >

                            @error('entrega')
                                <p class="mt-1 text-xs text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Monto --}}
                        <div>
                            <label
                                for="monto"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Monto
                            </label>

                            <input
                                id="monto"
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model.defer="monto"
                                class="mt-1 w-full rounded-lg border
                                       border-zinc-200 bg-white px-3 py-2
                                       text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400
                                       focus:ring-2 focus:ring-zinc-200
                                       dark:border-zinc-800
                                       dark:bg-zinc-950 dark:text-white
                                       dark:focus:border-zinc-600
                                       dark:focus:ring-zinc-800"
                            >

                            @error('monto')
                                <p class="mt-1 text-xs text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Monto mínimo --}}
                        <div>
                            <label
                                for="monto_minimo"
                                class="text-xs font-medium text-zinc-600
                                       dark:text-zinc-300"
                            >
                                Monto mínimo
                            </label>

                            <input
                                id="monto_minimo"
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model.defer="monto_minimo"
                                class="mt-1 w-full rounded-lg border
                                       border-zinc-200 bg-white px-3 py-2
                                       text-sm text-zinc-900 outline-none
                                       focus:border-zinc-400
                                       focus:ring-2 focus:ring-zinc-200
                                       dark:border-zinc-800
                                       dark:bg-zinc-950 dark:text-white
                                       dark:focus:border-zinc-600
                                       dark:focus:ring-zinc-800"
                            >

                            @error('monto_minimo')
                                <p class="mt-1 text-xs text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>


                    {{-- Botones modal --}}
                    <div
                        class="flex flex-col-reverse gap-2 border-t
                               border-zinc-200 pt-5
                               dark:border-zinc-800 sm:flex-row
                               sm:justify-end"
                    >
                        <button
                            type="button"
                            wire:click="cerrarModal"
                            class="rounded-lg border border-zinc-200
                                   px-4 py-2 text-sm font-medium
                                   text-zinc-700 hover:bg-zinc-50
                                   dark:border-zinc-800
                                   dark:text-zinc-200
                                   dark:hover:bg-zinc-900"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="actualizarFlete"
                            class="inline-flex items-center justify-center
                                   gap-2 rounded-lg bg-zinc-900 px-4 py-2
                                   text-sm font-medium text-white
                                   hover:bg-zinc-800
                                   disabled:cursor-not-allowed
                                   disabled:opacity-60
                                   dark:bg-white dark:text-zinc-900
                                   dark:hover:bg-zinc-200"
                        >
                            <svg
                                wire:loading
                                wire:target="actualizarFlete"
                                class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>

                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 0 1 8-8v4
                                       a4 4 0 0 0-4 4H4Z"
                                ></path>
                            </svg>

                            <span
                                wire:loading.remove
                                wire:target="actualizarFlete"
                            >
                                Guardar cambios
                            </span>

                            <span
                                wire:loading
                                wire:target="actualizarFlete"
                            >
                                Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>