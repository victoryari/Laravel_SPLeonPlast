@extends('layouts.app')
@section('title', 'Operaciones de Producción')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Operaciones de Producción</h1>
            <p class="text-xs sm:text-sm text-gray-600">Gestión de etapas y operaciones del proceso</p>
        </div>
        <a href="{{ route('operaciones_produccion.create') }}" class="shrink-0 flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
            <i class="fas fa-plus"></i>
            <span class="hidden sm:inline ml-2">Nuevo</span>
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" value="{{ $search ?? '' }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm md:text-base" placeholder="Buscar por código o descripción...">
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
                    <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                        @forelse ($operaciones as $op)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-gray-900">{{ $op->codigo }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-700">{{ $op->descripcion }}</td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 md:gap-3">
                                        <a href="{{ route('operaciones_produccion.edit', $op->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition" title="Editar">
                                            <i class="fas fa-edit text-sm md:text-lg"></i>
                                        </a>
                                        <form action="{{ route('operaciones_produccion.destroy', $op->codigo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Anular esta operación?');">
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
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500 italic">
                                    No se encontraron registros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($operaciones->hasPages())
                <div class="px-4 md:px-6 py-3 border-t bg-gray-50/50">
                    {{ $operaciones->links() }}
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
                    tableContainer.innerHTML = doc.getElementById('table-container').innerHTML;
                });
        }

        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(fetchResults, 400);
        });
    });
</script>
@endsection