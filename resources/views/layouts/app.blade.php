<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Leon Plast Producción</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .sidebar-transition { transition: transform 0.3s ease-in-out; }
        @media (max-width: 768px) {
            .sidebar-hidden { transform: translateX(-100%); }
        }
        /* Pequeño ajuste para que el scroll de la tabla sea más estilizado */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal text-gray-800">

    <div class="flex h-screen overflow-hidden bg-gray-100">

        <div id="sidebar" class="sidebar-transition sidebar-hidden md:transform-none fixed md:relative z-50 inset-y-0 left-0 w-64 h-full bg-slate-800 text-white flex flex-col flex-shrink-0 shadow-xl">
            <div class="p-6 text-center border-b border-slate-700 flex-shrink-0">
                <h2 class="text-xl font-bold tracking-wider">LEON PLAST</h2>
                <p class="text-xs text-slate-400 mt-1">Sistema de Producción</p>
            </div>
            
            <nav class="flex-1 mt-4 px-4 pb-4 overflow-y-auto">
                <div class="space-y-1">
                    @php
                        $routeDash = '#';
                        if(Auth::user()->rol == 'Administrador') $routeDash = route('admin.dashboard');
                        elseif(Auth::user()->rol == 'Supervisor') $routeDash = route('supervisor.dashboard');
                        elseif(Auth::user()->rol == 'Especialista') $routeDash = route('especialista.dashboard');
                    @endphp
                    <a href="{{ $routeDash }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('*.dashboard') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} transition">
                        <i class="fas fa-chart-line w-6"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="#" class="flex items-center p-3 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition">
                        <i class="fas fa-industry w-6"></i>
                        <span>Producción</span>
                    </a>

                    @if(Auth::user()->rol == 'Administrador')
                    <div>
                        <button type="button" id="btnTablasMaestras" class="w-full flex items-center justify-between p-3 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-database w-6"></i>
                                <span>Tablas Maestras</span>
                            </div>
                            <i id="iconTablasMaestras" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                        </button>
                        
                        <div id="menuTablasMaestras" class="hidden flex-col mt-1 pl-10 pr-2 space-y-1">
                            <a href="{{ route('procesos_produccion.index') }}" class="block p-2 text-sm {{ request()->routeIs('procesos_produccion.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Procesos de producción</a>
                            <a href="{{ route('formulas.index') }}" class="block p-2 text-sm {{ request()->routeIs('formulas.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Fórmulas</a>
                            <a href="{{ route('productos.index') }}" class="block p-2 text-sm {{ request()->routeIs('productos.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Productos</a>
                            <a href="{{ route('tipos_producto.index') }}" class="block p-2 text-sm {{ request()->routeIs('tipos_producto.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Tipo de productos</a>
                            <a href="{{ route('unidades_medida.index') }}" class="block p-2 text-sm {{ request()->routeIs('unidades_medida.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Unidad de medida</a>
                            <a href="{{ route('operaciones_produccion.index') }}" class="block p-2 text-sm {{ request()->routeIs('operaciones_produccion.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Operaciones</a>
                            <a href="{{ route('centros_trabajo.index') }}" class="block p-2 text-sm {{ request()->routeIs('centros_trabajo.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Centros de trabajo</a>
                            <a href="{{ route('trabajadores.index') }}" class="block p-2 text-sm {{ request()->routeIs('trabajadores.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Trabajadores</a>
                            <a href="{{ route('proveedores.index') }}" class="block p-2 text-sm {{ request()->routeIs('proveedores.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Proveedores</a>
                            <a href="{{ route('actividades.index') }}" class="block p-2 text-sm {{ request()->routeIs('actividades.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Actividades</a>
                            <a href="{{ route('moldes.index') }}" class="block p-2 text-sm {{ request()->routeIs('moldes.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Moldes</a>
                            <a href="{{ route('colores.index') }}" class="block p-2 text-sm {{ request()->routeIs('colores.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Color</a>
                        </div>
                    </div>
                    @endif

                    @if(in_array(Auth::user()->rol, ['Administrador', 'Supervisor']))
                    <a href="{{ route('compras.index') }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('compras.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} transition">
                        <i class="fas fa-shopping-cart w-6"></i>
                        <span>Compras</span>
                    </a>
                    @endif

                    <div>
                        <button type="button" id="btnInventario" class="w-full flex items-center justify-between p-3 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-700 hover:text-white transition focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-boxes w-6"></i>
                                <span>Inventario</span>
                            </div>
                            <i id="iconInventario" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                        </button>
                        
                        <div id="menuInventario" class="hidden flex-col mt-1 pl-10 pr-2 space-y-1">
                            <a href="{{ route('inventario.index') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.index') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Existencias</a>
                            
                            @if(in_array(Auth::user()->rol, ['Administrador', 'Supervisor']))
                                <a href="{{ route('inventario.recepciones') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.recepciones') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Recepciones Pendientes</a>
                            @endif
                            
                            <a href="{{ route('inventario.kardex') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.kardex') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Kardex de Movimientos</a>
                            
                            @if(in_array(Auth::user()->rol, ['Administrador', 'Supervisor']))
                                <a href="{{ route('inventario.ajuste') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.ajuste') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Ajuste Manual</a>
                            @endif

                            @if(in_array(Auth::user()->rol, ['Administrador', 'Supervisor']))
                                <a href="{{ route('inventario.extornos') }}" class="block p-2 text-sm {{ request()->routeIs('inventario.extorno') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} rounded transition">Extronos</a>
                            @endif
                        

                        </div>
                    </div>

                    <a href="{{ route('almacenes.index') }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('almacenes.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} transition">
                        <i class="fas fa-warehouse w-6"></i>
                        <span>Almacén</span>
                    </a>

                    <a href="#" class="flex items-center p-3 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-700 transition">
                        <i class="fas fa-file-invoice-dollar w-6"></i>
                        <span>Reportes</span>
                    </a>

                    @if(Auth::user()->rol == 'Administrador')
                    <a href="{{ route('usuarios.index') }}" class="flex items-center p-3 text-sm font-medium rounded-lg {{ request()->routeIs('usuarios.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700' }} transition">
                        <i class="fas fa-users-cog w-6"></i>
                        <span>Usuarios</span>
                    </a>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t border-slate-700 flex-shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center p-3 text-sm font-medium rounded-lg text-red-400 hover:bg-red-900/20 transition">
                            <i class="fas fa-sign-out-alt w-6"></i>
                            <span>Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            <div id="overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

            <header class="bg-white shadow-sm h-16 flex-shrink-0 flex items-center justify-between px-6 z-30">
                <button id="menuBtn" class="md:hidden text-slate-600 p-2 rounded-md hover:bg-gray-100">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <div class="flex items-center ml-auto">
                    <div class="mr-4 text-right hidden sm:block">
                        <p class="text-sm font-semibold text-gray-700">{{ Auth::user()->nombre_usuario }}</p>
                        <p class="text-xs text-gray-500 uppercase">{{ Auth::user()->rol }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold shadow-sm uppercase">
                        {{ substr(Auth::user()->nombre_usuario, 0, 1) }}
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                @yield('content')
            </main>

            <footer class="bg-white p-4 border-t border-gray-200 flex-shrink-0 z-30">
                <p class="text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} Leon Plast - Sistema de Control de Producción.
                </p>
            </footer>
        </div>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            sidebar.classList.toggle('sidebar-hidden');
            overlay.classList.toggle('hidden');
        }

        menuBtn?.addEventListener('click', toggleMenu);
        overlay?.addEventListener('click', toggleMenu);

        // Lógica para el menú de Tablas Maestras
        const btnTablasMaestras = document.getElementById('btnTablasMaestras');
        const menuTablasMaestras = document.getElementById('menuTablasMaestras');
        const iconTablasMaestras = document.getElementById('iconTablasMaestras');

        const currentUrl = window.location.href;
        
        const maestrasSlugs = [
            'unidades-medida', 'tipos-producto', 'productos', 
            'procesos-produccion', 'formulas', 'operaciones-produccion',
            'centros-trabajo', 'trabajadores', 'proveedores', 
            'actividades', 'moldes', 'colores'
        ];

        if (maestrasSlugs.some(slug => currentUrl.includes(slug))) {
            menuTablasMaestras?.classList.remove('hidden');
            iconTablasMaestras?.classList.add('rotate-180');
        }

        btnTablasMaestras?.addEventListener('click', () => {
            menuTablasMaestras.classList.toggle('hidden');
            iconTablasMaestras.classList.toggle('rotate-180');
        });

        // Lógica para el nuevo menú de Inventario
        const btnInventario = document.getElementById('btnInventario');
        const menuInventario = document.getElementById('menuInventario');
        const iconInventario = document.getElementById('iconInventario');

        if (currentUrl.includes('/inventario')) {
            menuInventario?.classList.remove('hidden');
            iconInventario?.classList.add('rotate-180');
        }

        btnInventario?.addEventListener('click', () => {
            menuInventario.classList.toggle('hidden');
            iconInventario.classList.toggle('rotate-180');
        });
    </script>
</body>
</html>