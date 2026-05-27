@extends('layouts.app')
@section('title', 'Editar Molde')

@section('content')
<div class="container mx-auto max-w-2xl">
    <x-page-header title="Modificar Molde" subtitle="Actualizando molde: {{ $molde->codigo }}" />

    <x-card class="p-6 md:p-8">
        <form action="{{ route('moldes.update', $molde->codigo) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <x-form-group label="Código del Molde">
                <input type="text" value="{{ $molde->codigo }}" class="input-field bg-slate-50 text-slate-400 font-mono cursor-not-allowed" readonly>
                <p class="text-[10px] text-gray-400 mt-2 italic">El código no es editable.</p>
            </x-form-group>

            <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $molde->descripcion) }}" class="input-field @error('descripcion') border-red-500 @enderror" required>
                @error('descripcion') <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
            </x-form-group>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('moldes.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary"><i class="fas fa-sync-alt mr-2"></i> Actualizar</button>
            </div>
        </form>
    </x-card>
</div>
@endsection