@extends('layouts.app')

@section('title', 'Mantenimiento de Unidades')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Unidades de Medida</h1>
            <p class="text-xs sm:text-sm text-gray-600">Gestión de unidades para los procesos de Leon Plast.</p>
        </div>
        <a href="{{ route('unidades_medida.create') }}" class="shrink-0 flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
            <i class="fas fa-plus"></i>
            <span class="hidden sm:inline ml-2">Nuevo</span>
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                        <th class="p-4 border-r border-slate-700 text-center">Código</th>
                        <th class="p-4 border-r border-slate-700 text-center">Descripción</th>
                        <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs md:text-sm">
                    @forelse($unidades as $unidad)
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-gray-900">{{ $unidad->codigo }}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-700">{{ $unidad->descripcion }}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                <div class="flex items-center justify-center gap-2 md:gap-3">
                                    <a href="{{ route('unidades_medida.edit', $unidad->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all" title="Editar">
                                        <i class="fas fa-edit text-sm md:text-lg"></i>
                                    </a>
                                    <form action="{{ route('unidades_medida.destroy', $unidad->codigo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Desea anular este registro?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Anular">
                                            <i class="fas fa-trash-alt text-sm md:text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 md:py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-balance-scale text-3xl md:text-4xl mb-3 text-gray-200"></i>
                                    <p class="text-sm md:text-base">No se encontraron unidades de medida activas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection