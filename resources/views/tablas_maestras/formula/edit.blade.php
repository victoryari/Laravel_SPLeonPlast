@extends('layouts.app')

@section('title', 'Editar Formula')

@section('content')
<div class="container mx-auto max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Editar Formula</h1>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-yellow-500">
        <form action="{{ route('formulas.update', $formula->codigo) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-5">
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">Código del Tipo</label>
                <input type="text" id="codigo" value="{{ $formula->codigo }}" class="w-full px-4 py-2 border border-gray-300 bg-gray-100 text-gray-500 rounded-lg cursor-not-allowed" disabled readonly>
                <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle"></i> El código es un identificador único y no se puede modificar.</p>
            </div>

            <div class="mb-6">
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $formula->descripcion) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 @error('descripcion') border-red-500 @enderror" maxlength="100" required>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-100">
                <a href="{{ route('formulas.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar Registro
                </button>
            </div>
        </form>
    </div>
</div>
@endsection