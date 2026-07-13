@extends('layouts.app')
@section('title', 'Nueva Fórmula')

@section('content')
<div class="container mx-auto max-w-2xl">
    <x-page-header title="Registrar Fórmula Base" subtitle="Cree una nueva fórmula de producción." />

    <x-card class="p-6">
        <form action="{{ route('formulas.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <x-form-group label="Código de Fórmula" required :error="$errors->first('codigo')">
                <input type="text" name="codigo" value="{{ old('codigo') }}" class="input-field uppercase" required>
            </x-form-group>

            <x-form-group label="Descripción" required :error="$errors->first('descripcion')">
                <input type="text" name="descripcion" value="{{ old('descripcion') }}" class="input-field" required>
            </x-form-group>

            <x-form-group label="Material Reciclado Equivalente (Opcional)" :error="$errors->first('codigo_material_reciclado')">
                <select name="codigo_material_reciclado" class="input-field">
                    <option value="">-- No admite reciclado o no especificado --</option>
                    @foreach($materialesReciclados as $mat)
                        <option value="{{ $mat->codigo }}" {{ old('codigo_material_reciclado') == $mat->codigo ? 'selected' : '' }}>
                            {{ $mat->codigo }} - {{ $mat->descripcion }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">Material que puede ser usado como reemplazo del 100% virgen en procesos de inyectado.</p>
            </x-form-group>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('formulas.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Crear Fórmula</button>
            </div>
        </form>
    </x-card>
</div>
@endsection