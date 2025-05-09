<x-layouts.app :title="__('Usuarios')">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-zinc-800 dark:text-white">Usuarios</h1>

            <a href="#" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                + Nuevo Usuario
            </a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-zinc-600 dark:text-zinc-300">Nombre</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-zinc-600 dark:text-zinc-300">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-zinc-600 dark:text-zinc-300">Rol</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-zinc-600 dark:text-zinc-300">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    <tr>
                        <td class="px-6 py-4 text-sm text-zinc-800 dark:text-zinc-200">Juan Pérez</td>
                        <td class="px-6 py-4 text-sm text-zinc-800 dark:text-zinc-200">juan@example.com</td>
                        <td class="px-6 py-4 text-sm text-zinc-800 dark:text-zinc-200">Administrador</td>
                        <td class="px-6 py-4 text-right">
                            <a href="#" class="text-indigo-600 hover:underline">Editar</a>
                        </td>
                    </tr>
                    <!-- Repite o haz dinámico -->
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
