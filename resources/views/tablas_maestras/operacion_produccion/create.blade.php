@extends('layouts.app')
@section('title', 'Nueva Operación')

@section('content')
<div class="container mx-auto pb-10">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Registrar Operación de Producción</h1>
            <p class="text-sm text-gray-600">Complete los datos para la nueva operación</p>
        </div>
        <a href="{{ route('operaciones_produccion.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition flex items-center w-fit">
            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
        </a>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <form action="{{ route('operaciones_produccion.store') }}" method="POST" class="p-6 md:p-8">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="codigo" class="block text-sm font-bold text-gray-700 mb-2">Código de la Operación <span class="text-red-500">*</span></label>
                    <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('codigo') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition uppercase" 
                           placeholder="Ej: OP-001" required>
                    @error('codigo')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="descripcion" class="block text-sm font-bold text-gray-700 mb-2">Descripción / Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('descripcion') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="Ej: Mezclado de Materiales" required>
                    @error('descripcion')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-50 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <a href="{{ route('operaciones_produccion.index') }}" class="w-full sm:w-auto px-6 py-3 text-center text-gray-600 font-bold hover:bg-gray-50 rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i> Guardar Operación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection