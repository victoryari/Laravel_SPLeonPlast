@extends('layouts.app')

@section('title', 'Editar Producto de Proceso')

@section('content')
<div class="container mx-auto pb-8 md:pb-10 max-w-2xl">
    <x-page-header title="Editar Producto de Proceso" subtitle="Modificar el producto en proceso: {{ $producto->codigo }}" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="p-6">
            <form action="{{ route('productos_proceso.update', $producto->codigo) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <x-form-group label="Código">
                    <input type="text" name="codigo" id="codigo" value="{{ $producto->codigo }}" disabled class="input-field bg-gray-50" />
                </x-form-group>

                <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                    <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $producto->descripcion) }}" class="input-field @error('descripcion') border-red-500 @enderror" placeholder="Nombre del producto en proceso" maxlength="100" required>
                    @error('descripcion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </x-form-group>

                <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('productos_proceso.index') }}" class="btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
