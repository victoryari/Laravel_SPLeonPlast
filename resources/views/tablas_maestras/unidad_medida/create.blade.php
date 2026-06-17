@extends('layouts.app')

@section('title', 'Nueva Unidad')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-page-header title="Nueva Unidad de Medida" subtitle="Defina el código y descripción para la nueva unidad." />

    <x-card class="p-6 md:p-8">
        <form action="{{ route('unidades_medida.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <x-form-group label="Código de Unidad" required :error="$errors->first('codigo')">
                <input type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: KG, UND, MTS" maxlength="10"
                       class="input-field @error('codigo') border-red-500 @enderror uppercase">
                @error('codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </x-form-group>

            <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                <input type="text" name="descripcion" value="{{ old('descripcion') }}" placeholder="Ej: Kilogramos"
                       class="input-field @error('descripcion') border-red-500 @enderror">
                @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </x-form-group>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('unidades_medida.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar Unidad</button>
            </div>
        </form>
    </x-card>
</div>
@endsection