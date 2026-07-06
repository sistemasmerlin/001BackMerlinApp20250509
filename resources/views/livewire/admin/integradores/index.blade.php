<div class="p-6 space-y-6">

    @if (session()->has('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Integradores API</h2>
                <p class="text-sm text-slate-500">
                    Administra los usuarios integradores, prefijos, parámetros comerciales y reglas de flete.
                </p>
            </div>

            <button
                wire:click="crear"
                type="button"
                class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-white font-semibold hover:bg-red-700 transition"
            >
                Nuevo integrador
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Buscar integrador
                </label>
                <input
                    type="text"
                    wire:model.live="q"
                    placeholder="Buscar por NIT, usuario, correo, nombre comercial o prefijo..."
                    class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                >
            </div>

            <div class="flex items-end">
                <div class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-3">
                    <p class="text-xs text-slate-500">Total registros</p>
                    <p class="text-xl font-bold text-slate-800">{{ $integradores->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Listado de integradores</h2>
                <p class="text-sm text-slate-500">
                    Usuarios autorizados para consumir servicios de integración.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Usuario</th>
                        <th class="px-4 py-3 text-left">NIT</th>
                        <th class="px-4 py-3 text-left">Nombre comercial</th>
                        <th class="px-4 py-3 text-left">Prefijo</th>
                        <th class="px-4 py-3 text-left">Lista</th>
                        <th class="px-4 py-3 text-left">Sucursal</th>
                        <th class="px-4 py-3 text-left">Cond. pago</th>
                        <th class="px-4 py-3 text-center">Flete</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($integradores as $integrador)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $integrador->user->name ?? 'Sin usuario' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $integrador->user->email ?? '' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                {{ $integrador->nit }}
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                {{ $integrador->nombre_comercial ?: '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ $integrador->prefijo_pedido }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                {{ $integrador->lista_precio }}
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                {{ $integrador->id_sucursal }}
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                {{ $integrador->condicion_pago }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if($integrador->calcula_flete)
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Sí
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        No
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if($integrador->activo)
                                    <span class="inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <button
                                        type="button"
                                        wire:click="editar({{ $integrador->id }})"
                                        class="rounded-xl bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600 transition"
                                    >
                                        Editar
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="cambiarEstado({{ $integrador->id }})"
                                        class="rounded-xl bg-slate-600 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 transition"
                                    >
                                        {{ $integrador->activo ? 'Inactivar' : 'Activar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-500">
                                No hay integradores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200">
            {{ $integradores->links() }}
        </div>
    </div>

    @if($modal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        >
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-6xl max-h-[90vh] overflow-y-auto">
                <form wire:submit.prevent="guardar">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-800">
                                {{ $integradorId ? 'Editar integrador' : 'Nuevo integrador' }}
                            </h2>
                            <p class="text-sm text-slate-500">
                                Configura el usuario integrador y sus parámetros comerciales.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="$set('modal', false)"
                            class="rounded-xl bg-slate-100 px-3 py-2 text-slate-600 hover:bg-slate-200 transition"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">
                                Datos del integrador
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Usuario integrador
                                    </label>
                                    <select
                                        wire:model.defer="user_id"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                        <option value="">Seleccione...</option>
                                        @foreach($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}">
                                                {{ $usuario->name }} - {{ $usuario->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        NIT
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="nit"
                                        placeholder="Ej: 900447351"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                    @error('nit')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Prefijo pedido
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="prefijo_pedido"
                                        placeholder="Ej: PVL"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500 uppercase"
                                    >
                                    @error('prefijo_pedido')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Nombre comercial
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="nombre_comercial"
                                        placeholder="Ej: Virtual Llantas"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                    @error('nombre_comercial')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-5">
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">
                                Parámetros comerciales
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Lista precio
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="lista_precio"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                    @error('lista_precio')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Sucursal
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="id_sucursal"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                    @error('id_sucursal')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Punto envío
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="punto_envio"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                    @error('punto_envio')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Condición pago
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="condicion_pago"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                    @error('condicion_pago')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-5">
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">
                                Datos del asesor
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Código asesor
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="codigo_asesor"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                    @error('codigo_asesor')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Nombre asesor
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="nombre_asesor"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                    @error('nombre_asesor')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Correo notificación
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.defer="correo_notificacion"
                                        placeholder="correo1@dominio.com"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                    @error('correo_notificacion')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-5">
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">
                                Reglas de negocio
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Estado
                                    </label>
                                    <select
                                        wire:model.defer="activo"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                    @error('activo')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Calcula flete
                                    </label>
                                    <select
                                        wire:model.defer="calcula_flete"
                                        class="w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500"
                                    >
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('calcula_flete')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-200 bg-slate-50">
                        <button
                            type="button"
                            wire:click="$set('modal', false)"
                            class="rounded-xl bg-white border border-slate-300 px-5 py-2.5 text-slate-700 font-semibold hover:bg-slate-100 transition"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="guardar"
                            class="rounded-xl bg-red-600 px-5 py-2.5 text-white font-semibold hover:bg-red-700 transition disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="guardar">
                                Guardar integrador
                            </span>

                            <span wire:loading wire:target="guardar">
                                Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>