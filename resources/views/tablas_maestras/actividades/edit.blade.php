@extends('layouts.app')
@section('title', 'Editar Actividad')

@section('content')
<div class="container mx-auto max-w-2xl">
    <x-page-header title="Editar Actividad" subtitle="Modifique la actividad seleccionada." />

    <x-card class="p-6 md:p-8">
        <form action="{{ route('actividades.update', $actividad->codigo) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <x-form-group label="Código (No editable)">
                <input type="text" value="{{ $actividad->codigo }}" class="input-field bg-slate-50 text-slate-400 font-mono cursor-not-allowed" readonly>
            </x-form-group>

            <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $actividad->descripcion) }}" class="input-field @error('descripcion') border-red-500 @enderror" required>
                @error('descripcion') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </x-form-group>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('actividades.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary"><i class="fas fa-sync-alt mr-2"></i> Actualizar</button>
            </div>
        </form>
    </x-card>
</div>
@endsection