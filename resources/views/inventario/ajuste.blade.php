@extends('layouts.app')
@section('title', 'Ajuste Manual de Inventario')

@section('content')
<link href="/vendor/select2/select2.min.css" rel="stylesheet" />
<div class="min-h-screen bg-slate-50 py-10 px-4">
    <div class="max-w-3xl mx-auto">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-800">
                Ajuste Manual de Inventario
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Registra correcciones por merma, sobrante o validación de inventario físico.
            </p>
        </div>

        <!-- Card -->
        <div class="rounded-3xl bg-white shadow-xl border border-slate-200 overflow-hidden">
            
            <!-- Top Accent -->
            <div class="h-2 bg-linear-to-r from-blue-600 to-indigo-600"></div>

            <form action="{{ route('inventario.store_ajuste') }}" method="POST" class="p-8 space-y-6">
                @csrf

                <!-- Producto -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Producto
                    </label>
                    <select name="codigo_producto" id="selectProducto"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                        required>
                        <option value="">-- Buscar producto --</option>
                        @foreach($productos as $p)
                            <option value="{{ $p->codigo }}">
                                {{ $p->codigo }} - {{ $p->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Almacén -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Almacén afectado
                    </label>
                    <select name="codigo_almacen"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                        required>
                        <option value="">-- Seleccione --</option>
                        @foreach($almacenes as $a)
                            <option value="{{ $a->codigo_almacen }}">
                                {{ $a->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Tipo de operación
                    </label>
                    <select name="tipo" id="selectTipo"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                        required>
                        <option value="INGRESO">INGRESO (+)</option>
                        <option value="SALIDA">SALIDA (-)</option>
                    </select>
                </div>

                <!-- Lote y Costo -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Lote
                        </label>
                        <!-- Para Ingresos -->
                        <input type="text" name="lote" id="inputLote" placeholder="Ej. LOTE-001"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                            required>
                        <!-- Para Salidas -->
                        <select name="lote_select" id="selectLote" style="display:none;"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <option value="">-- Seleccione lote --</option>
                        </select>
                    </div>
                    <div id="divCosto">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Costo Unitario (S/.)
                        </label>
                        <input type="number" name="costo_unitario" id="inputCosto" step="0.0001" min="0"
                            placeholder="Automático o Ingresar"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    </div>
                </div>

                <!-- Cantidad + Unidad Medida -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Cantidad
                        </label>
                        <input type="number" name="cantidad" step="0.01" min="0.01"
                            placeholder="0.00"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Unidad de Medida
                        </label>
                        <select name="codigo_unidad_medida"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                            required>
                            <option value="">-- Seleccione --</option>
                            @foreach($unidadesMedida as $u)
                                <option value="{{ $u->codigo }}">
                                    {{ $u->codigo }} - {{ $u->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Motivo -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Motivo del ajuste
                    </label>
                    <textarea name="observaciones" rows="3"
                        placeholder="Escriba el motivo del ajuste..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                        required></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                    <a href="{{ route('inventario.index') }}"
                        class="px-6 py-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="px-6 py-3 rounded-xl bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold shadow-lg transition">
                        <i class="fas fa-save mr-2"></i>
                        Procesar Ajuste
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="/vendor/jquery/jquery.min.js"></script>
<script src="/vendor/select2/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if (typeof $().select2 !== 'undefined') {
            $('#selectProducto').select2({
                placeholder: "-- Buscar producto --",
                allowClear: true,
                width: '100%'
            });
        }

        function updateLoteField() {
            const tipo = $('#selectTipo').val();
            if (tipo === 'SALIDA') {
                $('#inputLote').removeAttr('name').hide();
                $('#selectLote').attr('name', 'lote').show().prop('required', true);
                $('#divCosto').hide();
                $('#inputCosto').val('');
                cargarLotes();
            } else {
                $('#selectLote').removeAttr('name').hide().prop('required', false);
                $('#inputLote').attr('name', 'lote').show();
                $('#divCosto').show();
            }
        }

        function cargarLotes() {
            const producto = $('#selectProducto').val();
            const almacen = $('select[name="codigo_almacen"]').val();
            
            if (!producto || $('#selectTipo').val() !== 'SALIDA') return;
            
            $('#selectLote').html('<option value="">Cargando...</option>');
            $.get('/lotes-ajax', { producto: producto, almacen: almacen }, function(data) {
                $('#selectLote').empty();
                if(data.length === 0) {
                    $('#selectLote').append('<option value="">Sin lotes con stock</option>');
                } else {
                    $('#selectLote').append('<option value="">-- Seleccione lote --</option>');
                    data.forEach(function(lote) {
                        $('#selectLote').append(`<option value="${lote.lote}">${lote.lote} (Stock: ${lote.stock_actual})</option>`);
                    });
                }
            });
        }

        $('#selectTipo, select[name="codigo_almacen"]').on('change', updateLoteField);
        $('#selectProducto').on('change', cargarLotes);
        
        updateLoteField();
    });
</script>
@endsection