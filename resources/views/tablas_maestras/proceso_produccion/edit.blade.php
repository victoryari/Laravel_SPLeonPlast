@extends('layouts.app')

@section('title', 'Editar Proceso')

@section('content')
<div class="container mx-auto max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Editar Proceso</h1>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-yellow-500">
        <form action="{{ route('procesos_produccion.update', $proceso->codigo) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-1">Código</label>
                <input type="text" value="{{ $proceso->codigo }}" class="w-full px-4 py-2 border border-slate-300 bg-slate-100 text-slate-500 rounded-lg cursor-not-allowed" disabled readonly>
                <p class="text-xs text-slate-500 mt-1"><i class="fas fa-info-circle"></i> El código es único y no se puede modificar.</p>
            </div>

            <div class="mb-6">
                <label for="descripcion" class="block text-sm font-medium text-slate-700 mb-1">
                    Descripción <span class="text-red-500">*</span>
                </label>
                <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $proceso->descripcion) }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 @error('descripcion') border-red-500 @enderror" maxlength="150" required>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="codigo_almacen" class="block text-sm font-medium text-slate-700 mb-1">
                    Almacén de Producción
                </label>
                <select name="codigo_almacen" id="codigo_almacen" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 @error('codigo_almacen') border-red-500 @enderror">
                    <option value="">-- No Asignado --</option>
                    @foreach($almacenes as $almacen)
                        <option value="{{ $almacen->codigo_almacen }}" {{ old('codigo_almacen', $proceso->codigo_almacen) == $almacen->codigo_almacen ? 'selected' : '' }}>
                            {{ $almacen->descripcion }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">Si selecciona un almacén, este proceso consumirá materias primas EXCLUSIVAMENTE de este almacén.</p>
                @error('codigo_almacen')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

             <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
                <a href="{{ route('procesos_produccion.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-8 rounded-lg shadow transition">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection