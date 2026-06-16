@extends('layouts.app')
@section('title', 'Registrar Merma o Scrap')

@section('content')
<link href="/vendor/select2/select2.min.css" rel="stylesheet" />
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <x-page-header title="Registrar Merma" subtitle="Declare pérdida o molido de un producto">
        <x-slot:actions>
            <a href="{{ route('mermas.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </x-slot:actions>
    </x-page-header>

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-5 rounded shadow-sm" role="alert">
            <p class="font-bold">No se pudo registrar la Merma:</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <form action="{{ route('mermas.store') }}" method="POST">
        @csrf
        <x-card>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form-group class="md:col-span-2" label="Orden de Producción (OP)" required>
                        <select name="id_orden_produccion" id="selectOP" class="w-full" required>
                            <option value="">Seleccione la OP en curso...</option>
                            @foreach($ordenes as $op)
                                <option value="{{ $op->idop }}">
                                    OP-{{ $op->codigo_op }} - {{ $op->descripcion_producto_proceso }} ({{ $op->estado }})
                                </option>
                            @endforeach
                        </select>
                    </x-form-group>

                    <x-form-group class="md:col-span-2" label="Producto Origen (Con Stock Disponible)" required>
                        <select name="codigo_producto" id="selectProducto" class="w-full" required disabled>
                            <option value="">Primero seleccione una OP...</option>
                        </select>
                    </x-form-group>

                    <x-form-group label="Almacén" required>
                        <select name="codigo_almacen" id="selectAlmacen" class="input-field pointer-events-none bg-slate-100" readonly tabindex="-1">
                            @foreach($almacenes as $a)
                                <option value="{{ $a->codigo_almacen }}">{{ $a->descripcion }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">El almacén se auto-asigna según el producto.</p>
                    </x-form-group>

                    <x-form-group label="Cantidad Merma Pura (Irrecuperable)">
                        <input type="number" name="cantidad_pura" id="inputCantidadPura" step="0.01" min="0" class="input-field cantidad-input" placeholder="0.00">
                        <p class="text-[10px] text-slate-500 mt-1">Material que va a la basura.</p>
                    </x-form-group>

                    <x-form-group label="Cantidad Recuperada (Molienda)">
                        <input type="number" name="cantidad_recuperada" id="inputCantidadRecuperada" step="0.01" min="0" class="input-field cantidad-input" placeholder="0.00">
                        <p class="text-[10px] text-slate-500 mt-1">Material que se vuelve a usar.</p>
                    </x-form-group>

                    <div class="md:col-span-2 flex justify-between items-center px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-lg">
                        <span class="text-sm font-medium text-indigo-800">Total a mermar: <span id="totalMermar">0.00</span></span>
                        <span class="text-sm font-medium text-indigo-800" id="maxStockLabel">Max disponible: --</span>
                    </div>

                    <x-form-group class="md:col-span-2" label="Motivo o Descripción (Opcional)">
                        <textarea name="motivo" rows="2" class="input-field" placeholder="Ej: Máquina mal calibrada..."></textarea>
                    </x-form-group>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Registrar Merma
                </button>
            </div>
        </x-card>
    </form>
</div>

<script src="/vendor/jquery/jquery.min.js"></script>
<script src="/vendor/select2/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if(typeof $().select2 !== 'undefined') {
            $('#selectOP, #selectProducto').select2({
                placeholder: 'Seleccione una opción...',
                allowClear: true
            });
        }

        let maxStockDisponible = 0;

        $('#selectOP').on('change', function() {
            let idop = $(this).val();
            let $selectProd = $('#selectProducto');
            
            $selectProd.empty().append('<option value="">Seleccione el producto...</option>');
            $('#selectAlmacen').val('');
            maxStockDisponible = 0;
            actualizarValidacionStock();
            
            if (idop) {
                $selectProd.prop('disabled', false);
                // AJAX call to get products
                $.ajax({
                    url: '{{ route("mermas.productos_por_op") }}',
                    type: 'GET',
                    data: { idop: idop },
                    success: function(data) {
                        if(data.length === 0) {
                            $selectProd.empty().append('<option value="">No hay productos con stock para esta OP</option>');
                        } else {
                            $.each(data, function(index, item) {
                                $selectProd.append(
                                    $('<option></option>')
                                        .val(item.codigo)
                                        .data('almacen', item.codigo_almacen)
                                        .data('stock', item.stock_actual)
                                        .text(item.codigo + ' - ' + item.descripcion + ' (Stock: ' + parseFloat(item.stock_actual).toFixed(2) + ')')
                                );
                            });
                        }
                    }
                });
            } else {
                $selectProd.prop('disabled', true).empty().append('<option value="">Primero seleccione una OP...</option>');
            }
        });

        $('#selectProducto').on('change', function() {
            let option = $(this).find(':selected');
            if (option.val()) {
                $('#selectAlmacen').val(option.data('almacen'));
                maxStockDisponible = parseFloat(option.data('stock'));
                $('#maxStockLabel').text('Max disponible: ' + maxStockDisponible.toFixed(2));
            } else {
                $('#maxStockLabel').text('Max disponible: --');
                maxStockDisponible = 0;
            }
            actualizarValidacionStock();
        });

        $('.cantidad-input').on('input', function() {
            actualizarValidacionStock();
        });

        function actualizarValidacionStock() {
            let pura = parseFloat($('#inputCantidadPura').val()) || 0;
            let recu = parseFloat($('#inputCantidadRecuperada').val()) || 0;
            let total = pura + recu;
            
            $('#totalMermar').text(total.toFixed(2));

            if (total <= 0) {
                $('.cantidad-input').get(0).setCustomValidity('Debe ingresar al menos una cantidad mayor a 0');
            } else {
                $('.cantidad-input').get(0).setCustomValidity('');
            }
        }
    });
</script>
@endsection
