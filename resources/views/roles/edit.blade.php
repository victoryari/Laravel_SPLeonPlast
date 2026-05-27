@extends('layouts.app')
@section('title', 'Editar Rol')

@section('content')
<div class="container mx-auto pb-10 max-w-5xl">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Editar Rol</h1>
            <p class="text-sm text-gray-600">Modificando accesos para: <span class="font-bold text-blue-600">{{ $role->nombre }}</span></p>
        </div>
        <a href="{{ route('roles.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition flex items-center w-fit">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Roles
        </a>
    </div>

    <form action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Datos del Rol --}}
            <div class="lg:col-span-4">
                <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500 sticky top-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2"><i class="fas fa-info-circle text-blue-500 mr-2"></i>Datos Principales</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">
                                Nombre del Rol <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $role->nombre) }}" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-primary focus:border-primary {{ $errors->has('nombre') ? 'border-red-500' : 'border-gray-300' }}" 
                                   {{ $role->nombre === 'Administrador' ? 'readonly' : 'required' }}>
                            @if($role->nombre === 'Administrador')
                                <p class="text-xs text-amber-600 mt-1"><i class="fas fa-exclamation-triangle"></i> El nombre de este rol no puede ser modificado.</p>
                            @endif
                            @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="descripcion" class="block text-sm font-bold text-gray-700 mb-1">
                                Descripción <span class="text-xs text-gray-400 font-normal">(Opcional)</span>
                            </label>
                            <textarea name="descripcion" id="descripcion" rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">{{ old('descripcion', $role->descripcion) }}</textarea>
                            @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col gap-3">
                        <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-primary-dark shadow-md transition font-bold flex items-center justify-center gap-2">
                            <i class="fas fa-sync-alt"></i> Actualizar Rol y Permisos
                        </button>
                    </div>
                </div>
            </div>

            {{-- Selección de Permisos --}}
            <div class="lg:col-span-8">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-slate-50 border-b border-slate-200 p-6">
                        <h2 class="text-lg font-bold text-slate-800"><i class="fas fa-check-square text-blue-500 mr-2"></i>Permisos de Acceso a Módulos</h2>
                        <p class="text-sm text-slate-500 mt-1">Seleccione las áreas del sistema a las que este rol tendrá acceso.</p>
                    </div>
                    
                    <div class="p-6">
                        @if($errors->has('modulos'))
                            <div class="p-3 bg-red-50 text-red-600 text-sm rounded-lg mb-6 border border-red-200">
                                {{ $errors->first('modulos') }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($modulosGrupados as $grupo => $modulos)
                                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                    <h3 class="font-bold text-slate-700 mb-3 flex items-center justify-between border-b border-slate-200 pb-2">
                                        {{ $grupo ?: 'Otros Módulos' }}
                                        <label class="flex items-center gap-2 text-xs font-normal cursor-pointer text-blue-600 hover:text-blue-800">
                                            <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 rounded select-all-grupo" data-target="grupo-{{ Str::slug($grupo) }}">
                                            Todos
                                        </label>
                                    </h3>
                                    
                                    <div class="space-y-2 pl-1 grupo-{{ Str::slug($grupo) }}">
                                        @foreach($modulos as $modulo)
                                            <label class="flex items-center p-2 rounded hover:bg-slate-100 cursor-pointer transition">
                                                <input type="checkbox" name="modulos[]" value="{{ $modulo->id }}" 
                                                    class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                    {{ (is_array(old('modulos')) && in_array($modulo->id, old('modulos'))) || (empty(old('modulos')) && in_array($modulo->id, $rolModulos)) ? 'checked' : '' }}>
                                                <span class="ml-3 text-sm text-slate-700 flex items-center gap-2">
                                                    @if($modulo->icono) <i class="{{ $modulo->icono }} text-slate-400 w-4 text-center"></i> @endif
                                                    {{ $modulo->nombre }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar estado de los checkboxes "Todos"
        document.querySelectorAll('.select-all-grupo').forEach(checkbox => {
            const targetClass = checkbox.getAttribute('data-target');
            const groupCheckboxes = document.querySelectorAll(`.${targetClass} input[type="checkbox"]`);
            
            // Comprobar si todos están checked al cargar
            let allChecked = true;
            groupCheckboxes.forEach(cb => {
                if (!cb.checked) allChecked = false;
            });
            checkbox.checked = groupCheckboxes.length > 0 && allChecked;

            // Escuchar cambios en el "Todos"
            checkbox.addEventListener('change', function() {
                groupCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
            });

            // Escuchar cambios en individuales para actualizar el "Todos"
            groupCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    let anyUnchecked = false;
                    groupCheckboxes.forEach(c => {
                        if (!c.checked) anyUnchecked = true;
                    });
                    checkbox.checked = !anyUnchecked;
                });
            });
        });
    });
</script>
@endsection
