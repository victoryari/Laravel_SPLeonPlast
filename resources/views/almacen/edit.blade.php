@extends('layouts.app')

@section('title', 'Editar Almacén')

@section('content')
<div class="container mx-auto max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Editar Almacén</h1>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <form action="{{ route('almacenes.update', $almacen->codigo_almacen) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Código (Solo lectura) --}}
                <div class="col-span-1">
                    <label for="codigo_almacen" class="block text-sm font-medium text-gray-700 mb-1">
                        Código
                    </label>
                    <input type="text" id="codigo_almacen" value="{{ $almacen->codigo_almacen }}" class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed" readonly>
                    <p class="text-xs text-gray-400 mt-1">El código no se puede modificar.</p>
                </div>

                {{-- Tipo de Almacén --}}
                <div class="col-span-1">
                    <label for="tipo_almacen" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Almacén <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo_almacen" id="tipo_almacen" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('tipo_almacen') ? 'border-red-500' : 'border-gray-300' }}" required>
                        <option value="">Seleccione...</option>
                        @foreach($tipos as $key => $label)
                            <option value="{{ $key }}" {{ (old('tipo_almacen', $almacen->tipo_almacen)) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_almacen')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descripción --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                        Descripción <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $almacen->descripcion) }}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('descripcion') ? 'border-red-500' : 'border-gray-300' }}" placeholder="Nombre o descripción del almacén" maxlength="100" required>
                    @error('descripcion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dirección --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">
                        Dirección <span class="text-xs text-gray-400 font-normal">(Opcional)</span>
                    </label>
                    <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $almacen->direccion) }}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('direccion') ? 'border-red-500' : 'border-gray-300' }}" placeholder="Ubicación física del almacén" maxlength="200">
                    @error('direccion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Responsable --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="responsable" class="block text-sm font-medium text-gray-700 mb-1">
                        Responsable <span class="text-xs text-gray-400 font-normal">(Opcional)</span>
                    </label>
                    <input type="text" name="responsable" id="responsable" value="{{ old('responsable', $almacen->responsable) }}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('responsable') ? 'border-red-500' : 'border-gray-300' }}" placeholder="Persona encargada del almacén" maxlength="100">
                    @error('responsable')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6">
                <a href="{{ route('almacenes.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md transition font-semibold">Actualizar Almacén</button>
            </div>
        </form>
    </div>
</div>
@endsection
