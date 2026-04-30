@extends('layouts.app')

@section('title', 'Editar Unidad')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Editar Unidad: {{ $unidad->codigo }}</h1>
        <p class="text-sm text-slate-500">Actualice la descripción de la unidad seleccionada.</p>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
        <form action="{{ route('unidades_medida.update', $unidad->codigo) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Código (No editable)</label>
                <input type="text" value="{{ $unidad->codigo }}" disabled
                       class="w-full px-4 py-2 border border-slate-200 bg-slate-50 rounded-lg text-slate-400 font-mono">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion', $unidad->descripcion) }}"
                       class="w-full px-4 py-2 border @error('descripcion') border-red-500 @else border-slate-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('unidades_medida.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md transition font-semibold">Actualizar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection