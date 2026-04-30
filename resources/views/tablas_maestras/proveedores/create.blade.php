@extends('layouts.app')
@section('title', 'Nuevo Proveedor')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Registrar Proveedor</h1>
        <p class="text-sm text-slate-500">Alta de nuevo proveedor.</p>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
        <form action="{{ route('proveedores.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="ruc" class="block text-sm font-bold text-gray-700 mb-2">RUC <span class="text-red-500">*</span></label>
                    <input type="text" name="ruc" id="ruc" value="{{ old('ruc') }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('ruc') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="Ej: Registrar Nro. RUC" required
                           maxlength="11" 
                           pattern="[0-9]{1,11}"
                           title="Solo se permiten números (máximo 11 dígitos)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">         
                    @error('ruc')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="razon_social" class="block text-sm font-bold text-gray-700 mb-2">Razon Social <span class="text-red-500">*</span></label>
                    <input type="text" name="razon_social" id="razon_social" value="{{ old('razon_social') }}" 
                           class="w-full px-4 py-3 rounded-xl border @error('razon_social') border-red-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="Ingrese la Razon Social" required>
                    @error('razon_social')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="nombre_comercial" class="block text-sm font-bold text-gray-700 mb-2">Nombre Comercial <span class="text-red-500"></span></label>
                    <input type="text" name="nombre_comercial" id="nombre_comercial" value="{{ old('nombre_comercial') }}" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" >
                </div>

                <div class="md:col-span-2">
                    <label for="direccion" class="block text-sm font-bold text-gray-700 mb-2">Direccion <span class="text-red-500"></span></label>
                    <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" >
                </div>

                <div>
                    <label for="telefono" class="block text-sm font-bold text-gray-700 mb-2">Telefono <span class="text-xs font-normal text-gray-400">(Opcional)</span></label>
                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="Nro. Celular"
                           maxlength="9" 
                           pattern="[0-9]{1,9}"
                           title="Solo se permiten números (máximo 9 dígitos)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>

                <div>
                    <label for="correo" class="block text-sm font-bold text-gray-700 mb-2">Email <span class="text-xs font-normal text-gray-400">(Opcional)</span></label>
                    <input type="text" name="correo" id="correo" value="{{ old('correo') }}" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="example@tuempresa.com"
                           title="Ingrese un formato de correo válido (ejemplo: usuario@empresa.com)">
                </div>


                <div>
                    <label for="contacto" class="block text-sm font-bold text-gray-700 mb-2">Contacto <span class="text-xs font-normal text-gray-400">(Opcional)</span></label>
                    <input type="text" name="contacto" id="contacto" value="{{ old('contacto') }}" 
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           >
                </div>

            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('proveedores.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md transition font-semibold">Guardar Unidad</button>
            </div>
        </form>
    </div>
</div>
@endsection