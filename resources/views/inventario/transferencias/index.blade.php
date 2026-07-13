@extends('layouts.app')
@section('title', 'Transferencias entre Almacenes')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header 
        title="Transferencias de Almacén" 
        subtitle="Historial de movimientos de stock entre almacenes."
    >
        <x-slot name="actions">
            <a href="{{ route('inventario.transferencias.create') }}" class="btn-primary flex items-center gap-2">
                <i class="fas fa-exchange-alt"></i> Nueva Transferencia
            </a>
        </x-slot>
    </x-page-header>

    <x-filter-bar action="{{ route('inventario.transferencias.index') }}">
        <div class="flex items-center gap-4 w-full md:w-auto">
            <div class="flex flex-col">
                <label class="text-[10px] uppercase text-slate-500 font-bold mb-1">Fecha Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="input-field py-2" max="{{ date('Y-m-d') }}">
            </div>
            <div class="flex flex-col">
                <label class="text-[10px] uppercase text-slate-500 font-bold mb-1">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="input-field py-2" max="{{ date('Y-m-d') }}">
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-[250px]">
            <label class="text-[10px] uppercase text-slate-500 font-bold mb-1">Búsqueda</label>
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por número o nota..." 
                    class="input-field pl-10 py-2">
            </div>
        </div>
    </x-filter-bar>

    @php
        $headers = [
            ['label' => 'Documento', 'class' => 'text-left'],
            ['label' => 'Fecha', 'class' => 'text-left'],
            ['label' => 'Origen', 'class' => 'text-left'],
            ['label' => 'Destino', 'class' => 'text-left'],
            ['label' => 'Estado', 'class' => 'text-left']
        ];
    @endphp
    
    <x-data-table :headers="$headers" :hasActions="true">
        @forelse($transferencias as $t)
        <tr class="hover:bg-slate-50 transition">
            <td class="px-6 py-4 font-bold text-slate-800">{{ $t->numero_transferencia }}</td>
            <td class="px-6 py-4 text-slate-600">{{ \Carbon\Carbon::parse($t->fecha_transferencia)->format('d/m/Y H:i') }}</td>
            <td class="px-6 py-4">
                <span class="inline-flex px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-bold">{{ $t->almacenOrigen->descripcion ?? $t->codigo_almacen_origen }}</span>
            </td>
            <td class="px-6 py-4">
                <span class="inline-flex px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold">{{ $t->almacenDestino->descripcion ?? $t->codigo_almacen_destino }}</span>
            </td>
            <td class="px-6 py-4">
                <x-badge-status estado="{{ $t->estado }}" />
            </td>
            <td class="px-6 py-4 text-center">
                <a href="{{ route('inventario.transferencias.show', $t->id_transferencia) }}" class="btn-secondary text-xs inline-flex items-center gap-1 px-3 py-1.5" title="Ver detalle">
                    <i class="fas fa-eye text-blue-600"></i> Ver
                </a>
            </td>
        </tr>
        @empty
        <x-slot name="empty">
            <i class="fas fa-exchange-alt text-4xl text-slate-300 mb-3"></i>
            <p>No se han registrado transferencias de almacén.</p>
        </x-slot>
        @endforelse
    </x-data-table>

    @if($transferencias->hasPages())
        <div class="mt-4">
            {{ $transferencias->links() }}
        </div>
    @endif
</div>
@endsection
