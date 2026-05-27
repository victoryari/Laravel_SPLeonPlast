@extends('layouts.app')
@section('title', 'Nuevo Proveedor')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-page-header title="Registrar Proveedor" subtitle="Alta de nuevo proveedor." />

    <x-card class="p-8">
        <form action="{{ route('proveedores.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <x-form-group label="RUC" required :error="$errors->first('ruc')">
                        <input type="text" name="ruc" id="ruc" value="{{ old('ruc') }}" class="input-field @error('ruc') border-red-500 @enderror" placeholder="Ej: Registrar Nro. RUC" required maxlength="11" pattern="[0-9]{1,11}" title="Solo se permiten números (máximo 11 dígitos)" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        @error('ruc') <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
                    </x-form-group>
                </div>

                <div class="md:col-span-2">
                    <x-form-group label="Razón Social" required :error="$errors->first('razon_social')">
                        <input type="text" name="razon_social" id="razon_social" value="{{ old('razon_social') }}" class="input-field @error('razon_social') border-red-500 @enderror" placeholder="Ingrese la Razón Social" required>
                        @error('razon_social') <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
                    </x-form-group>
                </div>

                <x-form-group label="Nombre Comercial">
                    <input type="text" name="nombre_comercial" id="nombre_comercial" value="{{ old('nombre_comercial') }}" class="input-field">
                </x-form-group>

                <x-form-group label="Dirección">
                    <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" class="input-field">
                </x-form-group>

                <x-form-group label="Teléfono">
                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="input-field" placeholder="Nro. Celular" maxlength="9" pattern="[0-9]{1,9}" title="Solo se permiten números (máximo 9 dígitos)" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </x-form-group>

                <x-form-group label="Email">
                    <input type="text" name="correo" id="correo" value="{{ old('correo') }}" class="input-field" placeholder="example@tuempresa.com" title="Ingrese un formato de correo válido (ejemplo: usuario@empresa.com)">
                </x-form-group>

                <x-form-group label="Contacto">
                    <input type="text" name="contacto" id="contacto" value="{{ old('contacto') }}" class="input-field">
                </x-form-group>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('proveedores.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </x-card>
</div>
@endsection