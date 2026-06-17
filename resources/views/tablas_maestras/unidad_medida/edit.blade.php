@extends('layouts.app')

@section('title', 'Editar Unidad')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-page-header title="Editar Unidad: {{ $unidad->codigo }}" subtitle="Actualice la descripción de la unidad seleccionada." />

    <x-card class="p-6 md:p-8">
        <form action="{{ route('unidades_medida.update', $unidad->codigo) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <x-form-group label="Código (No editable)">
                <input type="text" value="{{ $unidad->codigo }}" disabled
                       class="input-field bg-slate-50 text-slate-400 font-mono cursor-not-allowed">
            </x-form-group>

            <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                <input type="text" name="descripcion" value="{{ old('descripcion', $unidad->descripcion) }}"
                       class="input-field @error('descripcion') border-red-500 @enderror">
                @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </x-form-group>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('unidades_medida.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Actualizar Cambios</button>
            </div>
        </form>
    </x-card>
</div>
@endsection