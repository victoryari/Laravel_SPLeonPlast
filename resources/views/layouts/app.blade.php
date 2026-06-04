<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Leon Plast Producción</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="font-sans leading-normal tracking-normal text-slate-800">

    <div class="flex h-screen overflow-hidden">

        <div id="sidebar" class="fixed md:relative z-50 inset-y-0 left-0 w-64 h-full bg-sidebar text-white flex flex-col shrink-0 shadow-xl -translate-x-full md:translate-x-0 transition-transform duration-200 ease-out">
            <div class="p-6 text-center border-b border-slate-700 shrink-0">
                <h2 class="text-xl font-bold tracking-wider">LEON PLAST</h2>
                <p class="text-xs text-sidebar-text mt-1">Sistema de Producción</p>
            </div>

            <nav class="flex-1 mt-4 px-4 pb-4 overflow-y-auto scrollbar-thin">
                <div class="space-y-1">
                    @php
                        $routeDash = '#';
                        if(Auth::user()->rol == 'Administrador') $routeDash = route('admin.dashboard');
                        elseif(Auth::user()->rol == 'Supervisor') $routeDash = route('supervisor.dashboard');
                        elseif(Auth::user()->rol == 'Especialista') $routeDash = route('especialista.dashboard');
                    @endphp
                    <a href="{{ $routeDash }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('*.dashboard') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} transition-all duration-150">
                        <i class="fas fa-chart-line w-6"></i>
                        <span>Dashboard</span>
                    </a>


                    @if(Auth::user()->hasAnyAccess(['procesos_produccion.index', 'formulas.index', 'productos.index', 'tipos_producto.index', 'unidades_medida.index', 'operaciones_produccion.index', 'centros_trabajo.index', 'trabajadores.index', 'proveedores.index', 'actividades.index', 'moldes.index', 'colores.index']))
                    <div>
                        <button type="button" id="btnTablasMaestras" class="w-full flex items-center justify-between p-3 text-sm font-medium rounded-lg text-sidebar-text hover:bg-sidebar-hover hover:text-white transition-all duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-database w-6"></i>
                                <span>Tablas Maestras</span>
                            </div>
                            <i id="iconTablasMaestras" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                        </button>

                        <div id="menuTablasMaestras" class="hidden flex-col mt-1 pl-10 pr-2 space-y-1">
                            @if(Auth::user()->hasAccess('procesos_produccion.index'))<a href="{{ route('procesos_produccion.index') }}" class="block p-2 text-sm {{ request()->routeIs('procesos_produccion.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Procesos de producción</a>@endif
                            @if(Auth::user()->hasAccess('formulas.index'))<a href="{{ route('formulas.index') }}" class="block p-2 text-sm {{ request()->routeIs('formulas.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Fórmulas</a>@endif
                            @if(Auth::user()->hasAccess('productos.index'))<a href="{{ route('productos.index') }}" class="block p-2 text-sm {{ request()->routeIs('productos.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Productos</a>@endif
                            @if(Auth::user()->hasAccess('tipos_producto.index'))<a href="{{ route('tipos_producto.index') }}" class="block p-2 text-sm {{ request()->routeIs('tipos_producto.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Tipo de productos</a>@endif
                            @if(Auth::user()->hasAccess('unidades_medida.index'))<a href="{{ route('unidades_medida.index') }}" class="block p-2 text-sm {{ request()->routeIs('unidades_medida.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Unidad de medida</a>@endif
                            @if(Auth::user()->hasAccess('operaciones_produccion.index'))<a href="{{ route('operaciones_produccion.index') }}" class="block p-2 text-sm {{ request()->routeIs('operaciones_produccion.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Operaciones</a>@endif
                            @if(Auth::user()->hasAccess('centros_trabajo.index'))<a href="{{ route('centros_trabajo.index') }}" class="block p-2 text-sm {{ request()->routeIs('centros_trabajo.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Centros de trabajo</a>@endif
                            @if(Auth::user()->hasAccess('trabajadores.index'))<a href="{{ route('trabajadores.index') }}" class="block p-2 text-sm {{ request()->routeIs('trabajadores.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Trabajadores</a>@endif
                            @if(Auth::user()->hasAccess('proveedores.index'))<a href="{{ route('proveedores.index') }}" class="block p-2 text-sm {{ request()->routeIs('proveedores.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Proveedores</a>@endif
                            @if(Auth::user()->hasAccess('actividades.index'))<a href="{{ route('actividades.index') }}" class="block p-2 text-sm {{ request()->routeIs('actividades.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Actividades</a>@endif
                            @if(Auth::user()->hasAccess('moldes.index'))<a href="{{ route('moldes.index') }}" class="block p-2 text-sm {{ request()->routeIs('moldes.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Moldes</a>@endif
                            @if(Auth::user()->hasAccess('colores.index'))<a href="{{ route('colores.index') }}" class="block p-2 text-sm {{ request()->routeIs('colores.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Color</a>@endif
                            @if(Auth::user()->rol == 'Administrador')<a href="{{ route('parametros.index') }}" class="block p-2 text-sm {{ request()->routeIs('parametros.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Parámetros del Sistema</a>@endif
                        </div>
                    </div>
                    @endif

                    @if(Auth::user()->hasAccess('compras.index'))
                    <a href="{{ route('guia_compras.index') }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('guia_compras.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} transition-all duration-150">
                        <i class="fas fa-truck-loading w-6"></i>
                        <span>Guías de Remisión</span>
                    </a>
                    @endif

                    @if(Auth::user()->hasAccess('compras.index'))
                    <a href="{{ route('compras.index') }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('compras.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} transition-all duration-150">
                        <i class="fas fa-shopping-cart w-6"></i>
                        <span>Compras</span>
                    </a>
                    @endif

                    @if(Auth::user()->hasAnyAccess(['inventario.index', 'inventario.recepciones', 'inventario.kardex', 'inventario.ajuste', 'inventario.extornos']))
                    <div>
                        <button type="button" id="btnInventario" class="w-full flex items-center justify-between p-3 text-sm font-medium rounded-lg text-sidebar-text hover:bg-sidebar-hover hover:text-white transition-all duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-boxes w-6"></i>
                                <span>Inventario</span>
                            </div>
                            <i id="iconInventario" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                        </button>

                        <div id="menuInventario" class="hidden flex-col mt-1 pl-10 pr-2 space-y-1">
                            @if(in_array(Auth::user()->rol, ['Administrador', 'Supervisor', 'Almacenero']))
                            <a href="{{ route('inventario.despachos.index') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.despachos*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">
                                <i class="fas fa-box-open mr-1 text-indigo-300"></i> Despachos a Producción
                            </a>
                            @endif

                            @if(Auth::user()->hasAccess('inventario.index'))<a href="{{ route('inventario.index') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.index') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Existencias</a>@endif

                            @if(Auth::user()->hasAccess('inventario.alertas_stock'))<a href="{{ route('inventario.alertas_stock') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.alertas*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150"><i class="fas fa-exclamation-triangle text-yellow-400 mr-1.5"></i>Alertas de Stock</a>@endif

                            @if(Auth::user()->hasAccess('inventario.recepciones'))<a href="{{ route('inventario.recepciones') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.recepciones') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Recepciones Pendientes</a>@endif

                            @if(Auth::user()->hasAccess('inventario.kardex'))<a href="{{ route('inventario.kardex') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.kardex') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Kardex de Movimientos</a>@endif

                            @if(Auth::user()->hasAccess('inventario.ajuste'))<a href="{{ route('inventario.ajuste.lista') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.ajuste.lista') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Lista de Ajustes</a>@endif

                            @if(Auth::user()->hasAccess('inventario.extornos'))<a href="{{ route('inventario.extornos') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.extornos') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">Extornos</a>@endif
                        </div>
                    </div>
                    @endif

                    @if(Auth::user()->hasAnyAccess(['produccion.ordenes.index']))
                    <div>
                        <button type="button" id="btnProduccion" class="w-full flex items-center justify-between p-3 text-sm font-medium rounded-lg text-sidebar-text hover:bg-sidebar-hover hover:text-white transition-all duration-150 focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-industry w-6"></i>
                                <span>Producción</span>
                            </div>
                            <i id="iconProduccion" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                        </button>

                        <div id="menuProduccion" class="hidden flex-col mt-1 pl-10 pr-2 space-y-1">
                            @if(Auth::user()->hasAccess('requerimientos_materiales.index'))
                            <a href="{{ route('requerimientos_materiales.index') }}" class="block p-2 text-sm {{ request()->routeIs('requerimientos_materiales*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">
                                <i class="fas fa-clipboard-list w-5"></i> Requerimientos
                            </a>
                            @endif
                            @if(Auth::user()->hasAccess('produccion.ordenes.index'))
                            <a href="{{ route('produccion.ordenes.index') }}" class="block p-2 text-sm {{ request()->routeIs('produccion.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">
                                Órdenes de Producción
                            </a>
                            <a href="{{ route('admin.rutas_produccion.index') }}" class="block p-2 text-sm {{ request()->routeIs('admin.rutas_produccion.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} rounded transition-all duration-150">
                                Rutas de Producción
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if(Auth::user()->hasAccess('almacenes.index'))
                    <a href="{{ route('almacenes.index') }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('almacenes.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} transition-all duration-150">
                        <i class="fas fa-warehouse w-6"></i>
                        <span>Almacén</span>
                    </a>
                    @endif

                    @if(Auth::user()->hasAccess('reportes.index'))
                    <a href="{{ route('reportes.index') }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('reportes.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} transition-all duration-150">
                        <i class="fas fa-file-invoice-dollar w-6"></i>
                        <span>Reportes</span>
                    </a>
                    @endif

                    @if(Auth::user()->hasAccess('usuarios.index'))
                    <a href="{{ route('usuarios.index') }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('usuarios.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} transition-all duration-150">
                        <i class="fas fa-users-cog w-6"></i>
                        <span>Usuarios</span>
                    </a>
                    @endif

                    @if(Auth::user()->hasAccess('roles.index'))
                    <a href="{{ route('roles.index') }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('roles.*') ? 'bg-sidebar-active text-white shadow-lg' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-white' }} transition-all duration-150">
                        <i class="fas fa-user-shield w-6"></i>
                        <span>Roles y Permisos</span>
                    </a>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t border-slate-700 shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center p-3 text-sm font-medium rounded-lg text-red-400 hover:bg-red-900/20 transition-all duration-150">
                            <i class="fas fa-sign-out-alt w-6"></i>
                            <span>Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            <div id="overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

            <header class="bg-white border-b border-slate-200 h-14 shrink-0 flex items-center justify-between px-6 z-30">
                <button id="menuBtn" class="md:hidden text-slate-600 p-2 rounded-md hover:bg-slate-100 transition-colors">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <div class="flex items-center ml-auto md:ml-0">
                    <div class="mr-4 text-right hidden sm:block">
                        <p class="text-sm font-semibold text-slate-700">{{ Auth::user()->nombre_usuario }}</p>
                        <p class="text-xs text-slate-500 uppercase tracking-wider">{{ Auth::user()->rol }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center text-white font-bold shadow-sm uppercase">
                        {{ substr(Auth::user()->nombre_usuario, 0, 1) }}
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 md:p-6 lg:p-8 overflow-y-auto scrollbar-thin">
                @yield('content')
            </main>

            <footer class="bg-white border-t border-slate-200 p-3 shrink-0 z-30 no-print">
                <p class="text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} Leon Plast — Sistema de Control de Producción.
                </p>
            </footer>
        </div>
    </div>

    <div id="toast-container" class="toast-container"></div>

    @if(session('success'))
        <x-toast type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-toast type="error" :message="session('error')" />
    @endif
    @if(session('warning'))
        <x-toast type="warning" :message="session('warning')" />
    @endif

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toast').forEach(function (el) {
                var autohide = parseInt(el.dataset.autohide) || 4000;
                var timer = setTimeout(function () {
                    el.classList.add('toast-hiding');
                    setTimeout(function () { el.remove(); }, 300);
                }, autohide);
                el.querySelector('.toast-close')?.addEventListener('click', function () {
                    clearTimeout(timer);
                    el.classList.add('toast-hiding');
                    setTimeout(function () { el.remove(); }, 300);
                });
            });
        });

        window.toast = function (message, type) {
            type = type || 'success';
            var icons = {
                success: 'fa-check-circle', error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle', info: 'fa-info-circle'
            };
            var colors = {
                success: '#059669', error: '#dc2626',
                warning: '#d97706', info: '#0284c7'
            };
            var icon = icons[type] || icons.success;
            var bg = colors[type] || colors.success;
            var toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.backgroundColor = bg;
            toast.setAttribute('data-autohide', '4000');
            toast.innerHTML = '<i class="fas ' + icon + '"></i><span>' + message + '</span><button class="toast-close">&times;</button>';
            document.getElementById('toast-container')?.appendChild(toast);
            var timer = setTimeout(function () {
                toast.classList.add('toast-hiding');
                setTimeout(function () { toast.remove(); }, 300);
            }, 4000);
            toast.querySelector('.toast-close').addEventListener('click', function () {
                clearTimeout(timer);
                toast.classList.add('toast-hiding');
                setTimeout(function () { toast.remove(); }, 300);
            });
        };

        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        menuBtn?.addEventListener('click', toggleMenu);
        overlay?.addEventListener('click', toggleMenu);

        // Tablas Maestras
        const btnTablasMaestras = document.getElementById('btnTablasMaestras');
        const menuTablasMaestras = document.getElementById('menuTablasMaestras');
        const iconTablasMaestras = document.getElementById('iconTablasMaestras');

        const currentUrl = window.location.href;

        const maestrasSlugs = [
            'unidades-medida', 'tipos-producto', 'productos',
            'procesos-produccion', 'formulas', 'operaciones-produccion',
            'centros-trabajo', 'trabajadores', 'proveedores',
            'actividades', 'moldes', 'colores', 'parametros'
        ];

        if (maestrasSlugs.some(slug => currentUrl.includes(slug))) {
            menuTablasMaestras?.classList.remove('hidden');
            menuTablasMaestras?.classList.add('flex');
            iconTablasMaestras?.classList.add('rotate-180');
        }

        btnTablasMaestras?.addEventListener('click', () => {
            menuTablasMaestras.classList.toggle('hidden');
            menuTablasMaestras.classList.toggle('flex');
            iconTablasMaestras.classList.toggle('rotate-180');
        });

        // Inventario
        const btnInventario = document.getElementById('btnInventario');
        const menuInventario = document.getElementById('menuInventario');
        const iconInventario = document.getElementById('iconInventario');

        const inventarioSlugs = ['existencias', 'recepciones', 'kardex', 'ajuste', 'extornos', 'alertas', 'despachos'];

        if (inventarioSlugs.some(slug => currentUrl.includes(slug))) {
            menuInventario?.classList.remove('hidden');
            menuInventario?.classList.add('flex');
            iconInventario?.classList.add('rotate-180');
        }

        if (btnInventario) {
            btnInventario.addEventListener('click', () => {
                menuInventario.classList.toggle('hidden');
                menuInventario.classList.toggle('flex');
                iconInventario.classList.toggle('rotate-180');
            });
        }

        // Produccion
        const btnProduccion = document.getElementById('btnProduccion');
        const menuProduccion = document.getElementById('menuProduccion');
        const iconProduccion = document.getElementById('iconProduccion');

        const produccionSlugs = ['ordenes', 'requerimientos_materiales'];

        if (produccionSlugs.some(slug => currentUrl.includes(slug))) {
            menuProduccion?.classList.remove('hidden');
            menuProduccion?.classList.add('flex');
            iconProduccion?.classList.add('rotate-180');
        }

        if (btnProduccion) {
            btnProduccion.addEventListener('click', () => {
                menuProduccion.classList.toggle('hidden');
                menuProduccion.classList.toggle('flex');
                iconProduccion.classList.toggle('rotate-180');
            });
        }
    </script>
</body>
</html>
