@extends('layouts.app')

@section('title', 'Editar Almacén')

@section('content')
<div class="container mx-auto max-w-3xl">
    <x-page-header title="Editar Almacén" subtitle="Modifique los datos del almacén." />

    <x-card class="p-6">
        <form action="{{ route('almacenes.update', $almacen->codigo_almacen) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1">
                    <x-form-group label="Código">
                        <input type="text" id="codigo_almacen" value="{{ $almacen->codigo_almacen }}" class="input-field bg-slate-50 text-slate-400 font-mono cursor-not-allowed" readonly>
                        <p class="text-xs text-gray-400 mt-1">El código no se puede modificar.</p>
                    </x-form-group>
                </div>

                <div class="col-span-1">
                    <x-form-group label="Tipo de Almacén" required :error="$errors->first('tipo_almacen')">
                        <select name="tipo_almacen" id="tipo_almacen" class="input-field @error('tipo_almacen') border-red-500 @enderror" required>
                            <option value="">Seleccione...</option>
                            @foreach($tipos as $key => $label)
                                <option value="{{ $key }}" {{ (old('tipo_almacen', $almacen->tipo_almacen)) == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('tipo_almacen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </x-form-group>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                        <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $almacen->descripcion) }}" class="input-field @error('descripcion') border-red-500 @enderror" placeholder="Nombre o descripción del almacén" maxlength="100" required>
                        @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </x-form-group>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <x-form-group label="Dirección">
                        <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $almacen->direccion) }}" class="input-field" placeholder="Ubicación física del almacén" maxlength="200">
                    </x-form-group>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <x-form-group label="Responsable">
                        <input type="text" name="responsable" id="responsable" value="{{ old('responsable', $almacen->responsable) }}" class="input-field" placeholder="Persona encargada del almacén" maxlength="100">
                    </x-form-group>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6">
                <a href="{{ route('almacenes.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Actualizar Almacén</button>
            </div>
        </form>
    </x-card>
</div>
@endsection
