@extends('layouts.app')
@section('title', 'Auditoría y Extornos')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    
    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Panel de Auditoría y Extornos</h1>
        <p class="text-xs sm:text-sm text-slate-600">Reversión de movimientos de almacén. Uso exclusivo de Administrador.</p>
    </div>

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border border-red-100">
        <form method="GET" action="{{ route('inventario.extornos') }}" class="w-full flex flex-col md:flex-row gap-4">
            
            <div class="flex items-center gap-2">
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-slate-500 font-bold mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none" max="{{ date('Y-m-d') }}">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] uppercase text-slate-500 font-bold mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none" max="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <label class="text-[10px] uppercase text-slate-500 font-bold mb-1">Búsqueda</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </div>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Buscar recibo, ticket o producto..." 
                        class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm outline-none">
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('inventario.extornos') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors border border-slate-300">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <div id="table-container" class="transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold border-b border-slate-700">
                        <th class="p-4 border-r border-slate-700">Fecha / Hora</th>
                        <th class="p-4 border-r border-slate-700">Producto y Almacén</th>
                        <th class="p-4 border-r border-slate-700">Tipo</th>
                        <th class="p-4 border-r border-slate-700">Documento</th>
                        <th class="p-4 border-r border-slate-700 text-center">Cantidad</th>
                        <th class="p-4 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movimientos as $mov)
                    @php $primaryKey = $mov->id_kardex ?? $mov->id; @endphp
                    <tr class="hover:bg-red-50/40 transition">
                        <td class="px-4 md:px-6 py-3 text-slate-500">
                            {{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 md:px-6 py-3">
                            <p class="font-bold text-slate-800">{{ $mov->producto }}</p>
                            <p class="text-[10px] text-slate-500 font-medium">ALM: {{ $mov->almacen }}</p>
                        </td>
                        <td class="px-4 md:px-6 py-3">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold border {{ $mov->tipo_movimiento == 'INGRESO' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                {{ $mov->tipo_movimiento }}
                            </span>
                        </td>
                        <td class="px-4 md:px-6 py-3 font-medium text-slate-600">
                            {{ $mov->documento }} {{ $mov->numero_documento }}
                        </td>
                        <td class="px-4 md:px-6 py-3 text-center font-black text-base {{ $mov->cantidad_entrada > 0 ? 'text-green-600' : 'text-amber-600' }}">
                            {{ $mov->cantidad_entrada > 0 ? '+'.number_format($mov->cantidad_entrada, 2) : '-'.number_format($mov->cantidad_salida, 2) }}
                        </td>
                        <td class="px-4 md:px-6 py-3 text-center">
                            <button onclick="abrirModalExtorno({{ $primaryKey }}, '{{ $mov->numero_documento }}', '{{ $mov->producto }}')" 
                                class="bg-red-50 hover:bg-red-600 text-red-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all border border-red-200 hover:border-red-600 shadow-sm flex items-center gap-1 mx-auto">
                                <i class="fas fa-exclamation-triangle"></i> Revertir
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500 italic">No se encontraron movimientos disponibles para extorno.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movimientos->hasPages())
            <div class="px-4 md:px-6 py-3 border-t bg-slate-50/50">
                {{ $movimientos->links() }}
            </div>
        @endif
        </div>
    </div>
    </div>

<div id="modalExtorno" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm overflow-y-auto items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border-t-8 border-red-600">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <i class="fas fa-shield-alt text-3xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-black text-slate-800 mb-2">Advertencia Crítica</h3>
            <p class="text-sm text-slate-500 mb-4">Va a revertir el movimiento del producto <span id="lblProducto" class="font-bold text-slate-800"></span> del documento <span id="lblDoc" class="font-bold text-slate-800"></span>.</p>
            
            <form id="formExtorno" method="POST" class="text-left space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Motivo / Observación</label>
                    <input type="text" name="motivo" class="w-full border-slate-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 bg-slate-50" required placeholder="Ej: Error de digitación en guía, devolución...">
                </div>
                <div>
                    <label class="block text-xs font-bold text-red-600 uppercase mb-1">Confirmación de Seguridad</label>
                    <input type="text" name="confirmacion" class="w-full border-red-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 placeholder-red-200 font-bold text-center" required placeholder="Escriba la palabra EXTORNAR">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="cerrarModal()" class="flex-1 px-4 py-2.5 bg-slate-200 text-slate-700 rounded-lg font-bold text-sm hover:bg-slate-300 transition">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg font-bold text-sm hover:bg-red-700 shadow-md shadow-red-200 transition">Procesar Reversión</button>
                </div>
            </form>
    </div>
    </div>
</div>

<script>
    function abrirModalExtorno(id, doc, producto) {
        document.getElementById('lblDoc').innerText = doc;
        document.getElementById('lblProducto').innerText = producto;
        document.getElementById('formExtorno').action = `/admin/inventario/extornos/procesar/${id}`;
        document.getElementById('modalExtorno').classList.remove('hidden');
        document.getElementById('modalExtorno').classList.add('flex');
    }
    function cerrarModal() {
        document.getElementById('modalExtorno').classList.add('hidden');
        document.getElementById('modalExtorno').classList.remove('flex');
        document.getElementById('formExtorno').reset();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const tableContainer = document.getElementById('table-container');
        let timeout = null;

        function fetchResults(url = null) {
            if (!url) {
                url = new URL(window.location.href);
                if (searchInput.value) {
                    url.searchParams.set('search', searchInput.value);
                } else {
                    url.searchParams.delete('search');
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