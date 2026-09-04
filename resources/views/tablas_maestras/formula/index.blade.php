@extends('layouts.app')
@section('title', 'Fórmulas')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <!-- Header de Página -->
    <x-page-header title="Maestro de Fórmulas" subtitle="Gestión de fórmulas y composiciones">
        <x-slot:actions>
            <a href="{{ route('formulas.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Buscador y Filtros -->
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md border border-slate-200/80 mb-5">
        <div class="flex gap-2">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400 text-sm"></i>
                </div>
                <input type="text" id="searchInput" value="{{ $search ?? '' }}" class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm text-slate-800 outline-none transition shadow-xs" placeholder="Buscar por código o descripción...">
            </div>
            <button id="btnClearSearch" type="button" class="btn-secondary px-4 py-2 text-sm flex items-center">
                <i class="fas fa-times mr-1.5"></i> <span class="hidden sm:inline">Limpiar</span>
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div id="table-container" class="transition-opacity duration-300">
        <div class="bg-white rounded-xl shadow-md border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-800 text-white text-[11px] uppercase tracking-wider font-bold">
                            <th class="py-3 px-4 md:px-6 w-44">Código</th>
                            <th class="py-3 px-4 md:px-6">Descripción</th>
                            <th class="py-3 px-4 md:px-6 text-center w-32">Ítems</th>
                            <th class="py-3 px-4 md:px-6 text-center w-40">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white" id="tbFormulas">
                        @forelse ($formulas as $formu)
                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                <td class="px-4 md:px-6 py-2.5 font-bold text-xs md:text-sm text-slate-900 whitespace-nowrap">{{ $formu->codigo }}</td>
                                <td class="px-4 md:px-6 py-2.5 text-xs md:text-sm font-medium text-slate-800 uppercase">{{ $formu->descripcion }}</td>
                                <td class="px-4 md:px-6 py-2.5 text-center whitespace-nowrap">
                                    <x-badge color="{{ $formu->composiciones_count > 0 ? 'green' : 'red' }}">{{ $formu->composiciones_count }} comp.</x-badge>
                                </td>
                                <td class="px-4 md:px-6 py-2.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5 md:gap-2">
                                        <a href="{{ route('formulas.composicion', $formu->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 text-purple-600 bg-purple-50 hover:bg-purple-600 hover:text-white rounded-lg transition-all shadow-2xs" title="Diseñar Composición">
                                            <i class="fas fa-list-ol text-xs md:text-sm"></i>
                                        </a>
                                        <a href="{{ route('formulas.edit', $formu->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition-all shadow-2xs" title="Editar Cabecera">
                                            <i class="fas fa-edit text-xs md:text-sm"></i>
                                        </a>
                                        <form action="{{ route('formulas.destroy', $formu->codigo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Anular esta fórmula y su composición?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all shadow-2xs" title="Anular">
                                                <i class="fas fa-trash-alt text-xs md:text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10">
                                    <x-empty-state icon="fa-flask" message="No se encontraron fórmulas registradas." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($formulas->hasPages())
                <div class="px-4 md:px-6 py-3.5 border-t border-slate-100 bg-slate-50">
                    {{ $formulas->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const tableContainer = document.getElementById('table-container');
        const btnClearSearch = document.getElementById('btnClearSearch');
        let timeout = null;

        // Recuperar filtro de sessionStorage si no hay en URL
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('search')) {
            const savedSearch = sessionStorage.getItem('formulas_search');
            if (savedSearch && searchInput.value === '') {
                searchInput.value = savedSearch;
                fetchResults();
            }
        } else {
            sessionStorage.setItem('formulas_search', searchInput.value);
        }

        function fetchResults(url = null) {
            if (!url) {
                url = new URL(window.location.href);
                url.searchParams.set('search', searchInput.value);
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
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    tableContainer.classList.remove('opacity-50', 'pointer-events-none');
                });
        }

        searchInput.addEventListener('input', function () {
            sessionStorage.setItem('formulas_search', searchInput.value);
            clearTimeout(timeout);
            timeout = setTimeout(() => fetchResults(), 400);
        });

        btnClearSearch.addEventListener('click', function() {
            searchInput.value = '';
            sessionStorage.removeItem('formulas_search');
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