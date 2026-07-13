@extends('layouts.app')
@section('title', 'Productos')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Maestro de Productos" subtitle="Catálogo general de artículos y productos">
        <x-slot:actions>
            <a href="{{ route('productos.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6 flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400"></i>
            </div>
            <input type="text" id="searchInput" value="{{ $search ?? '' }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition" placeholder="Buscar por código o descripción...">
        </div>
        
        <div class="md:w-1/3 flex gap-2">
            <select id="tipoFilter" class="w-full px-4 py-2 md:py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition cursor-pointer">
                <option value="">Todos los tipos de producto</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->codigo }}" {{ ($tipoFiltro ?? '') == $tipo->codigo ? 'selected' : '' }}>
                        {{ $tipo->descripcion }}
                    </option>
                @endforeach
            </select>
            
            @if(!empty($search) || !empty($tipoFiltro))
                <a href="{{ route('productos.index', ['clear_filter' => 1]) }}" class="px-3 py-2 bg-red-50 text-red-600 border border-red-100 rounded-lg hover:bg-red-100 transition flex items-center justify-center" title="Limpiar filtro">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
    </div>

    <div id="table-container" class="transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                            <th class="p-4 border-r border-slate-700 text-center cursor-pointer hover:bg-slate-700 transition" 
                                onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => 'codigo', 'order' => request('sort') === 'codigo' && request('order') === 'asc' ? 'desc' : 'asc']) }}'">
                                Código 
                                @if(request('sort') === 'codigo')
                                    <i class="fas fa-sort-{{ request('order') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 text-slate-500 opacity-50"></i>
                                @endif
                            </th>
                            <th class="p-4 border-r border-slate-700 text-center cursor-pointer hover:bg-slate-700 transition"
                                onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => 'descripcion', 'order' => request('sort', 'descripcion') === 'descripcion' && request('order', 'asc') === 'asc' ? 'desc' : 'asc']) }}'">
                                Descripción
                                @if(request('sort', 'descripcion') === 'descripcion')
                                    <i class="fas fa-sort-{{ request('order', 'asc') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 text-slate-500 opacity-50"></i>
                                @endif
                            </th>
                            <th class="p-4 border-r border-slate-700 text-center cursor-pointer hover:bg-slate-700 transition"
                                onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => 'codigo_tipo_producto', 'order' => request('sort') === 'codigo_tipo_producto' && request('order') === 'asc' ? 'desc' : 'asc']) }}'">
                                Tipo
                                @if(request('sort') === 'codigo_tipo_producto')
                                    <i class="fas fa-sort-{{ request('order') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 text-slate-500 opacity-50"></i>
                                @endif
                            </th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs md:text-sm">
                        @forelse ($productos as $pro)
                            <tr class="hover:bg-slate-50/50 transition duration-150">
                                <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-slate-900">{{ $pro->codigo }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-slate-700">{{ $pro->descripcion }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <x-badge color="slate">{{ $pro->tipo ? $pro->tipo->descripcion : 'Sin Tipo' }}</x-badge>
                                </td>
                                
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 md:gap-3">
                                        <a href="{{ route('productos.edit', $pro->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition-all" title="Editar">
                                            <i class="fas fa-edit text-sm md:text-lg"></i>
                                        </a>
                                        <form action="{{ route('productos.destroy', $pro->codigo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de anular este producto?');">
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
                                <td colspan="4">
                                    <x-empty-state icon="fa-box-open" message="No se encontraron productos con los criterios ingresados." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($productos->hasPages())
                <div class="px-4 md:px-6 py-3 md:py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $productos->links() }}
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
                    url.searchParams.set('codigo_tipo_producto', tipoFilter.value);
                } else {
                    url.searchParams.delete('codigo_tipo_producto');
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