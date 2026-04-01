<div class="p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Solicitudes de crédito</h1>
            <p class="text-sm text-gray-500">Gestión y almacenamiento de solicitudes</p>
        </div>

        <div class="flex gap-2">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por razón social, NIT o nombre comercial"
                class="w-full md:w-80 rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500"
            >

            <button
                wire:click="abrirModal"
                class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
            >
                Nueva solicitud
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @error('referencias')
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            {{ $message }}
        </div>
    @enderror

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Razón social</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">NIT / C.C.</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ciudad</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($solicitudes as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ optional($item->fecha_solicitud)->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $item->razon_social }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $item->nit_cc }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $item->ciudad }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                {{ strtoupper($item->estado) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a
                                    href="{{ route('admin.solicitudes-credito.pdf.solicitud', $item->id) }}"
                                    target="_blank"
                                    class="inline-flex items-center rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700"
                                >
                                    PDF Solicitud
                                </a>

                                <a
                                    href="{{ route('admin.solicitudes-credito.pdf.tratamiento', $item->id) }}"
                                    target="_blank"
                                    class="inline-flex items-center rounded-lg bg-slate-700 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                                >
                                    PDF Tratamiento
                                </a>

                                <form method="POST" action="{{ route('admin.solicitudes-credito.firmar.solicitud', $item->id) }}">
    @csrf
    <button class="bg-green-600 text-white px-3 py-1 rounded">
        Firmar Solicitud
    </button>
</form>

<a
    href="{{ route('admin.solicitudes-credito.pdf.unificado.ver', $item->id) }}"
    target="_blank"
    class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700"
>
    Ver PDF Unificado
</a>

<a
    href="{{ route('admin.solicitudes-credito.pdf.unificado.descargar', $item->id) }}"
    class="inline-flex items-center rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white hover:bg-cyan-700"
>
    Descargar PDF
</a>
                            </div>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">
                            No hay solicitudes registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $solicitudes->links() }}
    </div>

    @if($modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[95vh] w-full max-w-6xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
                <div class="sticky top-0 flex items-center justify-between border-b bg-white px-6 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Nueva solicitud de crédito</h2>
                        <p class="text-sm text-gray-500">Completa la información principal</p>
                    </div>

                    <button wire:click="cerrarModal" class="rounded-lg px-3 py-2 text-sm text-gray-500 hover:bg-gray-100">
                        Cerrar
                    </button>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Fecha solicitud</label>
                            <input type="date" wire:model="solicitud.fecha_solicitud" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Razón social</label>
                            <input type="text" wire:model="solicitud.razon_social" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Nombre comercial</label>
                            <input type="text" wire:model="solicitud.nombre_comercial" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">NIT / C.C.</label>
                            <input type="text" wire:model="solicitud.nit_cc" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Ciudad</label>
                            <input type="text" wire:model="solicitud.ciudad" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Departamento</label>
                            <input type="text" wire:model="solicitud.depto" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Celular</label>
                            <input type="text" wire:model="solicitud.celular" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Correo electrónico</label>
                            <input type="email" wire:model="solicitud.correo_electronico" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Referencias comerciales</h3>
                            <button wire:click="agregarReferencia" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                                Agregar referencia
                            </button>
                        </div>

                        <div class="space-y-4">
                            @foreach($referencias as $index => $referencia)
                                <div class="rounded-2xl border border-gray-200 p-4">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 class="font-semibold text-gray-800">Referencia {{ $index + 1 }}</h4>
                                        <button wire:click="eliminarReferencia({{ $index }})" class="text-sm text-red-500 hover:text-red-700">
                                            Eliminar
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <input type="text" wire:model="referencias.{{ $index }}.empresa" placeholder="Empresa" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="text" wire:model="referencias.{{ $index }}.nit" placeholder="NIT" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="text" wire:model="referencias.{{ $index }}.telefono" placeholder="Teléfono" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="text" wire:model="referencias.{{ $index }}.depto" placeholder="Departamento" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="text" wire:model="referencias.{{ $index }}.ciudad" placeholder="Ciudad" class="rounded-xl border border-gray-300 px-4 py-2">
                                        <input type="number" step="0.01" wire:model="referencias.{{ $index }}.cupo_credito" placeholder="Cupo crédito" class="rounded-xl border border-gray-300 px-4 py-2">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button wire:click="cerrarModal" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button wire:click="guardar" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                            Guardar solicitud
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>