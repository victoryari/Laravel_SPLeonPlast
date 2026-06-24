@extends('layouts.app')
@section('title', 'Editar Ajuste #' . $ajuste->id_kardex)

@section('content')
<div class="min-h-screen bg-slate-50 py-10 px-4">
    <div class="max-w-3xl mx-auto">

        <div class="mb-6">
            <a href="{{ route('inventario.ajuste.lista') }}"
                class="text-sm text-primary hover:text-primary-dark transition">
                <i class="fas fa-arrow-left mr-1"></i> Volver a la Bandeja de Ajustes
            </a>
        </div>

        <div class="rounded-3xl bg-white shadow-xl border border-slate-200 overflow-hidden">
            <div class="h-2 bg-linear-to-r from-amber-500 to-orange-500"></div>

            <div class="p-8">
                <h1 class="text-2xl font-bold text-slate-800 mb-2">Editar Ajuste #{{ $ajuste->id_kardex }}</h1>
                <p class="text-sm text-slate-500 mb-8">
                    Documento: <span class="font-mono">{{ $ajuste->numero_documento }}</span> |
                    Producto: {{ $ajuste->codigo_producto }} - {{ $ajuste->producto }} |
                    Almacén: {{ $ajuste->almacen }}
                </p>

                <form action="{{ route('inventario.ajuste.update', $ajuste->id_kardex) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3 mb-6">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold text-amber-800">Información importante</p>
                            <p class="text-xs text-amber-700 mt-1">
                                Si ya existen movimientos posteriores para este producto/almacén, al cambiar la cantidad
                                se recalcularán automáticamente los saldos de todos los movimientos siguientes y el stock actual.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Lote y Costo Unitario (Lectura) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Lote</label>
                            <input type="text" value="{{ $ajuste->lote }}" readonly
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 bg-slate-100 text-slate-600 outline-none cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Costo Unitario (S/.)</label>
                            <input type="text" value="{{ $ajuste->cantidad_entrada > 0 ? $ajuste->costo_entrada : $ajuste->costo_salida }}" readonly
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 bg-slate-100 text-slate-600 outline-none cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Cantidad</label>
                            <input type="number" name="cantidad" step="0.01" min="0.01"
                                value="{{ $ajuste->cantidad_entrada > 0 ? $ajuste->cantidad_entrada : $ajuste->cantidad_salida }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Unidad de Medida</label>
                            <select name="codigo_unidad_medida"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                                required>
                                <option value="">-- Seleccione --</option>
                                @foreach($unidadesMedida as $u)
                                    <option value="{{ $u->codigo }}" {{ $ajuste->codigo_unidad_medida == $u->codigo ? 'selected' : '' }}>
                                        {{ $u->codigo }} - {{ $u->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de operación</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 px-4 py-3 rounded-xl border cursor-pointer transition
                                {{ $ajuste->cantidad_entrada > 0 ? 'border-green-300 bg-green-50' : 'border-slate-300' }}">
                                <input type="radio" name="tipo" value="INGRESO"
                                    {{ $ajuste->cantidad_entrada > 0 ? 'checked' : '' }}
                                    class="text-green-600 focus:ring-green-500">
                                <span class="text-sm font-semibold text-green-700">INGRESO (+)</span>
                            </label>
                            <label class="flex items-center gap-2 px-4 py-3 rounded-xl border cursor-pointer transition
                                {{ $ajuste->cantidad_salida > 0 ? 'border-red-300 bg-red-50' : 'border-slate-300' }}">
                                <input type="radio" name="tipo" value="SALIDA"
                                    {{ $ajuste->cantidad_salida > 0 ? 'checked' : '' }}
                                    class="text-red-600 focus:ring-red-500">
                                <span class="text-sm font-semibold text-red-700">SALIDA (-)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Motivo / Observaciones</label>
                        <textarea name="observaciones" rows="3"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">{{ $ajuste->observaciones }}</textarea>
                    </div>

                    <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                        <a href="{{ route('inventario.ajuste.lista') }}"
                            class="px-6 py-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="px-6 py-3 rounded-xl bg-linear-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold shadow-lg transition">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
