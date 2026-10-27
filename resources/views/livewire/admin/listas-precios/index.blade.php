<div class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
            Listas de precios
        </h1>

        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Genere la lista con productos disponibles y resumen de pedido.
        </p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4
                    text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.listas-precios.exportar') }}"
        class="max-w-2xl space-y-5 rounded-xl border border-zinc-200
               bg-white p-6 shadow-sm dark:border-zinc-800
               dark:bg-zinc-950"
    >
        @csrf

        <div>
            <label
                for="lista"
                class="mb-2 block text-sm font-medium
                       text-zinc-700 dark:text-zinc-200"
            >
                Lista de precios
            </label>

            <select
                id="lista"
                name="lista"
                required
                class="w-full rounded-lg border border-zinc-300
                       bg-white px-3 py-2 text-sm text-zinc-900
                       dark:border-zinc-700 dark:bg-zinc-900
                       dark:text-white"
            >
                <option value="001" @selected(old('lista', '001') === '001')>
                    001
                </option>

                <option value="202" @selected(old('lista') === '202')>
                    202
                </option>
            </select>
        </div>

        <label class="flex items-start gap-3">
            <input
                type="checkbox"
                name="imagenes"
                value="1"
                checked
                class="mt-1 rounded border-zinc-300"
            >

            <span>
                <span class="block text-sm font-medium
                             text-zinc-900 dark:text-white">
                    Incluir imágenes
                </span>

                <span class="mt-1 block text-xs
                             text-zinc-500 dark:text-zinc-400">
                    Solo para llantas y neumáticos.
                </span>
            </span>
        </label>

        <div class="rounded-lg bg-zinc-50 p-4 text-sm
                    text-zinc-600 dark:bg-zinc-900
                    dark:text-zinc-300">
            Una pestaña por categoría, ordenada por marca y referencia.
            Incluye la hoja RESUMEN PEDIDO.
            Los descuentos y el flete se calculan al importar en la app.
        </div>

        <button
            type="submit"
            class="rounded-lg bg-zinc-900 px-5 py-2.5
                   text-sm font-medium text-white
                   dark:bg-white dark:text-zinc-900"
        >
            Generar Excel
        </button>
    </form>


    @if (session('success'))
    <div class="rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
@endif

<div
    @if ($exportaciones->contains(
        fn ($fila) => in_array($fila->estado, ['pendiente', 'procesando'])
    ))
        wire:poll.5s
    @endif
    class="space-y-3"
>
    <h2 class="font-semibold text-zinc-900 dark:text-white">
        Mis últimas exportaciones
    </h2>

    @forelse ($exportaciones as $exportacion)
        <div
            wire:key="exportacion-{{ $exportacion->id }}"
            class="flex flex-wrap items-center justify-between gap-3
                   rounded-xl border border-zinc-200 p-4
                   dark:border-zinc-800"
        >
            <div>
                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                    Lista {{ $exportacion->lista }}
                    · {{ $exportacion->imagenes ? 'Con imágenes' : 'Sin imágenes' }}
                </p>

                <p class="text-xs text-zinc-500">
                    {{ $exportacion->created_at }}
                </p>

                @if ($exportacion->estado === 'pendiente')
                    <p class="mt-1 text-sm text-amber-600">
                        En cola…
                    </p>
                @elseif ($exportacion->estado === 'procesando')
                    <p class="mt-1 text-sm text-blue-600">
                        Generando archivo…
                    </p>
                @elseif ($exportacion->estado === 'error')
                    <p class="mt-1 text-sm text-rose-600">
                        {{ $exportacion->mensaje }}
                    </p>
                @else
                    <p class="mt-1 text-sm text-emerald-600">
                        Listo para descargar
                    </p>
                @endif
            </div>

            @if ($exportacion->estado === 'listo')
                <a
                    href="{{ route(
                        'admin.listas-precios.descargar',
                        $exportacion->id
                    ) }}"
                    class="rounded-lg bg-zinc-900 px-4 py-2
                           text-sm font-medium text-white
                           dark:bg-white dark:text-zinc-900"
                >
                    Descargar Excel
                </a>
            @endif
        </div>
    @empty
        <p class="text-sm text-zinc-500">
            Todavía no ha generado listas.
        </p>
    @endforelse
