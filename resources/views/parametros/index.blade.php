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
            <form action="{{ route('parametros.fetchTipoCambio') }}" method="POST">
                @csrf
                <button type="submit" class="px-6 py-3 bg-linear-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-xl shadow-md transition font-semibold">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar desde SUNAT
                </button>
            </form>
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
                        <div class="p-6 space-y-6">
                            @foreach($parametros_categoria as $parametro)
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        {{ $parametro->descripcion }}
                                        @if(!$parametro->editable)
                                            <span class="ml-2 text-xs text-red-500 bg-red-50 px-2 py-0.5 rounded-full" title="No se puede editar">Solo lectura</span>
                                        @endif
                                    </label>
                                    <p class="text-xs text-slate-400 mb-2 font-mono">{{ $parametro->codigo_parametro }}</p>
                                    
                                    @if($parametro->tipo === 'NUMERICO')
                                        <input type="number" step="0.001" name="{{ $parametro->codigo_parametro }}" value="{{ $parametro->valor }}" 
                                               class="w-full rounded-xl border {{ $parametro->editable ? 'border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 bg-white' : 'border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed' }} px-4 py-3 outline-none transition"
                                               {{ $parametro->editable ? '' : 'readonly' }}>
                                    @elseif($parametro->tipo === 'BOOLEANO')
                                        <select name="{{ $parametro->codigo_parametro }}" class="w-full rounded-xl border {{ $parametro->editable ? 'border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 bg-white' : 'border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed' }} px-4 py-3 outline-none transition" {{ $parametro->editable ? '' : 'disabled' }}>
                                            <option value="1" {{ $parametro->valor == '1' ? 'selected' : '' }}>SI</option>
                                            <option value="0" {{ $parametro->valor == '0' ? 'selected' : '' }}>NO</option>
                                        </select>
                                    @else
                                        <input type="text" name="{{ $parametro->codigo_parametro }}" value="{{ $parametro->valor }}" 
                                               class="w-full rounded-xl border {{ $parametro->editable ? 'border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 bg-white' : 'border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed' }} px-4 py-3 outline-none transition"
                                               {{ $parametro->editable ? '' : 'readonly' }}>
                                    @endif
                                    
                                    <div class="mt-1 text-xs text-slate-400 text-right">
                                        Actualizado: {{ \Carbon\Carbon::parse($parametro->fecha_actualizacion)->format('d/m/Y H:i') }}
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
@endsection
