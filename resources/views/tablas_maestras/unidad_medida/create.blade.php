@extends('layouts.app')

@section('title', 'Nueva Unidad')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Nueva Unidad de Medida</h1>
        <p class="text-sm text-slate-500">Defina el código y descripción para la nueva unidad.</p>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
        <form action="{{ route('unidades_medida.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Código de Unidad</label>
                <input type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: KG, UND, MTS" maxlength="10"
                       class="w-full px-4 py-2 border @error('codigo') border-red-500 @else border-slate-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none uppercase">
                @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion') }}" placeholder="Ej: Kilogramos"
                       class="w-full px-4 py-2 border @error('descripcion') border-red-500 @else border-slate-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('unidades_medida.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-primary-dark shadow-md transition font-semibold">Guardar Unidad</button>
            </div>
        </form>
    </div>
</div>
@endsection