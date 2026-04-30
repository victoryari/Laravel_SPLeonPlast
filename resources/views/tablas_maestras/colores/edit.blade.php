@extends('layouts.app')
@section('title', 'Editar Color')

@section('content')
<div class="container mx-auto pb-10 max-w-2xl">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Modificar Color</h1>
            <p class="text-sm text-gray-600">Actualizando registro: <span class="font-bold text-blue-600">{{ $color->codigo }}</span></p>
        </div>
        <a href="{{ route('colores.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition flex items-center w-fit">
            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <form action="{{ route('colores.update', $color->codigo) }}" method="POST" class="p-6 md:p-8">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 mb-2">Código del Color</label>
                    <input type="text" value="{{ $color->codigo }}" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed outline-none font-bold" readonly>
                    <p class="text-[10px] text-gray-400 mt-2 italic">El código no es editable.</p>
                </div>

                <div>
                    <label for="descripcion" class="block text-sm font-bold text-gray-700 mb-2">Descripción <span class="text-red-500">*</span></label>
                    <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $color->descripcion) }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('descripcion') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" required>
                    @error('descripcion')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-50 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <a href="{{ route('colores.index') }}" class="w-full sm:w-auto px-6 py-3 text-center text-gray-600 font-bold hover:bg-gray-50 rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection