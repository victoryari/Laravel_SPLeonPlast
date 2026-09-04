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

    <div class="bg-white rounded-xl shadow-md border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-slate-800 text-white text-[11px] uppercase tracking-wider font-bold">
                        <th class="py-3 px-4 md:px-6 w-32">Código</th>
                        <th class="py-3 px-4 md:px-6">Descripción</th>
                        <th class="py-3 px-4 md:px-6 text-center w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($unidades as $unidad)
                        <tr class="hover:bg-slate-50/80 transition duration-150">
                            <td class="px-4 md:px-6 py-2.5 font-bold text-xs md:text-sm text-slate-900 whitespace-nowrap">{{ $unidad->codigo }}</td>
                            <td class="px-4 md:px-6 py-2.5 text-xs md:text-sm font-medium text-slate-800 uppercase">{{ $unidad->descripcion }}</td>
                            <td class="px-4 md:px-6 py-2.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5 md:gap-2">
                                    <a href="{{ route('unidades_medida.edit', $unidad->codigo) }}" class="inline-flex items-center justify-center w-8 h-8 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition-all shadow-2xs" title="Editar">
                                        <i class="fas fa-edit text-xs md:text-sm"></i>
                                    </a>
                                    <form action="{{ route('unidades_medida.destroy', $unidad->codigo) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Desea anular este registro?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all shadow-2xs" title="Anular">
                                            <i class="fas fa-trash-alt text-xs md:text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-10">
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