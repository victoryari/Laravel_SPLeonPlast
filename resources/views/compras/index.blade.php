@extends('layouts.app')
@section('title', 'Registro de Compras')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Registro de Compras</h1>
            <p class="text-xs sm:text-sm text-gray-600">Gestión administrativa de adquisiciones</p>
        </div>
        <a href="{{ route('compras.create') }}" class="shrink-0 flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
            <i class="fas fa-plus"></i>
            <span class="hidden sm:inline ml-2">Nueva Compra</span>
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6">
        <form action="{{ route('compras.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm md:text-base outline-none" placeholder="Buscar por documento o proveedor...">
            </div>
            <select name="estado" onchange="this.form.submit()" class="py-2 md:py-2.5 px-4 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                <option value="">Todos los estados</option>
                <option value="PENDIENTE" {{ request('estado') === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                <option value="RECIBIDA" {{ request('estado') === 'RECIBIDA' ? 'selected' : '' }}>Recibida</option>
                <option value="CANCELADA" {{ request('estado') === 'CANCELADA' ? 'selected' : '' }}>Anulada</option>
            </select>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 border-b border-gray-200 text-[11px] md:text-xs">
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">N° Documento / Fecha</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Proveedor</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-right">Total</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Creado Por</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-center">Estado</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                    @forelse ($compras as $compra)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="font-bold text-gray-900 uppercase">
                                    {{ $compra->tipo_documento }} {{ $compra->serie_documento }}-{{ $compra->numero_documento }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="text-gray-800 font-medium">
                                    {{ $compra->datosProveedor->razon_social ?? 'Proveedor No Encontrado' }}
                                </div>
                                <div class="text-xs text-gray-500">RUC: {{ $compra->ruc_proveedor ?? '-' }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-right font-bold text-blue-600">
                                S/ {{ number_format($compra->total, 2) }}
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600">
                                {{ $compra->creador->nombre_usuario ?? 'Sistema' }}
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                @switch($compra->estado)
                                    @case('PENDIENTE')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-bold border border-yellow-200">PENDIENTE</span>
                                        @break
                                    @case('RECIBIDA')
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold border border-green-200">RECIBIDA</span>
                                        @break
                                    @case('CANCELADA')
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-[10px] font-bold border border-red-200">ANULADA</span>
                                        @break
                                    @default
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-[10px] font-bold border border-gray-200">{{ $compra->estado }}</span>
                                @endswitch
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    
                                    {{-- Botón VER: Siempre visible --}}
                                    <a href="{{ route('compras.show', $compra->id_compra) }}" class="text-slate-400 hover:text-blue-600 transition-colors" title="Ver Detalle">
                                        <i class="fas fa-eye text-lg"></i>
                                    </a>

                                    {{-- Botones EDITAR y ANULAR: Solo visibles si está PENDIENTE --}}
                                    @if($compra->estado === 'PENDIENTE')
                                        <a href="{{ route('compras.edit', $compra->id_compra) }}" class="text-slate-400 hover:text-amber-600 transition-colors" title="Editar">
                                            <i class="fas fa-edit text-lg"></i>
                                        </a>
                                        
                                        <button type="button" data-id="{{ $compra->id_compra }}" data-documento="{{ $compra->numero_documento }}" class="btn-anular text-slate-400 hover:text-red-600 transition-colors" title="Anular">
                                            <i class="fas fa-ban text-lg"></i>
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
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">No se encontraron compras registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 md:px-6 py-3 border-t bg-gray-50/50">
            {{ $compras->links() }}
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-anular');
        if (!btn) return;

        const id = btn.getAttribute('data-id');
        const numero = btn.getAttribute('data-documento') || 'S/N';

        if (confirm('¿Está seguro de anular el documento ' + numero + '? Escriba ANULAR para confirmar.')) {
            const palabra = prompt('Escriba ANULAR para desactivar el registro:');
            if (palabra === 'ANULAR') {
                document.getElementById('form-anular-' + id).submit();
            }
        }
    });
</script>
@endsection