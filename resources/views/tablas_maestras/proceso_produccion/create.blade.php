@extends('layouts.app')

@section('title', 'Nuevo Proceso')

@section('content')
<div class="container mx-auto max-w-2xl">
    <x-page-header title="Registrar Proceso" subtitle="Registre un nuevo proceso de producción en el sistema." />

    <x-card class="p-6">
        <form action="{{ route('procesos_produccion.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <x-form-group label="Código" required :error="$errors->first('codigo')">
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" class="input-field uppercase @error('codigo') border-red-500 @enderror" placeholder="Ej: PROC-01" maxlength="15" required>
                @error('codigo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </x-form-group>

            <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" class="input-field @error('descripcion') border-red-500 @enderror" placeholder="Nombre o detalle del proceso" maxlength="150" required>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </x-form-group>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('procesos_produccion.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </x-card>
</div>
@endsection