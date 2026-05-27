@extends('layouts.app')
@section('title', 'Editar Actividad')

@section('content')
<div class="container mx-auto max-w-2xl pb-10">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Editar Actividad</h1>
        <a href="{{ route('actividades.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition flex items-center w-fit">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <form action="{{ route('actividades.update', $actividad->codigo) }}" method="POST" class="p-6 md:p-8">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2">Código (No editable)</label>
                    <input type="text" value="{{ $actividad->codigo }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed font-bold" readonly>
                </div>

                <div>
                    <label for="descripcion" class="block text-sm font-bold text-gray-700 mb-2">Descripción <span class="text-red-500">*</span></label>
                    <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $actividad->descripcion) }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('descripcion') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    @error('descripcion') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-50 flex justify-end gap-3">
                <a href="{{ route('actividades.index') }}" class="px-6 py-3 text-gray-600 font-bold hover:bg-gray-50 rounded-xl transition">Cancelar</a>
                <button type="submit" class="px-8 py-3 bg-primary hover:bg-primary-dark text-white font-bold rounded-xl shadow-lg transition">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection