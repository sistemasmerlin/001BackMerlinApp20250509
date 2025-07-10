<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @livewireStyles

        <!-- DataTables CSS -->
        <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/responsive/2.2.6/css/responsive.dataTables.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">

    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-200 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden text-base" icon="x-mark" />

                <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                    <x-app-logo />
                </a>

                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Panel de Control')" class="text-zinc-700 text-base">
                        <flux:navlist.item  icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate class="!text-base">
                         {{ __('Inicio') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
                
            @can('Usuarios')
                <div x-data="{ open: false }" class="px-4">
                    <button
                        @click="open = !open"
                        class="flex w-full items-center gap-2 py-2 text-base font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-200 dark:hover:text-white"
                    >
                    <flux:icon name="users" class="h-4 w-4" />
                        <span>{{ __('Usuarios') }}</span>
                        <flux:icon x-show="!open" name="chevron-down" class="ms-auto h-4 w-4" />
                        <flux:icon x-show="open" name="chevron-up" class="ms-auto h-4 w-4" />
                    </button>

                    <div x-show="open" class="ms-6 mt-1 space-y-1">
                        <flux:navlist.item :href="route('usuarios.index')" :current="request()->routeIs('usuarios.*')" wire:navigate>
                            {{ __('Lista de Usuarios') }}
                        </flux:navlist.item>
                        <flux:navlist.item :href="route('roles.index')" :current="request()->routeIs('roles.*')" wire:navigate>
                            {{ __('Roles') }}
                        </flux:navlist.item>
                        <flux:navlist.item :href="route('permisos.index')" :current="request()->routeIs('permisos.*')" wire:navigate>
                            {{ __('Permisos') }}
                        </flux:navlist.item>
                        <flux:navlist.item :href="route('relacion.asesores.index')" :current="request()->routeIs('relacion.asesores.*')" wire:navigate>
                            {{ __('Relacion Asesores') }}
                        </flux:navlist.item>
                    </div>
                </div>
            @endcan
            @can('Comercial')
                <div x-data="{ open: false }" class="px-4">
                    <button
                        @click="open = !open"
                        class="flex w-full items-center gap-2 py-2 text-base font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-200 dark:hover:text-white"
                    >
                    <flux:icon name="briefcase" class="h-4 w-4" />
                        <span>{{ __('Comercial') }}</span>
                        <flux:icon x-show="!open" name="chevron-down" class="ms-auto h-4 w-4" />
                        <flux:icon x-show="open" name="chevron-up" class="ms-auto h-4 w-4" />
                    </button>
                    @can('Administrar promociones')
                    <div x-show="open" class="ms-6 mt-1 space-y-1">
                        <flux:navlist.item :href="route('promociones.index')" :current="request()->routeIs('promociones.*')" wire:navigate>
                            {{ __('Lista de Promociones') }}
                        </flux:navlist.item>
                    </div>
                    @endcan
                    @can('Administrar pedidos')
                    <div x-show="open" class="ms-6 mt-1 space-y-1">
                        <flux:navlist.item :href="route('pedidos.index')" :current="request()->routeIs('pedidos.*')" wire:navigate>
                            {{ __('Listar Pedidos') }}
                        </flux:navlist.item>
                    </div>
                    @endcan
                    @can('Administrar backorder')
                    <div x-show="open" class="ms-6 mt-1 space-y-1">
                        <flux:navlist.item :href="route('backOrder.index')" :current="request()->routeIs('backOrder.*')" wire:navigate>
                            {{ __('Listar BackOrder') }}
                        </flux:navlist.item>
                    </div>
                    @endcan
                    @can('Administrar noticias')
                    <div x-show="open" class="ms-6 mt-1 space-y-1">
                        <flux:navlist.item :href="route('noticias.index')" :current="request()->routeIs('noticias.*')" wire:navigate>
                            {{ __('Listar Noticias') }}
                        </flux:navlist.item>
                    </div>
                    @endcan
                </div>
            @endcan
            @can('Logistica')
                <div x-data="{ open: false }" class="px-4">
                    <button
                        @click="open = !open"
                        class="flex w-full items-center gap-2 py-2 text-base font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-200 dark:hover:text-white"
                    >
                    <flux:icon name="truck" class="h-4 w-4" />
                        <span>{{ __('Fletes') }}</span>
                        <flux:icon x-show="!open" name="chevron-down" class="ms-auto h-4 w-4" />
                        <flux:icon x-show="open" name="chevron-up" class="ms-auto h-4 w-4" />
                    </button>

                    @can('Administrar fletes')
                    <div x-show="open" class="ms-6 mt-1 space-y-1">
                        <flux:navlist.item :href="route('fletes.index')" :current="request()->routeIs('fletes.*')" wire:navigate>
                            {{ __('Lista de ciudades') }}
                        </flux:navlist.item>
                    </div>
                    @endcan
                </div>
            @endcan
            @can('Cartera')
                <div x-data="{ open: false }" class="px-4">
                    <button
                        @click="open = !open"
                        class="flex w-full items-center gap-2 py-2 text-base font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-200 dark:hover:text-white"
                    >
                    <flux:icon name="wallet" class="h-4 w-4" />
                        <span>{{ __('Cartera') }}</span>
                        <flux:icon x-show="!open" name="chevron-down" class="ms-auto h-4 w-4" />
                        <flux:icon x-show="open" name="chevron-up" class="ms-auto h-4 w-4" />
                    </button>

                    @can('Intereses por mora')
                    <div x-show="open" class="ms-6 mt-1 space-y-1">
                        <flux:navlist.item :href="route('intereses.cartera.index')" :current="request()->routeIs('intereses.cartera.*')" wire:navigate>
                            {{ __('Intereses por mora') }}
                        </flux:navlist.item>
                    </div>
                    @endcan
                </div>
            @endcan
                <!-- <flux:navlist variant="outline">
                    <flux:navlist.item :href="route('terceros.index')" :current="request()->routeIs('terceros.*')" wire:navigate>
                                {{ __('Terceros') }}
                            </flux:navlist.item>
                </flux:navlist> -->

            <flux:spacer />

           <!--  <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist> -->

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-base font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-base">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-base leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-base font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-base">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-base leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}
        
        @fluxScripts
        <!-- Livewire scripts -->

        @livewireScripts
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.2.6/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

        <!-- Aquí irán los scripts personalizados de cada vista -->
        @stack('scripts')

    </body>
</html>
