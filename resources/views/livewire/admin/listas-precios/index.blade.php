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
</div>