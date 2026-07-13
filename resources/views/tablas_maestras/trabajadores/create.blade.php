@extends('layouts.app')
@section('title', 'Nuevo Trabajador')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-page-header title="Registrar Trabajador" subtitle="Alta de nuevo personal o contratista." />

    <x-card class="p-8">
        <form action="{{ route('trabajadores.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <x-form-group label="Código" required :error="$errors->first('codigo')">
                        <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" class="input-field @error('codigo') border-red-500 @enderror" placeholder="Ej: Registrar Nro. DNI" required>
                        @error('codigo')
                            <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                        @enderror
                    </x-form-group>
                </div>

                <div class="md:col-span-2">
                    <x-form-group label="Nombre Completo" required :error="$errors->first('nombre')">
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="input-field @error('nombre') border-red-500 @enderror" placeholder="Apellidos y Nombres" required>
                        @error('nombre')
                            <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                        @enderror
                    </x-form-group>
                </div>

                <div>
                    <x-form-group label="Empresa">
                        <input type="text" name="empresa" id="empresa" value="{{ old('empresa') }}" class="input-field" placeholder="Ej: Leon Plast / Contratistas SAC">
                    </x-form-group>
                </div>

                <div>
                    <x-form-group label="Sueldo Base">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-slate-500 font-medium">S/</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="sueldo" id="sueldo" value="{{ old('sueldo') }}" class="input-field pl-10" placeholder="0.00">
                        </div>
                    </x-form-group>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('trabajadores.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </x-card>
</div>
@endsection