</div>


{{-- ============================================================
     ADMINISTRACIÓN DE SUBCATEGORÍAS Y DESCUENTOS
============================================================ --}}

<section
    class="space-y-5 rounded-xl border border-zinc-200 bg-white p-6
           shadow-sm dark:border-zinc-800 dark:bg-zinc-950"
>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                Subcategorías y descuentos
            </h2>

            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Agrupe marcas y configure los descuentos que se utilizarán
                en las listas de precios.
            </p>
        </div>

        @if ($subcategoriaId)
            <button
                type="button"
                wire:click="iniciarFormulario"
                class="rounded-lg border border-zinc-300 px-4 py-2
                       text-sm font-medium text-zinc-700 hover:bg-zinc-50
                       dark:border-zinc-700 dark:text-zinc-200
                       dark:hover:bg-zinc-900"
            >
                Nueva subcategoría
            </button>
        @endif
    </div>

    @if (session('subcategoria_success'))
        <div
            class="rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800"
        >
            {{ session('subcategoria_success') }}
        </div>
    @endif

    @if (session('subcategoria_error'))
        <div class="rounded-lg bg-rose-50 p-4 text-sm text-rose-700">
            {{ session('subcategoria_error') }}
        </div>
    @endif

    <form
        wire:submit.prevent="guardarSubcategoria"
        class="space-y-5 rounded-xl border border-zinc-200 p-5
               dark:border-zinc-800"
    >
        <h3 class="font-semibold text-zinc-900 dark:text-white">
            {{ $subcategoriaId ? 'Editar subcategoría' : 'Nueva subcategoría' }}
        </h3>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label
                    for="subcategoria-nombre"
                    class="mb-2 block text-sm font-medium
                           text-zinc-700 dark:text-zinc-200"
                >
                    Nombre
                </label>

                <input
                    id="subcategoria-nombre"
                    type="text"
                    wire:model="nombre"
                    maxlength="150"
                    placeholder="Ejemplo: ILUMINACIÓN"
                    class="w-full rounded-lg border border-zinc-300
                           bg-white px-3 py-2 text-sm text-zinc-900
                           dark:border-zinc-700 dark:bg-zinc-900
                           dark:text-white"
                >

                @error('nombre')
                    <p class="mt-1 text-sm text-rose-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label
                    for="subcategoria-tipo"
                    class="mb-2 block text-sm font-medium
                           text-zinc-700 dark:text-zinc-200"
                >
                    Calcular descuento por
                </label>

                <select
                    id="subcategoria-tipo"
                    wire:model="tipoCalculo"
                    class="w-full rounded-lg border border-zinc-300
                           bg-white px-3 py-2 text-sm text-zinc-900
                           dark:border-zinc-700 dark:bg-zinc-900
                           dark:text-white"
                >
                    <option value="subtotal">
                        Subtotal antes de IVA
                    </option>

                    <option value="unidades">
                        Total de unidades
                    </option>
                </select>

                @error('tipoCalculo')
                    <p class="mt-1 text-sm text-rose-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label
                    for="subcategoria-orden"
                    class="mb-2 block text-sm font-medium
                           text-zinc-700 dark:text-zinc-200"
                >
                    Orden de la hoja
                </label>

                <input
                    id="subcategoria-orden"
                    type="number"
                    min="0"
                    wire:model="orden"
                    class="w-full rounded-lg border border-zinc-300
                           bg-white px-3 py-2 text-sm text-zinc-900
                           dark:border-zinc-700 dark:bg-zinc-900
                           dark:text-white"
                >

                @error('orden')
                    <p class="mt-1 text-sm text-rose-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex items-center pt-7">
                <label class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        wire:model="activa"
                        class="rounded border-zinc-300"
                    >

                    <span class="text-sm text-zinc-700 dark:text-zinc-200">
                        Subcategoría activa
                    </span>
                </label>
            </div>
        </div>

        <div>
            <label
                for="subcategoria-marcas"
                class="mb-2 block text-sm font-medium
                       text-zinc-700 dark:text-zinc-200"
            >
                Marcas
            </label>

            <textarea
                id="subcategoria-marcas"
                wire:model="marcasTexto"
                rows="4"
                placeholder="NARVA&#10;RINOVA LIGHTING"
                class="w-full rounded-lg border border-zinc-300
                       bg-white px-3 py-2 text-sm text-zinc-900
                       dark:border-zinc-700 dark:bg-zinc-900
                       dark:text-white"
            ></textarea>

            <p class="mt-1 text-xs text-zinc-500">
                Escriba una marca por línea, exactamente como aparece
                en el campo marca_excel de los productos.
            </p>

            @error('marcasTexto')
                <p class="mt-1 text-sm text-rose-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h4 class="font-medium text-zinc-900 dark:text-white">
                        Rangos de descuento
                    </h4>

                    <p class="mt-1 text-xs text-zinc-500">
                        Los valores inicial y final están incluidos.
                        Deje “Hasta” vacío para indicar sin límite.
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="agregarRango"
                    class="rounded-lg border border-zinc-300 px-3 py-2
                           text-sm text-zinc-700 hover:bg-zinc-50
                           dark:border-zinc-700 dark:text-zinc-200
                           dark:hover:bg-zinc-900"
                >
                    + Agregar rango
                </button>
            </div>

            @error('rangos')
                <p class="text-sm text-rose-600">
                    {{ $message }}
                </p>
            @enderror

            @foreach ($rangos as $indice => $rango)
                <div
                    wire:key="rango-{{ $indice }}"
                    class="grid gap-3 rounded-lg bg-zinc-50 p-3
                           dark:bg-zinc-900 md:grid-cols-4"
                >
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium
                                   text-zinc-600 dark:text-zinc-300"
                        >
                            Desde
                        </label>

                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            wire:model="rangos.{{ $indice }}.desde"
                            class="w-full rounded-lg border border-zinc-300
                                   bg-white px-3 py-2 text-sm
                                   dark:border-zinc-700 dark:bg-zinc-950
                                   dark:text-white"
                        >

                        @error("rangos.$indice.desde")
                            <p class="mt-1 text-xs text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-medium
                                   text-zinc-600 dark:text-zinc-300"
                        >
                            Hasta
                        </label>

                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            wire:model="rangos.{{ $indice }}.hasta"
                            placeholder="Sin límite"
                            class="w-full rounded-lg border border-zinc-300
                                   bg-white px-3 py-2 text-sm
                                   dark:border-zinc-700 dark:bg-zinc-950
                                   dark:text-white"
                        >

                        @error("rangos.$indice.hasta")
                            <p class="mt-1 text-xs text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-medium
                                   text-zinc-600 dark:text-zinc-300"
                        >
                            Descuento %
                        </label>

                        <input
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            wire:model="rangos.{{ $indice }}.porcentaje"
                            class="w-full rounded-lg border border-zinc-300
                                   bg-white px-3 py-2 text-sm
                                   dark:border-zinc-700 dark:bg-zinc-950
                                   dark:text-white"
                        >

                        @error("rangos.$indice.porcentaje")
                            <p class="mt-1 text-xs text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="flex items-end">
                        <button
                            type="button"
                            wire:click="eliminarRango({{ $indice }})"
                            class="w-full rounded-lg border border-rose-200
                                   px-3 py-2 text-sm text-rose-600
                                   hover:bg-rose-50"
                        >
                            Quitar
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-3">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="guardarSubcategoria"
                class="rounded-lg bg-zinc-900 px-5 py-2.5
                       text-sm font-medium text-white
                       disabled:opacity-50
                       dark:bg-white dark:text-zinc-900"
            >
                <span wire:loading.remove wire:target="guardarSubcategoria">
                    {{ $subcategoriaId ? 'Guardar cambios' : 'Crear subcategoría' }}
                </span>

                <span wire:loading wire:target="guardarSubcategoria">
                    Guardando...
                </span>
            </button>

            @if ($subcategoriaId)
                <button
                    type="button"
                    wire:click="iniciarFormulario"
                    class="rounded-lg border border-zinc-300 px-5 py-2.5
                           text-sm text-zinc-700
                           dark:border-zinc-700 dark:text-zinc-200"
                >
                    Cancelar edición
                </button>
            @endif
        </div>
    </form>

    <div class="space-y-3">
        <h3 class="font-semibold text-zinc-900 dark:text-white">
            Subcategorías configuradas
        </h3>

        @forelse ($subcategorias as $subcategoria)
            <div
                wire:key="subcategoria-{{ $subcategoria->id }}"
                class="rounded-xl border border-zinc-200 p-4
                       dark:border-zinc-800"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="font-semibold text-zinc-900 dark:text-white">
                                {{ $subcategoria->nombre }}
                            </h4>

                            @if ($subcategoria->activa)
                                <span
                                    class="rounded-full bg-emerald-100 px-2 py-1
                                           text-xs text-emerald-700"
                                >
                                    Activa
                                </span>
                            @else
                                <span
                                    class="rounded-full bg-zinc-100 px-2 py-1
                                           text-xs text-zinc-600"
                                >
                                    Inactiva
                                </span>
                            @endif
                        </div>

                        <p class="mt-1 text-sm text-zinc-500">
                            Cálculo:
                            {{ $subcategoria->tipo_calculo === 'unidades'
                                ? 'por unidades'
                                : 'por subtotal antes de IVA' }}
                            · Orden: {{ $subcategoria->orden }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="editarSubcategoria({{ $subcategoria->id }})"
                        class="rounded-lg border border-zinc-300 px-3 py-2
                               text-sm text-zinc-700 hover:bg-zinc-50
                               dark:border-zinc-700 dark:text-zinc-200
                               dark:hover:bg-zinc-900"
                    >
                        Editar
                    </button>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-medium uppercase text-zinc-500">
                        Marcas
                    </p>

                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (
                            $marcasPorSubcategoria->get($subcategoria->id, collect())
                            as $marca
                        )
                            <span
                                class="rounded-full bg-zinc-100 px-3 py-1
                                       text-xs text-zinc-700
                                       dark:bg-zinc-800 dark:text-zinc-200"
                            >
                                {{ $marca->marca_excel }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-zinc-500">
                            <tr>
                                <th class="py-2 pr-4">Desde</th>
                                <th class="py-2 pr-4">Hasta</th>
                                <th class="py-2 pr-4">Descuento</th>
                            </tr>
                        </thead>

                        <tbody class="text-zinc-700 dark:text-zinc-200">
                            @foreach (
                                $rangosPorSubcategoria->get($subcategoria->id, collect())
                                as $rango
                            )
                                <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                    <td class="py-2 pr-4">
                                        {{ number_format((float) $rango->desde, 2, ',', '.') }}
                                    </td>

                                    <td class="py-2 pr-4">
                                        {{ $rango->hasta === null
                                            ? 'Sin límite'
                                            : number_format((float) $rango->hasta, 2, ',', '.') }}
                                    </td>

                                    <td class="py-2 pr-4">
                                        {{ number_format((float) $rango->porcentaje, 2, ',', '.') }} %
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="text-sm text-zinc-500">
                Todavía no hay subcategorías configuradas.
            </p>
        @endforelse
    </div>
</section>
</div>