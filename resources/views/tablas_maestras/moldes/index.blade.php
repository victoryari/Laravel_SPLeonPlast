@extends('layouts.app')
@section('title', 'Moldes')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Maestro de Moldes" subtitle="Gestión de moldes para la producción">
        <x-slot:actions>
            <a href="{{ route('moldes.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo Molde</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" value="{{ $search ?? '' }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base" placeholder="Buscar por código o descripción...">
        </div>
    </div>

    <div id="table-container">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                            <th class="p-4 border-r border-slate-700 text-center">Código</th>
                            <th class="p-4 border-r border-slate-700 text-center">Descripción del Molde</th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                        @forelse ($moldes as $molde)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-gray-900">{{ $molde->codigo }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-700">{{ $molde->descripcion }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 md:gap-3">
                                        <a href="{{ route('moldes.edit', $molde->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition" title="Editar">
                                            <i class="fas fa-edit text-sm md:text-lg"></i>
                                        </a>
                                        <form action="{{ route('moldes.destroy', $molde->codigo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de desactivar este molde?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition" title="Desactivar">
                                                <i class="fas fa-trash-alt text-sm md:text-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <x-empty-state icon="fa-cube" message="No se encontraron moldes registrados." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($moldes->hasPages())
                <div class="px-4 md:px-6 py-3 border-t bg-gray-50/50">
                    {{ $moldes->links() }}
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

        function fetchResults() {
            const url = new URL(window.location.href);
            url.searchParams.set('search', searchInput.value);
            
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newTable = doc.getElementById('table-container');
                    if (newTable) tableContainer.innerHTML = newTable.innerHTML;
                });
        }

        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(fetchResults, 400);
        });
    });
</script>
@endsection