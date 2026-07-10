@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css" rel="stylesheet">
@endpush
@extends('layouts.app')
@section('title', 'Nueva Regla de Mapeo')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Nueva Regla de Mapeo</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Vincular un producto de salida con su equivalente de retorno.</p>
        </div>
        <a href="{{ route('mapeo-terceros.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>

    @if(session('error'))
    <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-red-500"></i>
        <span class="font-medium text-sm">{{ session('error') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form action="{{ route('mapeo-terceros.store') }}" method="POST">
            @csrf

            <div class="space-y-6">
                <!-- Origen -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Producto Origen (El que se envía) <span class="text-red-500">*</span>
                    </label>
                    <select id="origen" name="codigo_producto_origen" class="w-full" required>
                        <option value="">Seleccione el producto que sale...</option>
                        @foreach($productosOrigen as $prod)
                            <option value="{{ $prod->codigo }}" {{ old('codigo_producto_origen') == $prod->codigo ? 'selected' : '' }}>
                                {{ $prod->codigo }} - {{ $prod->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    @error('codigo_producto_origen')
                        <p class="text-red-500 text-xs font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Destino -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Producto Destino (El que retorna transformado) <span class="text-red-500">*</span>
                    </label>
                    <select id="destino" name="codigo_producto_destino" class="w-full" required>
                        <option value="">Seleccione el producto que retorna...</option>
                        @foreach($productosDestino as $prod)
                            <option value="{{ $prod->codigo }}" {{ old('codigo_producto_destino') == $prod->codigo ? 'selected' : '' }}>
                                {{ $prod->codigo }} - {{ $prod->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    @error('codigo_producto_destino')
                        <p class="text-red-500 text-xs font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Proceso -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Descripción del Proceso (Opcional)</label>
                    <input type="text" name="descripcion_proceso" value="{{ old('descripcion_proceso') }}" 
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none" 
                           placeholder="Ej. Horneado y Tropicalizado">
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end gap-3">
                <a href="{{ route('mapeo-terceros.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-sm font-semibold transition-all shadow-sm shadow-primary/20 flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    Guardar Regla
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const config = {
            create: false,
            sortField: { field: "text", direction: "asc" }
        };
        
        new TomSelect("#origen", config);
        new TomSelect("#destino", config);
    });
</script>
@endpush
@endsection
