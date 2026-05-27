@extends('layouts.app')

@section('title', 'Nuevo Proceso')

@section('content')
<div class="container mx-auto max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Registrar Proceso</h1>
        
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <form action="{{ route('procesos_produccion.store') }}" method="POST">
            @csrf
            
            <div class="mb-5">
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">
                    Código <span class="text-red-500">*</span>
                </label>
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg uppercase focus:ring-primary focus:border-primary @error('codigo') border-red-500 @enderror" placeholder="Ej: PROC-01" maxlength="15" required>
                @error('codigo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción <span class="text-red-500">*</span>
                </label>
                <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary @error('descripcion') border-red-500 @enderror" placeholder="Nombre o detalle del proceso" maxlength="150" required>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('procesos_produccion.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-primary-dark shadow-md transition font-semibold">Guardar Unidad</button>
            </div>
        </form>
    </div>
</div>
@endsection