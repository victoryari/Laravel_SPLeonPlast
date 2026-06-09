@extends('layouts.app')
@section('title', 'Bandeja de Despachos')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Bandeja de Despachos" subtitle="Requerimientos de producción pendientes de atención en el almacén">
    </x-page-header>

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6">
        <form action="{{ route('inventario.despachos.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="codigo" value="{{ request('codigo') }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base outline-none" placeholder="Buscar por código de requerimiento...">
            </div>
            <button type="submit" class="btn-primary">
                Buscar
            </button>
        </form>
    </div>

    <div class="mb-4 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
            <li class="mr-2" role="presentation">
                <a href="{{ route('inventario.despachos.index', ['tab' => 'pendientes', 'codigo' => request('codigo')]) }}" class="inline-block p-4 border-b-2 rounded-t-lg transition-colors {{ $tab === 'pendientes' ? 'border-primary text-primary font-bold' : 'border-transparent hover:text-gray-600 hover:border-gray-300 text-gray-500' }}">
                    <i class="fas fa-clock mr-2"></i>Pendientes de Atención
                </a>
            </li>
            <li class="mr-2" role="presentation">
                <a href="{{ route('inventario.despachos.index', ['tab' => 'atendidos', 'codigo' => request('codigo')]) }}" class="inline-block p-4 border-b-2 rounded-t-lg transition-colors {{ $tab === 'atendidos' ? 'border-emerald-500 text-emerald-600 font-bold' : 'border-transparent hover:text-gray-600 hover:border-gray-300 text-gray-500' }}">
                    <i class="fas fa-check-circle mr-2"></i>Atendidos (Historial)
                </a>
            </li>
        </ul>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 border-b border-gray-200 text-[11px] md:text-xs">
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Código / Fecha</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Productos a Despachar</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Solicitado Por</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-center">Estado</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                    @forelse ($requerimientos as $req)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="font-bold text-gray-900 uppercase">{{ $req->codigo }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($req->fecha_creacion)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="text-gray-800 text-xs space-y-1">
                                    @foreach($req->detalles->unique('codigo_producto')->take(3) as $det)
                                        <div class="truncate max-w-[300px]" title="{{ $det->producto->descripcion ?? $det->codigo_producto }}">
                                            <span class="font-bold text-slate-700">{{ $det->codigo_producto }}</span> 
                                            <span class="text-slate-500">- {{ $det->producto->descripcion ?? '' }}</span>
                                        </div>
                                    @endforeach
                                    
                                    @if($req->detalles->unique('codigo_producto')->count() > 3)
                                        <div class="text-[10px] text-indigo-500 font-medium mt-1">
                                            Y {{ $req->detalles->unique('codigo_producto')->count() - 3 }} producto(s) más...
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600">{{ $req->creador->nombre_usuario ?? 'Sistema' }}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                @php
                                    $colors = ['BORRADOR' => 'slate', 'PENDIENTE' => 'yellow', 'APROBADO' => 'green', 'RECHAZADO' => 'red', 'ATENDIDO_PARCIAL' => 'blue', 'ATENDIDO_TOTAL' => 'emerald', 'ANULADO' => 'red'];
                                @endphp
                                <x-badge color="{{ $colors[$req->estado] ?? 'slate' }}">{{ $req->estado }}</x-badge>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    @if($tab === 'pendientes')
                                        <a href="{{ route('inventario.despachos.atender', $req->id_requerimiento) }}" class="text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition-colors font-semibold shadow-sm" title="Despachar Materiales">
                                            <i class="fas fa-boxes mr-1"></i> Despachar
                                        </a>
                                    @else
                                        <a href="{{ route('requerimientos_materiales.show', $req->id_requerimiento) }}" class="text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-3 py-1 rounded-md transition-colors font-semibold shadow-sm" title="Ver Detalle">
                                            <i class="fas fa-eye mr-1"></i> Ver Detalle
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">No se encontraron requerimientos en esta bandeja.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 md:px-6 py-3 border-t bg-gray-50/50">
            {{ $requerimientos->links() }}
        </div>
    </div>
</div>
@endsection
