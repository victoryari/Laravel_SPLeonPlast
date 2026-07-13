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

    <x-table :headers="['Código', 'Descripción', ['label' => 'Acciones', 'class' => 'text-center']]">
        @forelse($unidades as $unidad)
            <tr class="hover:bg-slate-50/50 transition duration-150">
                <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-slate-900">{{ $unidad->codigo }}</td>
                <td class="px-4 md:px-6 py-3 md:py-4 text-slate-700">{{ $unidad->descripcion }}</td>
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
    </x-table>
</div>
@endsection