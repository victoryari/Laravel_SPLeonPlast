@extends('layouts.app')
@section('title', 'Parámetros del Sistema')

@section('content')
<div class="min-h-screen bg-slate-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <x-page-header title="Parámetros del Sistema" subtitle="Administra las variables globales y configuraciones clave de LeonPlast" />

        <div class="mb-6 flex justify-between items-center bg-white p-5 rounded-3xl shadow-sm border border-slate-200">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Tipo de Cambio Actual</h3>
                <p class="text-sm text-slate-500">Obtén el tipo de cambio oficial de la SUNAT.</p>
            </div>
            <div class="flex gap-4">
                <button type="button" onclick="abrirModal('modalCrearParametro')" class="px-6 py-3 bg-linear-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl shadow-md transition font-semibold">
                    <i class="fas fa-plus mr-2"></i> Crear Parámetro
                </button>
                <form action="{{ route('parametros.fetchTipoCambio') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-linear-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-xl shadow-md transition font-semibold">
                        <i class="fas fa-sync-alt mr-2"></i> Actualizar desde SUNAT
                    </button>
                </form>
            </div>
        </div>

        <form action="{{ route('parametros.updateBulk') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                @foreach($categorias as $categoria => $parametros_categoria)
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center">
                                <i class="fas fa-sliders-h text-primary mr-2"></i> {{ $categoria }}
                            </h2>
                        </div>
                        <div class="p-4 space-y-3">
                            @foreach($parametros_categoria as $parametro)
                                <div class="flex items-center justify-between bg-slate-50 border border-slate-100 p-3 rounded-xl hover:bg-slate-100 transition">
                                    <div class="w-1/2 pr-3">
                                        <label class="block text-xs font-bold text-slate-700 truncate" title="{{ $parametro->descripcion }}">
                                            {{ $parametro->descripcion }}
                                            @if(!$parametro->editable)
                                                <span class="ml-1 text-[10px] text-red-500 bg-red-50 px-1.5 py-0.5 rounded-full" title="No se puede editar">Fijo</span>
                                            @endif
                                        </label>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $parametro->codigo_parametro }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="w-1/2 flex flex-col items-end">
                                        @if($parametro->tipo === 'NUMERICO')
                                            <input type="number" step="0.001" name="{{ $parametro->codigo_parametro }}" value="{{ $parametro->valor }}" 
                                                   class="w-full max-w-[150px] text-right rounded-md border {{ $parametro->editable ? 'border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary/50 bg-white' : 'border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed' }} px-2 py-1.5 text-xs font-semibold outline-none transition"
                                                   {{ $parametro->editable ? '' : 'readonly' }}>
                                        @elseif($parametro->tipo === 'BOOLEANO')
                                            <select name="{{ $parametro->codigo_parametro }}" class="w-full max-w-[150px] rounded-md border {{ $parametro->editable ? 'border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary/50 bg-white' : 'border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed' }} px-2 py-1.5 text-xs font-semibold outline-none transition" {{ $parametro->editable ? '' : 'disabled' }}>
                                                <option value="1" {{ $parametro->valor == '1' ? 'selected' : '' }}>SI</option>
                                                <option value="0" {{ $parametro->valor == '0' ? 'selected' : '' }}>NO</option>
                                            </select>
                                        @else
                                            <input type="text" name="{{ $parametro->codigo_parametro }}" value="{{ $parametro->valor }}" 
                                                   class="w-full text-right rounded-md border {{ $parametro->editable ? 'border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary/50 bg-white' : 'border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed' }} px-2 py-1.5 text-xs font-semibold outline-none transition"
                                                   {{ $parametro->editable ? '' : 'readonly' }}>
                                        @endif
                                        <span class="text-[9px] text-slate-400 mt-1" title="Última actualización">Act: {{ \Carbon\Carbon::parse($parametro->fecha_actualizacion)->format('d/m/y H:i') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-8 py-4 bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl shadow-xl transition font-bold text-lg">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Modal Crear Parámetro -->
<x-modal id="modalCrearParametro" title="Nuevo Parámetro del Sistema">
    <form action="{{ route('parametros.store') }}" method="POST" id="formCrearParametro" class="space-y-4">
        @csrf
        <x-form-group label="Código del Parámetro (Ej. DOC_TICKET)" required>
            <input type="text" name="codigo_parametro" class="input-field uppercase" required>
        </x-form-group>
        <x-form-group label="Descripción" required>
            <input type="text" name="descripcion" class="input-field" required>
        </x-form-group>
        <x-form-group label="Valor" required>
            <input type="text" name="valor" class="input-field" required>
        </x-form-group>
        <div class="grid grid-cols-2 gap-4">
            <x-form-group label="Categoría" required>
                <input type="text" name="categoria" class="input-field uppercase" list="categoriasList" required>
                <datalist id="categoriasList">
                    @foreach($categorias->keys() as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
            </x-form-group>
            <x-form-group label="Tipo" required>
                <select name="tipo" class="input-field" required>
                    <option value="TEXTO">TEXTO</option>
                    <option value="NUMERICO">NUMERICO</option>
                    <option value="BOOLEANO">BOOLEANO</option>
                </select>
            </x-form-group>
        </div>
        <x-slot:footer>
            <button type="submit" form="formCrearParametro" class="btn-primary w-full">
                <i class="fas fa-save"></i> Guardar Parámetro
            </button>
        </x-slot:footer>
    </form>
</x-modal>

<script>
    window.cerrarModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            const form = document.getElementById('form-' + id);
            if (form) form.reset();
        }
    };
    window.abrirModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    };
</script>
@endsection
