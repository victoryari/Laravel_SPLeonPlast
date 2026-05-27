@extends('layouts.app')

@section('title', 'Nuevo Tipo de Producto')

@section('content')
<div class="container mx-auto max-w-2xl">
    <x-page-header title="Nuevo Tipo de Producto" subtitle="Registre un nuevo tipo de producto en el sistema." />

    <x-card class="p-6">
        <form action="{{ route('tipos_producto.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <x-form-group label="Código del Tipo" required :error="$errors->first('codigo')">
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" class="input-field uppercase @error('codigo') border-red-500 @enderror" placeholder="Ej: TP01" maxlength="10" required>
                @error('codigo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </x-form-group>

            <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" class="input-field @error('descripcion') border-red-500 @enderror" placeholder="Ingrese la descripción" maxlength="100" required>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </x-form-group>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('tipos_producto.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </x-card>
</div>
@endsection