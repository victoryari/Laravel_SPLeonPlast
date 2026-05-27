@extends('layouts.app')
@section('title', 'Fórmulas')

@section('content')
<div class="container mx-auto">
    <x-page-header title="Maestro de Fórmulas" subtitle="Gestión de fórmulas y composiciones">
        <x-slot:actions>
            <a href="{{ route('formulas.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" value="{{ $search ?? '' }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition" placeholder="Buscar por código o descripción...">
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
                            <th class="p-4 border-r border-slate-700 text-center">Ítems</th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs md:text-sm" id="tbFormulas">
                        @forelse ($formulas as $formu)
                            <tr class="hover:bg-slate-50/50 transition duration-150">
                                <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-gray-900">{{ $formu->codigo }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-700">{{ $formu->descripcion }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <x-badge color="{{ $formu->composiciones_count > 0 ? 'green' : 'red' }}">{{ $formu->composiciones_count }} comp.</x-badge>
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 md:gap-3">
                                        <a href="{{ route('formulas.composicion', $formu->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-purple-600 bg-purple-50 hover:bg-purple-600 hover:text-white rounded-lg transition-all" title="Diseñar Composición">
                                            <i class="fas fa-list-ol text-sm md:text-lg"></i>
                                        </a>
                                        <a href="{{ route('formulas.edit', $formu->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition-all" title="Editar Cabecera">
                                            <i class="fas fa-edit text-sm md:text-lg"></i>
                                        </a>
                                        <form action="{{ route('formulas.destroy', $formu->codigo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Anular esta fórmula y su composición?');">
                                            @csrf @method('DELETE')
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
                                    <x-empty-state icon="fa-flask" message="No se encontraron fórmulas registradas." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($formulas->hasPages())
                <div class="px-4 md:px-6 py-3 md:py-4 border-t border-gray-100 bg-gray-50/50">
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
        let timeout = null;

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
            clearTimeout(timeout);
            timeout = setTimeout(() => fetchResults(), 400);
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