@extends('layouts.app')
@section('title', 'Registro de Compras')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Registro de Compras</h1>
            <p class="text-xs sm:text-sm text-gray-600">Gestión administrativa de adquisiciones</p>
        </div>
        <a href="{{ route('compras.create') }}" class="shrink-0 flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
            <i class="fas fa-plus"></i>
            <span class="hidden sm:inline ml-2">Nueva Compra</span>
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

    {{-- Barra de Búsqueda y Filtro --}}
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6 flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base transition" placeholder="Buscar por documento o proveedor...">
        </div>
        
        <div class="md:w-1/4">
            <select id="estadoFilter" class="w-full px-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base transition cursor-pointer">
                <option value="">Todos los estados</option>
                <option value="PENDIENTE" {{ request('estado') == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                <option value="RECIBIDA" {{ request('estado') == 'RECIBIDA' ? 'selected' : '' }}>Recibida</option>
                <option value="CANCELADA" {{ request('estado') == 'CANCELADA' ? 'selected' : '' }}>Anulada</option>
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
                            <th class="p-4 border-r border-slate-700 text-center">N° Documento / Fecha</th>
                            <th class="p-4 border-r border-slate-700 text-center">Proveedor</th>
                            <th class="p-4 border-r border-slate-700 text-center">Total</th>
                            <th class="p-4 border-r border-slate-700 text-center hidden md:table-cell">Creado Por</th>
                            <th class="p-4 border-r border-slate-700 text-center">Estado</th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                        @forelse ($compras as $compra)
                            <tr class="hover:bg-slate-50/50 transition duration-150">
                                <td class="px-4 md:px-6 py-3 md:py-4">
                                    <div class="font-bold text-gray-900 uppercase">
                                        {{ $compra->tipo_documento }} {{ $compra->serie_documento }}-{{ $compra->numero_documento }}
                                    </div>
                                    <div class="text-[10px] md:text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4">
                                    <div class="text-gray-800 font-medium">
                                        {{ $compra->datosProveedor->razon_social ?? 'Proveedor No Encontrado' }}
                                    </div>
                                    <div class="text-[10px] md:text-xs text-gray-500">RUC: {{ $compra->ruc_proveedor ?? '-' }}</div>
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-right font-bold text-blue-600">
                                    S/ {{ number_format($compra->total, 2) }}
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">
                                    {{ $compra->creador->nombre_usuario ?? 'Sistema' }}
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    @php
                                        $estadoBadge = match($compra->estado) {
                                            'PENDIENTE' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'RECIBIDA'  => 'bg-green-50 text-green-700 border-green-200',
                                            'CANCELADA' => 'bg-red-50 text-red-700 border-red-200',
                                            default     => 'bg-slate-100 text-slate-600 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] md:text-xs font-semibold border {{ $estadoBadge }}">
                                        {{ $compra->estado }}
                                    </span>
                                </td>
                                <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 md:gap-3">
                                        {{-- Botón VER --}}
                                        <a href="{{ route('compras.show', $compra->id_compra) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-slate-500 bg-slate-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all" title="Ver Detalle">
                                            <i class="fas fa-eye text-sm md:text-lg"></i>
                                        </a>

                                        {{-- Botones EDITAR y ANULAR: Solo si PENDIENTE --}}
                                        @if($compra->estado === 'PENDIENTE')
                                            <a href="{{ route('compras.edit', $compra->id_compra) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all" title="Editar">
                                                <i class="fas fa-edit text-sm md:text-lg"></i>
                                            </a>
                                            
                                            <button onclick="confirmarAnulacion({{ $compra->id_compra }}, '{{ $compra->numero_documento }}')" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Anular">
                                                <i class="fas fa-ban text-sm md:text-lg"></i>
                                            </button>
                                            
                                            <form id="form-anular-{{ $compra->id_compra }}" action="{{ route('compras.anular', $compra->id_compra) }}" method="POST" class="hidden">
                                                @csrf
                                                <input type="hidden" name="confirmacion" value="ANULAR">
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 md:py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-file-invoice text-3xl md:text-4xl mb-3 text-gray-200"></i>
                                        <p class="text-sm md:text-base">No se encontraron compras con los criterios ingresados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($compras->hasPages())
                <div class="px-4 md:px-6 py-3 md:py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $compras->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function confirmarAnulacion(id, numero) {
        if (confirm(`¿Está seguro de anular el documento ${numero}? Escriba ANULAR para confirmar.`)) {
            const palabra = prompt("Escriba ANULAR para desactivar el registro:");
            if (palabra === 'ANULAR') {
                document.getElementById('form-anular-' + id).submit();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const estadoFilter = document.getElementById('estadoFilter');
        const tableContainer = document.getElementById('table-container');
        let timeout = null;

        function fetchResults(url = null) {
            if (!url) {
                url = new URL(window.location.href);
                url.searchParams.set('search', searchInput.value);
                
                if (estadoFilter.value) {
                    url.searchParams.set('estado', estadoFilter.value);
                } else {
                    url.searchParams.delete('estado');
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

        estadoFilter.addEventListener('change', function () {
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