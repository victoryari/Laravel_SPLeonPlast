@extends('layouts.app')

@section('title', 'Nuevo Producto de Proceso')

@section('content')
<div class="container mx-auto pb-8 md:pb-10 max-w-2xl">
    <x-page-header title="Nuevo Producto de Proceso" subtitle="Registrar un nuevo producto en proceso (PEP)" />

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
        <div class="p-6">
            <form action="{{ route('productos_proceso.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <x-form-group label="Código" required :error="$errors->first('codigo')">
                    <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" class="input-field uppercase @error('codigo') border-red-500 @enderror" placeholder="Ej: PEP-001" maxlength="20" required>
                    @error('codigo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </x-form-group>

                <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                    <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" class="input-field @error('descripcion') border-red-500 @enderror" placeholder="Nombre del producto en proceso" maxlength="100" required>
                    @error('descripcion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </x-form-group>

                <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('productos_proceso.index') }}" class="btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
