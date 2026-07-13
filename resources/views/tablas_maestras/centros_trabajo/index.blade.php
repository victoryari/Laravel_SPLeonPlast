@extends('layouts.app')
@section('title', 'Centros de Trabajo')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Centros de Trabajo" subtitle="Gestión de áreas y recursos de producción">
        <x-slot:actions>
            <a href="{{ route('centros_trabajo.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400"></i>
            </div>
            <input type="text" id="searchInput" value="{{ $search ?? '' }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base" placeholder="Buscar por código o descripción...">
        </div>
    </div>

    <div id="table-container">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                            <th class="p-4 border-r border-slate-700 text-center">Código</th>
                            <th class="p-4 border-r border-slate-700 text-center">Descripción</th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs md:text-sm">
                        @forelse ($centros as $ct)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-slate-900">{{ $ct->codigo }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-slate-700">{{ $ct->descripcion }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 md:gap-3">
                                        <a href="{{ route('centros_trabajo.edit', $ct->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition" title="Editar">
                                            <i class="fas fa-edit text-sm md:text-lg"></i>
                                        </a>
                                        <form action="{{ route('centros_trabajo.destroy', $ct->codigo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Anular este centro de trabajo?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition" title="Anular">
                                                <i class="fas fa-trash-alt text-sm md:text-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <x-empty-state icon="fa-industry" message="No se encontraron registros de centros de trabajo." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($centros->hasPages())
                <div class="px-4 md:px-6 py-3 border-t bg-slate-50/50">
                    {{ $centros->links() }}
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