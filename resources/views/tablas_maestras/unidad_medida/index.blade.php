@extends('layouts.app')

@section('title', 'Mantenimiento de Unidades')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Unidades de Medida" subtitle="Gestión de unidades para los procesos de Leon Plast.">
        <x-slot:actions>
            <a href="{{ route('unidades_medida.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo</span>
            </a>
        </x-slot:actions>
    </x-page-header>

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
                                    <a href="{{ route('unidades_medida.edit', $unidad->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition-all" title="Editar">
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
                            <td colspan="3">
                                <x-empty-state icon="fa-balance-scale" message="No se encontraron unidades de medida activas." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection