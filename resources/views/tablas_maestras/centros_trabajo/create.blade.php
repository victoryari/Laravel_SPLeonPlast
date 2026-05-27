@extends('layouts.app')
@section('title', 'Nuevo Centro de Trabajo')

@section('content')
<div class="container mx-auto max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Registrar Proceso</h1>
        
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <form action="{{ route('procesos_produccion.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="codigo" class="block text-sm font-bold text-gray-700 mb-2">Código del Centro <span class="text-red-500">*</span></label>
                    <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('codigo') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-primary focus:border-primary outline-none transition uppercase" 
                           placeholder="Ej: CTR-001" required>
                    @error('codigo')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="descripcion" class="block text-sm font-bold text-gray-700 mb-2">Descripción / Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('descripcion') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" 
                           placeholder="Ej: Área de Extrusión" required>
                    @error('descripcion')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('centros_trabajo.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-primary-dark shadow-md transition font-semibold">Guardar Unidad</button>
            </div>
        </form>
    </div>
</div>
@endsection