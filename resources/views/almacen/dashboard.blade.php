@extends('layouts.app')
@section('title', 'Panel de Almacén')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Panel de Control - Almacén</h1>
        <p class="text-sm text-slate-600 mt-1">Bienvenido al sistema, <span class="font-bold text-primary">{{ Auth::user()->nombre_usuario }}</span>.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 border-b-4 border-b-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Requerimientos Pdtes.</p>
                    <p class="text-3xl font-black text-slate-800 mt-2">{{ $reqsPendientes }}</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 text-2xl shadow-inner">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 border-b-4 border-b-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stock Crítico</p>
                    <p class="text-3xl font-black text-slate-800 mt-2">{{ $alertasStock }}</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center text-red-500 text-2xl shadow-inner">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 border-b-4 border-b-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Recepciones Pdtes.</p>
                    <p class="text-3xl font-black text-slate-800 mt-2">{{ $recepcionesPend }}</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center text-green-500 text-2xl shadow-inner">
                    <i class="fas fa-box-open"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-700 uppercase text-sm tracking-wide mb-4 flex items-center">
                <i class="fas fa-tasks text-primary mr-2"></i> Últimos Requerimientos
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 uppercase text-xs">
                            <th class="px-4 py-2 font-semibold">Código</th>
                            <th class="px-4 py-2 font-semibold">Fecha</th>
                            <th class="px-4 py-2 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ultimosReqs as $req)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2 font-medium">{{ $req->codigo }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($req->fecha_requerimiento)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($req->estado == 'ATENDIDO_TOTAL') bg-green-100 text-green-800 
                                    @elseif($req->estado == 'ATENDIDO_PARCIAL') bg-blue-100 text-blue-800 
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ str_replace('_', ' ', $req->estado) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-4 text-center text-slate-400">No hay requerimientos recientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-700 uppercase text-sm tracking-wide mb-4 flex items-center">
                <i class="fas fa-exchange-alt text-blue-500 mr-2"></i> Últimos Movimientos
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 uppercase text-xs">
                            <th class="px-4 py-2 font-semibold">Producto</th>
                            <th class="px-4 py-2 font-semibold">Tipo</th>
                            <th class="px-4 py-2 font-semibold">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ultimosKardex as $mov)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2">{{ Str::limit($mov->nombre_producto, 25) }}</td>
                            <td class="px-4 py-2">
                                <span class="font-bold {{ $mov->tipo_movimiento == 'INGRESO' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $mov->tipo_movimiento }}
                                </span>
                            </td>
                            <td class="px-4 py-2 font-medium">
                                {{ $mov->tipo_movimiento == 'INGRESO' ? number_format($mov->cantidad_entrada, 2) : number_format($mov->cantidad_salida, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-4 text-center text-slate-400">Sin movimientos recientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
