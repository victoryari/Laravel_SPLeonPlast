@extends('layouts.app')
@section('title', 'Panel de Especialista')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Panel de Control - Especialista</h1>
        <p class="text-sm text-gray-600 mt-1">Bienvenido al sistema, <span class="font-bold text-primary">{{ Auth::user()->nombre_usuario }}</span>.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Fórmulas</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ $totalFormulas }}</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 text-2xl shadow-inner">
                    <i class="fas fa-flask"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Composiciones</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ $totalComposiciones }}</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500 text-2xl shadow-inner">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Productos</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ $totalProductos }}</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-primary-50 flex items-center justify-center text-primary text-2xl shadow-inner">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-teal-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Procesos</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">{{ $totalProcesos }}</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-teal-50 flex items-center justify-center text-teal-500 text-2xl shadow-inner">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wide mb-4 flex items-center">
                <i class="fas fa-flask text-purple-500 mr-2"></i> Últimas Fórmulas
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <th class="px-4 py-2 font-semibold">Código</th>
                            <th class="px-4 py-2 font-semibold">Descripción</th>
                            <th class="px-4 py-2 font-semibold">Componentes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($ultimasFormulas as $fm)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium">{{ $fm->codigo }}</td>
                            <td class="px-4 py-2">{{ $fm->descripcion }}</td>
                            <td class="px-4 py-2">
                                <x-badge color="slate">{{ $composicionesCount[$fm->codigo] ?? 0 }} items</x-badge>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-4 text-center text-gray-400">Sin fórmulas registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-700 uppercase text-sm tracking-wide mb-4 flex items-center">
                <i class="fas fa-box text-primary mr-2"></i> Acceso Rápido
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('formulas.index') }}" class="flex flex-col items-center justify-center p-4 bg-purple-50 border border-purple-100 rounded-lg text-purple-700 hover:bg-purple-100 transition">
                    <i class="fas fa-flask text-2xl mb-2"></i>
                    <span class="text-xs font-semibold">Fórmulas</span>
                </a>
                <a href="{{ route('productos.index') }}" class="flex flex-col items-center justify-center p-4 bg-primary-50 border border-primary-50 rounded-lg text-primary hover:bg-primary-50 transition">
                    <i class="fas fa-box text-2xl mb-2"></i>
                    <span class="text-xs font-semibold">Productos</span>
                </a>
                <a href="{{ route('procesos_produccion.index') }}" class="flex flex-col items-center justify-center p-4 bg-teal-50 border border-teal-100 rounded-lg text-teal-700 hover:bg-teal-100 transition">
                    <i class="fas fa-cogs text-2xl mb-2"></i>
                    <span class="text-xs font-semibold">Procesos</span>
                </a>
                <a href="{{ route('produccion.ordenes.index') }}" class="flex flex-col items-center justify-center p-4 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-700 hover:bg-indigo-100 transition">
                    <i class="fas fa-industry text-2xl mb-2"></i>
                    <span class="text-xs font-semibold">Órdenes</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
