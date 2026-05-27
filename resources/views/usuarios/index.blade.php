@extends('layouts.app')
@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Usuarios del Sistema" subtitle="Administración de accesos y roles">
        <x-slot:actions>
            <a href="{{ route('usuarios.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo Usuario</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    {{-- Barra de Búsqueda y Filtro --}}
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6 flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" value="{{ $search ?? '' }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition" placeholder="Buscar por usuario, email o rol...">
        </div>
        
        <div class="md:w-1/4">
            <select id="rolFilter" class="w-full px-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition cursor-pointer">
                <option value="">Todos los roles</option>
                <option value="Administrador" {{ request('rol') == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                <option value="Supervisor" {{ request('rol') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                <option value="Especialista" {{ request('rol') == 'Especialista' ? 'selected' : '' }}>Especialista</option>
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div id="table-container" class="transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                            <th class="p-4 border-r border-slate-700 text-center">Usuario</th>
                            <th class="p-4 border-r border-slate-700 text-center">Trabajador Vinculado</th>
                            <th class="p-4 border-r border-slate-700 text-center">Rol</th>
                            <th class="p-4 border-r border-slate-700 text-center hidden md:table-cell">Último Login</th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                        @forelse ($usuarios as $user)
                            <tr class="hover:bg-slate-50/50 transition duration-150">
                                <td class="px-4 md:px-6 py-3 md:py-4">
                                    <div class="font-bold text-gray-900">{{ $user->nombre_usuario }}</div>
                                    <div class="text-[10px] md:text-xs text-gray-500">{{ $user->email ?? 'Sin email' }}</div>
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-700">
                                    @if($user->trabajador)
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fas fa-user-tie text-slate-400 text-[10px]"></i>
                                            {{ $user->trabajador->nombre }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    @php
                                        $rolBadge = match($user->rol) {
                                            'Administrador' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'Supervisor'    => 'bg-primary-50 text-primary border-primary/20',
                                            default         => 'bg-slate-100 text-slate-600 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] md:text-xs font-semibold border {{ $rolBadge }}">
                                        {{ $user->rol }}
                                    </span>
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-500 text-center hidden md:table-cell">
                                    {{ $user->ultimo_login ? \Carbon\Carbon::parse($user->ultimo_login)->format('d/m/Y H:i') : 'Nunca' }}
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 md:gap-3">
                                        <a href="{{ route('usuarios.edit', $user->id_usuario) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition-all" title="Editar">
                                            <i class="fas fa-edit text-sm md:text-lg"></i>
                                        </a>
                                        @if(Auth::id() != $user->id_usuario)
                                        <form action="{{ route('usuarios.destroy', $user->id_usuario) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de desactivar este usuario?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Desactivar">
                                                <i class="fas fa-user-slash text-sm md:text-lg"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 md:py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-users text-3xl md:text-4xl mb-3 text-gray-200"></i>
                                        <p class="text-sm md:text-base">No se encontraron usuarios con los criterios ingresados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($usuarios->hasPages())
                <div class="px-4 md:px-6 py-3 md:py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $usuarios->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const rolFilter = document.getElementById('rolFilter');
        const tableContainer = document.getElementById('table-container');
        let timeout = null;

        function fetchResults(url = null) {
            if (!url) {
                url = new URL(window.location.href);
                url.searchParams.set('search', searchInput.value);
                
                if (rolFilter.value) {
                    url.searchParams.set('rol', rolFilter.value);
                } else {
                    url.searchParams.delete('rol');
                }
                
                url.searchParams.delete('page');
            }

            window.history.pushState({}, '', url);
            tableContainer.classList.add('opacity-50', 'pointer-events-none');

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContainer = doc.getElementById('table-container');
                    if (newContainer) {
                        tableContainer.innerHTML = newContainer.innerHTML;
                    }
                })
                .catch(error => console.error('Error al filtrar:', error))
                .finally(() => {
                    tableContainer.classList.remove('opacity-50', 'pointer-events-none');
                });
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => fetchResults(), 400);
        });

        rolFilter.addEventListener('change', function () {
            fetchResults();
        });

        tableContainer.addEventListener('click', function(e) {
            const aTag = e.target.closest('nav[role="navigation"] a');
            if (aTag) {
                e.preventDefault();
                fetchResults(new URL(aTag.href));
            }
        });
    });
</script>
@endsection