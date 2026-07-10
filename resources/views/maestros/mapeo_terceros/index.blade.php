@extends('layouts.app')
@section('title', 'Mapeo a Terceros')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Mapeo a Terceros</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Configuración de reglas para transformación de productos.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('mapeo-terceros.create') }}" class="px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-sm font-semibold transition-all shadow-sm shadow-primary/20 flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Nueva Regla
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-500"></i>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-bold">
                        <th class="p-4 border-b border-slate-100">ID</th>
                        <th class="p-4 border-b border-slate-100">Producto Origen (Sale)</th>
                        <th class="p-4 border-b border-slate-100">Producto Destino (Retorna)</th>
                        <th class="p-4 border-b border-slate-100">Descripción Proceso</th>
                        <th class="p-4 border-b border-slate-100 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mapeos as $mapeo)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="p-4 text-slate-500 font-medium">#{{ $mapeo->id_mapeo }}</td>
                        <td class="p-4">
                            <div class="text-sm font-bold text-slate-800">{{ $mapeo->codigo_producto_origen }}</div>
                            <div class="text-xs text-slate-500">{{ $mapeo->descripcion_origen }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-sm font-bold text-slate-800">{{ $mapeo->codigo_producto_destino }}</div>
                            <div class="text-xs text-slate-500">{{ $mapeo->descripcion_destino }}</div>
                        </td>
                        <td class="p-4 text-sm text-slate-600">
                            {{ $mapeo->descripcion_proceso ?? '-' }}
                        </td>
                        <td class="p-4 text-center">
                            <form action="{{ route('mapeo-terceros.destroy', $mapeo->id_mapeo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de eliminar esta regla?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Eliminar">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">
                            <i class="fas fa-route text-4xl mb-3 text-slate-200"></i>
                            <p class="text-sm font-medium">No hay reglas de mapeo configuradas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mapeos->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $mapeos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
