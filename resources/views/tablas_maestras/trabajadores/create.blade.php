@extends('layouts.app')
@section('title', 'Nuevo Trabajador')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Registrar Trabajador</h1>
        <p class="text-sm text-slate-500">Alta de nuevo personal o contratista.</p>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
        <form action="{{ route('trabajadores.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="codigo" class="block text-sm font-bold text-gray-700 mb-2">Código <span class="text-red-500">*</span></label>
                    <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('codigo') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="Ej: Registrar Nro. DNI" required>
                    @error('codigo')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="nombre" class="block text-sm font-bold text-gray-700 mb-2">Nombre Completo <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('nombre') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="Apellidos y Nombres" required>
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="empresa" class="block text-sm font-bold text-gray-700 mb-2">Empresa <span class="text-xs font-normal text-gray-400">(Opcional)</span></label>
                    <input type="text" name="empresa" id="empresa" value="{{ old('empresa') }}" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="Ej: Leon Plast / Contratistas SAC">
                </div>

                <div>
                    <label for="sueldo" class="block text-sm font-bold text-gray-700 mb-2">Sueldo Base <span class="text-xs font-normal text-gray-400">(Opcional)</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-medium">S/</span>
                        </div>
                        <input type="number" step="0.01" min="0" name="sueldo" id="sueldo" value="{{ old('sueldo') }}" 
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                               placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('trabajadores.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md transition font-semibold">Guardar Unidad</button>
            </div>
        </form>
    </div>
</div>
@endsection