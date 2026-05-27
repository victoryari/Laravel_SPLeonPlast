@extends('layouts.app')
@section('title', 'Almacenes')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Gestión de Almacenes" subtitle="Administración de almacenes y ubicaciones de almacenamiento">
        <x-slot:actions>
            <a href="{{ route('almacenes.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo</span>
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

    {{-- Barra de Búsqueda y Filtros --}}
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6 flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" value="{{ $search ?? '' }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition" placeholder="Buscar por código, descripción o responsable...">
        </div>
        
        <div class="md:w-1/3">
            <select id="tipoFilter" class="w-full px-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition cursor-pointer">
                <option value="">Todos los tipos</option>
                @foreach($tipos as $key => $label)
                    <option value="{{ $key }}" {{ ($tipoFiltro ?? '') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
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
                            <th class="p-4 border-r border-slate-700 text-center">Código</th>
                            <th class="p-4 border-r border-slate-700 text-center">Descripción</th>
                            <th class="p-4 border-r border-slate-700 text-center">Tipo</th>
                            <th class="p-4 border-r border-slate-700 text-center hidden md:table-cell">Dirección</th>
                            <th class="p-4 border-r border-slate-700 text-center hidden md:table-cell">Responsable</th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                        @forelse ($almacenes as $alm)
                            <tr class="hover:bg-slate-50/50 transition duration-150">
                                <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-gray-900">{{ $alm->codigo_almacen }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-700">{{ $alm->descripcion }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    @php
                                        $badgeColors = [
                                            'MATERIA_PRIMA'      => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'PRODUCTO_TERMINADO' => 'bg-green-50 text-green-700 border-green-200',
                                            'PRODUCTO_PROCESO'   => 'bg-primary-50 text-primary border-primary/20',
                                            'INSUMOS'            => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'SUMINISTROS'        => 'bg-slate-100 text-slate-600 border-slate-200',
                                        ];
                                        $colorClass = $badgeColors[$alm->tipo_almacen] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] md:text-xs font-semibold border {{ $colorClass }}">
                                        {{ \App\Models\Almacen::TIPOS_ALMACEN[$alm->tipo_almacen] ?? $alm->tipo_almacen }}
                                    </span>
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-500 hidden md:table-cell">{{ $alm->direccion ?? '—' }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">
                                    @if($alm->responsable)
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fas fa-user-tie text-slate-400 text-[10px]"></i>
                                            {{ $alm->responsable }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 md:gap-3">
                                        <a href="{{ route('almacenes.edit', $alm->codigo_almacen) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition-all" title="Editar">
                                            <i class="fas fa-edit text-sm md:text-lg"></i>
                                        </a>
                                        <form action="{{ route('almacenes.destroy', $alm->codigo_almacen) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de anular este almacén?');">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Anular">
                                                <i class="fas fa-trash-alt text-sm md:text-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 md:py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-warehouse text-3xl md:text-4xl mb-3 text-gray-200"></i>
                                        <p class="text-sm md:text-base">No se encontraron almacenes con los criterios ingresados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($almacenes->hasPages())
                <div class="px-4 md:px-6 py-3 md:py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $almacenes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const tipoFilter = document.getElementById('tipoFilter');
        const tableContainer = document.getElementById('table-container');
        let timeout = null;

        function fetchResults(url = null) {
            if (!url) {
                url = new URL(window.location.href);
                url.searchParams.set('search', searchInput.value);
                
                if (tipoFilter.value) {
                    url.searchParams.set('tipo_almacen', tipoFilter.value);
                } else {
                    url.searchParams.delete('tipo_almacen');
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

        tipoFilter.addEventListener('change', function () {
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
