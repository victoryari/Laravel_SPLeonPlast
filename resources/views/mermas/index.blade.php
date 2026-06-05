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
        <form action="{{ route('mermas.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 md:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base outline-none" placeholder="Buscar por código o producto...">
            </div>
            <button type="submit" class="btn-secondary px-6">Buscar</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 border-b border-gray-200 text-[11px] md:text-xs">
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Fecha</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Producto Origen</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider">Almacén</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-center">Tipo</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-right">Cantidad</th>
                        <th class="px-4 md:px-6 py-3 md:py-4 font-bold uppercase tracking-wider text-right">Costo Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                    @forelse ($mermas as $m)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600">
                                {{ $m->created_at->format('d/m/Y H:i') }}
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">No se encontraron registros de mermas.</td>
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
