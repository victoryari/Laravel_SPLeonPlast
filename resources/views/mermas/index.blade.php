@extends('layouts.app')
@section('title', 'Registro de Mermas y Scrap')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Mermas y Scrap" subtitle="Gestión de material defectuoso y molido">
        <x-slot:actions>
            <a href="{{ route('mermas.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Registrar Merma</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6">
        <form action="{{ route('mermas.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="relative w-full md:w-48">
                <input type="date" name="fecha" value="{{ request('fecha') }}" class="w-full px-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base outline-none text-gray-600">
            </div>
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base outline-none" placeholder="Buscar por código o producto...">
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-2 md:py-2.5 rounded-lg transition font-medium text-sm md:text-base">Buscar</button>
            <a href="{{ route('mermas.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 md:px-6 py-2 md:py-2.5 rounded-lg border border-gray-300 transition font-medium text-sm md:text-base flex items-center justify-center">
                <i class="fas fa-times mr-2"></i> <span class="hidden sm:inline">Limpiar</span>
            </a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 border-b border-gray-200 text-[11px] md:text-xs">
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Fecha</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">OP</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Producto Origen</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Almacén</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-center">Tipo</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-right">Cantidad</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-right">Costo Total</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                    @forelse ($mermas as $m)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600">
                                {{ \Carbon\Carbon::parse($m->fecha_merma)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-800 font-medium whitespace-nowrap">
                                @if($m->ordenProduccion)
                                    OP-{{ $m->ordenProduccion->codigo_op }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="font-bold text-gray-900 uppercase">
                                    {{ $m->codigo_producto }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ Str::limit($m->descripcion_producto, 30) }}
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-800">
                                {{ $m->codigo_almacen }}
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                @if($m->tipo_merma === 'PURA')
                                    <x-badge color="red">PURA (Pérdida)</x-badge>
                                @else
                                    <x-badge color="green">{{ $m->tipo_merma }}</x-badge>
                                @endif
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-right font-bold text-slate-700">
                                {{ number_format($m->cantidad, 2) }}
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-right font-semibold text-primary">
                                S/ {{ number_format($m->costo_total, 2) }}
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                @if($m->estado !== 'ANULADA')
                                    <form action="{{ route('mermas.anular', $m->id_merma) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de anular esta merma? Esta acción revertirá los movimientos de inventario y no se puede deshacer.');">
                                        @csrf
                                        <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Anular Merma">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-red-500 font-bold border border-red-500 px-2 py-1 rounded">ANULADA</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500 italic">No se encontraron registros de mermas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 md:px-6 py-3 border-t bg-gray-50/50">
            {{ $mermas->links() }}
        </div>
    </div>
</div>
@endsection
