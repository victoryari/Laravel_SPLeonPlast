@extends('layouts.app')
@section('title', 'Nuevo Molde')

@section('content')
<div class="container mx-auto max-w-2xl">
    <x-page-header title="Registrar Molde" subtitle="Añada un nuevo molde al catálogo." />

    <x-card class="p-6 md:p-8">
        <form action="{{ route('moldes.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <x-form-group label="Código del Molde" required :error="$errors->first('codigo')">
                <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" class="input-field uppercase @error('codigo') border-red-500 @enderror" placeholder="Ej: MLD-001" required>
                @error('codigo') <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
            </x-form-group>

            <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" class="input-field @error('descripcion') border-red-500 @enderror" placeholder="Ej: Molde para Base Plástica" required>
                @error('descripcion') <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
            </x-form-group>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('moldes.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary"><i class="fas fa-save mr-2"></i> Guardar Molde</button>
            </div>
        </form>
    </x-card>
</div>
@endsection