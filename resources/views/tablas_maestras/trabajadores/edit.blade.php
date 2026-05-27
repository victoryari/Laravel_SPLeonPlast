@extends('layouts.app')
@section('title', 'Editar Trabajador')

@section('content')
<div class="container mx-auto max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Editar Trabajador</h1>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-yellow-500">
        <form action="{{ route('trabajadores.update', $trabajador->codigo) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">Código del trabajador</label>
                <input type="text" id="codigo" value="{{ $trabajador->codigo }}" class="w-full px-4 py-2 border border-gray-300 bg-gray-100 text-gray-500 rounded-lg cursor-not-allowed" disabled readonly>
                <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle"></i> El código es un identificador único y no se puede modificar.</p>
            </div>

            <div class="mb-6">
                <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo</label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $trabajador->nombre) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-yellow-500 @error('nombre') border-red-500 @enderror" maxlength="100" required>
                @error('nombre')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="empresa" class="block text-sm font-bold text-gray-700 mb-2">Empresa <span class="text-xs font-normal text-gray-400">(Opcional)</span></label>
                <input type="text" name="empresa" id="empresa" value="{{ old('empresa', $trabajador->empresa) }}" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
            </div>

            <div class="mb-6">
                <label for="sueldo" class="block text-sm font-bold text-gray-700 mb-2">Sueldo Base <span class="text-xs font-normal text-gray-400">(Opcional)</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-medium">S/</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="sueldo" id="sueldo" value="{{ old('sueldo', $trabajador->sueldo) }}" 
                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-100">
                <a href="{{ route('trabajadores.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar Registro
                </button>
            </div>
        </form>
    </div>
</div>
@endsection