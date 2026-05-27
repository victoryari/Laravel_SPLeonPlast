@extends('layouts.app')
@section('title', 'Productos')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Maestro de Productos</h1>
            <p class="text-xs sm:text-sm text-gray-600">Catálogo general de artículos y productos</p>
        </div>
        <a href="{{ route('productos.create') }}" class="shrink-0 flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
            <i class="fas fa-plus"></i>
            <span class="hidden sm:inline ml-2">Nuevo</span>
        </a>
    </div>

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

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6 flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" value="{{ $search ?? '' }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition" placeholder="Buscar por código o descripción...">
        </div>
        
        <div class="md:w-1/3">
            <select id="tipoFilter" class="w-full px-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition cursor-pointer">
                <option value="">Todos los tipos de producto</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->codigo }}" {{ ($tipoFiltro ?? '') == $tipo->codigo ? 'selected' : '' }}>
                        {{ $tipo->descripcion }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div id="table-container" class="transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                            <th class="p-4 border-r border-slate-700 text-center">Código</th>
                            <th class="p-4 border-r border-slate-700 text-center">Descripción</th>
                            <th class="p-4 border-r border-slate-700 text-center">Tipo</th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                        @forelse ($productos as $pro)
                            <tr class="hover:bg-slate-50/50 transition duration-150">
                                <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-gray-900">{{ $pro->codigo }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-700">{{ $pro->descripcion }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] md:text-xs font-semibold bg-slate-100 text-slate-600">
                                        {{ $pro->tipo ? $pro->tipo->descripcion : 'Sin Tipo' }}
                                    </span>
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
                                <td colspan="4" class="px-6 py-10 md:py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-box-open text-3xl md:text-4xl mb-3 text-gray-200"></i>
                                        <p class="text-sm md:text-base">No se encontraron productos con los criterios ingresados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($productos->hasPages())
                <div class="px-4 md:px-6 py-3 md:py-4 border-t border-gray-100 bg-gray-50/50">
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