@extends('layouts.app')

@section('title', 'Nuevo Producto')

@section('content')
<div class="container mx-auto max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Registrar Producto</h1>
        
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <form action="{{ route('productos.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1">
                    <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">
                        Código <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg uppercase focus:ring-blue-500 focus:border-blue-500 @error('codigo') border-red-500 @enderror" placeholder="Ej: PROD-001" maxlength="15" required>
                    @error('codigo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="col-span-1">
                    <label for="codigo_tipo_producto" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Producto <span class="text-red-500">*</span>
                    </label>
                    <select name="codigo_tipo_producto" id="codigo_tipo_producto" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('codigo_tipo_producto') border-red-500 @enderror" required>
                        <option value="">Seleccione...</option>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->codigo }}" {{ old('codigo_tipo_producto') == $tipo->codigo ? 'selected' : '' }}>
                                {{ $tipo->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    @error('codigo_tipo_producto')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                        Descripción <span class="text-xs text-gray-400 font-normal">(Opcional)</span>
                    </label>
                    <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('descripcion') border-red-500 @enderror" placeholder="Nombre o detalle del artículo" maxlength="150">
                    @error('descripcion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1">
                    <label for="codigo_unidad_medida" class="block text-sm font-medium text-gray-700 mb-1">
                        Unidad de Medida <span class="text-xs text-gray-400 font-normal">(Opcional)</span>
                    </label>
                    <select name="codigo_unidad_medida" id="codigo_unidad_medida" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('codigo_unidad_medida') border-red-500 @enderror">
                        <option value="">Ninguna / No aplica</option>
                        @foreach($unidades as $um)
                            <option value="{{ $um->codigo }}" {{ old('codigo_unidad_medida') == $um->codigo ? 'selected' : '' }}>
                                {{ $um->descripcion }} ({{ $um->codigo }})
                            </option>
                        @endforeach
                    </select>
                    @error('codigo_unidad_medida')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1">
                    <label for="codigo_color" class="block text-sm font-medium text-gray-700 mb-1">
                        Color <span class="text-xs text-gray-400 font-normal">(Opcional)</span>
                    </label>
                    <input type="text" name="codigo_color" id="codigo_color" value="{{ old('codigo_color') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('codigo_color') border-red-500 @enderror" placeholder="Ej: Rojo, Azul, Transparente..." maxlength="50">
                    @error('codigo_color')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1 md:col-span-2 flex items-center mt-2 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <input type="hidden" name="es_producto_proceso" value="0">
                    <input type="checkbox" name="es_producto_proceso" id="es_producto_proceso" value="1" {{ old('es_producto_proceso') == '1' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer">
                    <label for="es_producto_proceso" class="ml-3 text-sm font-medium text-gray-800 cursor-pointer">
                        Es Producto en Proceso (PEP)
                    </label>
                    <span class="ml-2 text-xs text-gray-500">(Marcar si este producto es un intermedio en la producción)</span>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('productos.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md transition font-semibold">Guardar Unidad</button>
            </div>
        </form>
    </div>
</div>
@endsection