@extends('layouts.app')
@section('title', 'Nuevo Centro de Trabajo')

@section('content')
<div class="container mx-auto max-w-2xl">
    <x-page-header title="Registrar Centro de Trabajo" subtitle="Registre un nuevo centro de trabajo en el sistema." />

    <x-card class="p-6">
        <form action="{{ route('procesos_produccion.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <x-form-group label="Código del Centro" required :error="$errors->first('codigo')">
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" class="input-field uppercase @error('codigo') border-red-500 @enderror" placeholder="Ej: CTR-001" required>
                @error('codigo')
                    <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                @enderror
            </x-form-group>

            <x-form-group label="Descripción / Nombre" required :error="$errors->first('descripcion')">
                <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" class="input-field @error('descripcion') border-red-500 @enderror" placeholder="Ej: Área de Extrusión" required>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                @enderror
            </x-form-group>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('centros_trabajo.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </x-card>
</div>
@endsection