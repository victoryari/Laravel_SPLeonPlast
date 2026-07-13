@extends('layouts.app')
@section('title', 'Editar Centro de Trabajo')

@section('content')
<div class="container mx-auto max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Editar Centro de Trabajo</h1>

    </div>

    <div class="max-w-2xl bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
        <form action="{{ route('centros_trabajo.update', $centro->codigo) }}" method="POST" class="p-6 md:p-8">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-400 mb-2">Código del Centro</label>
                    <input type="text" value="{{ $centro->codigo }}" 
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed outline-none font-bold" readonly>
                    <p class="text-[10px] text-slate-400 mt-2 italic">El código no puede ser modificado por integridad de datos.</p>
                </div>

                <div>
                    <label for="descripcion" class="block text-sm font-bold text-slate-700 mb-2">Descripción / Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $centro->descripcion) }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('descripcion') border-red-500 @else border-slate-300 @enderror focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" required>
                    @error('descripcion')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
                <a href="{{ route('centros_trabajo.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-8 rounded-lg shadow transition">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection