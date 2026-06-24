@extends('layouts.app')
@section('title', 'Registrar Merma o Scrap')

@section('content')
<link href="/vendor/select2/select2.min.css" rel="stylesheet" />
<style>
    /* Custom Select2 Styling para coincidir con Tailwind */
    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        height: 3.125rem;
        padding: 0.6rem 1rem;
        transition: all 0.2s;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155;
        line-height: 1.75rem;
        padding-left: 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 3rem;
        right: 0.75rem;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #4f46e5;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        outline: none;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-top: 4px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.5rem;
        outline: none;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #4f46e5;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #f8fafc;
        color: #4f46e5;
        font-weight: 500;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #e0e7ff;
        color: #3730a3;
    }
</style>
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

                    <x-form-group class="md:col-span-2" label="Proceso de la OP" required>
                        <select name="id_proceso" id="selectProceso" class="w-full" required disabled>
                            <option value="">Primero seleccione una OP...</option>
                        </select>
                    </x-form-group>

                    <x-form-group class="md:col-span-2" label="Producto Origen (Con Stock Disponible)" required>
                        <select name="codigo_producto" id="selectProducto" class="w-full" required disabled>
                            <option value="">Primero seleccione un proceso...</option>
                        </select>
                        <div class="mt-2" id="wrapperLimpiezaMaquina" style="display: none;">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="chkLimpiezaMaquina" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700 font-medium">Merma por Limpieza de Máquina (Consumo proporcional de insumos)</span>
                            </label>
                        </div>
                    </x-form-group>

                    <x-form-group label="Almacén" required>
                        <select name="codigo_almacen" id="selectAlmacen" class="w-full rounded-xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all shadow-sm pointer-events-none" readonly tabindex="-1">
                            @foreach($almacenes as $a)
                                <option value="{{ $a->codigo_almacen }}">{{ $a->descripcion }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">El almacén se auto-asigna según el producto.</p>
                    </x-form-group>

                    <div id="sectionMermaEstandar" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5 w-full">
                        <x-form-group label="Cantidad Merma Pura (Irrecuperable)">
                            <input type="number" name="cantidad_pura" id="inputCantidadPura" step="0.01" min="0" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all shadow-sm cantidad-input" placeholder="0.00">
                            <p class="text-[10px] text-slate-500 mt-1">Material que va a la basura.</p>
                        </x-form-group>

                        <x-form-group label="Cantidad Recuperada (Molienda)">
                            <input type="number" name="cantidad_recuperada" id="inputCantidadRecuperada" step="0.01" min="0" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all shadow-sm cantidad-input" placeholder="0.00">
                            <p class="text-[10px] text-slate-500 mt-1">Material que se vuelve a usar.</p>
                            <div class="mt-3">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="es_molido" value="1" id="chkEsMolido" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-300 focus:ring focus:ring-offset-0 focus:ring-emerald-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 font-medium">☑ Material recuperado ya molido en máquina</span>
                                </label>
                            </div>
                        </x-form-group>

                        <div class="md:col-span-2 flex justify-between items-center px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-lg">
                            <span class="text-sm font-medium text-indigo-800">Total a mermar: <span id="totalMermar">0.00</span></span>
                            <span class="text-sm font-medium text-indigo-800" id="maxStockLabel">Max disponible: --</span>
                        </div>
                    </div>

                    <div id="sectionComponentesFormula" class="md:col-span-2 hidden">
                        <h3 class="text-md font-bold mb-3 text-slate-700 border-b pb-2" id="titleComponentes">Distribución de Insumos</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-slate-100">
                                        <th class="p-2 border">Componente</th>
                                        <th class="p-2 border">Stock en Almacén</th>
                                        <th class="p-2 border">Merma Pura</th>
                                        <th class="p-2 border">Merma Recuperada</th>
                                        <th class="p-2 border text-center col-action" style="display:none;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyComponentesEnsamblado">
                                    <tr>
                                        <td colspan="5" class="p-4 text-center text-slate-500">Seleccione el producto de origen...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-slate-500 mt-2" id="helpTextComponentes"><i class="fas fa-info-circle"></i> Los descuentos se harán directamente de los stocks de cada componente indicado.</p>
                    </div>

                    <input type="hidden" name="es_ensamblado" id="es_ensamblado_flag" value="0">

                    <x-form-group class="md:col-span-2" label="Motivo o Descripción (Opcional)">
                        <textarea name="motivo" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all shadow-sm" placeholder="Ej: Máquina mal calibrada..."></textarea>
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
            $('#selectOP, #selectProceso, #selectProducto').select2({
                placeholder: 'Seleccione una opción...',
                allowClear: true
            });
        }

        let maxStockDisponible = 0;
        let formulaComponentes = [];
        let isEnsambladoMode = false;
        let isLimpiezaMode = false;

        $('#selectOP').on('change', function() {
            let idop = $(this).val();
            let $selectProc = $('#selectProceso');
            let $selectProd = $('#selectProducto');
            
            $selectProc.empty().append('<option value="">Seleccione el proceso...</option>');
            $selectProd.empty().append('<option value="">Primero seleccione un proceso...</option>');
            $('#selectAlmacen').val('');
            maxStockDisponible = 0;
            actualizarValidacionStock();
            
            if (idop) {
                $selectProc.prop('disabled', false);
                $selectProd.prop('disabled', true);
                
                $.ajax({
                    url: '{{ route("mermas.procesos_por_op") }}',
                    type: 'GET',
                    data: { idop: idop },
                    success: function(data) {
                        if(data.length === 0) {
                            $selectProc.empty().append('<option value="">No hay procesos para esta OP</option>');
                        } else {
                            $.each(data, function(index, item) {
                                $selectProc.append(
                                    $('<option></option>')
                                        .val(item.id)
                                        .text(item.descripcion_completa)
                                );
                            });
                        }
                    }
                });
            } else {
                $selectProc.prop('disabled', true).empty().append('<option value="">Primero seleccione una OP...</option>');
                $selectProd.prop('disabled', true).empty().append('<option value="">Primero seleccione un proceso...</option>');
            }
        });

        $('#selectProceso').on('change', function() {
            let idop = $('#selectOP').val();
            let id_proceso = $(this).val();
            let processText = $(this).find('option:selected').text().toUpperCase();
            isEnsambladoMode = processText.includes('ENSAMBLADO');
            
            if (isEnsambladoMode) {
                $('#wrapperLimpiezaMaquina').hide();
                $('#chkLimpiezaMaquina').prop('checked', false);
                isLimpiezaMode = false;
                
                $('#sectionMermaEstandar').hide();
                $('#sectionComponentesFormula').show();
                $('#es_ensamblado_flag').val('1');
                $('.col-action').hide();
                $('#titleComponentes').text('Registro de Merma por Componente (Ensamblado)');
                $('#helpTextComponentes').html('<i class="fas fa-info-circle"></i> Ingrese las cantidades a mermar de cada componente manualamente.');
            } else {
                $('#wrapperLimpiezaMaquina').show();
                $('#chkLimpiezaMaquina').prop('checked', false);
                isLimpiezaMode = false;
                
                $('#sectionMermaEstandar').show();
                $('#sectionComponentesFormula').hide();
                $('#es_ensamblado_flag').val('0');
                $('.col-action').hide();
            }

            let $selectProd = $('#selectProducto');
            
            $selectProd.empty().append('<option value="">Seleccione el producto...</option>');
            $('#selectAlmacen').val('');
            maxStockDisponible = 0;
            actualizarValidacionStock();
            
            if (id_proceso) {
                $selectProd.prop('disabled', false);
                $.ajax({
                    url: '{{ route("mermas.productos_por_op") }}',
                    type: 'GET',
                    data: { idop: idop, id_proceso: id_proceso },
                    success: function(data) {
                        if(data.length === 0) {
                            $selectProd.empty().append('<option value="">No hay productos con stock para este proceso</option>');
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
                $selectProd.prop('disabled', true).empty().append('<option value="">Primero seleccione un proceso...</option>');
            }
        });

        $('#selectProducto').on('change', function() {
            let option = $(this).find(':selected');

            if (option.val()) {
                $('#selectAlmacen').val(option.data('almacen'));
                maxStockDisponible = parseFloat(option.data('stock'));
                $('#maxStockLabel').text('Max disponible: ' + maxStockDisponible.toFixed(2));
                
                let idop = $('#selectOP').val();
                let id_proceso = $('#selectProceso').val();
                let codigo_almacen = option.data('almacen');
                
                $.ajax({
                    url: '{{ route("mermas.componentes_ensamblado") }}',
                    type: 'GET',
                    data: { idop: idop, id_proceso: id_proceso, codigo_almacen: codigo_almacen },
                    success: function(data) {
                        formulaComponentes = data.map(c => ({...c, activo: true}));
                        if (isEnsambladoMode || isLimpiezaMode) {
                            renderComponentesTable();
                        }
                    }
                });
            } else {
                $('#maxStockLabel').text('Max disponible: --');
                maxStockDisponible = 0;
                formulaComponentes = [];
                if (isEnsambladoMode || isLimpiezaMode) {
                    $('#tbodyComponentesEnsamblado').html('<tr><td colspan="5" class="p-4 text-center text-slate-500">Seleccione el producto de origen...</td></tr>');
                }
            }
            actualizarValidacionStock();
        });

        $('#chkLimpiezaMaquina').on('change', function() {
            isLimpiezaMode = $(this).is(':checked');
            if (isLimpiezaMode) {
                $('#sectionComponentesFormula').show();
                $('#es_ensamblado_flag').val('1');
                $('.col-action').show();
                $('#titleComponentes').text('Distribución Proporcional de Insumos (Limpieza)');
                $('#helpTextComponentes').html('<i class="fas fa-info-circle"></i> Excluya los insumos que no participan en la limpieza. Las cantidades se calcularán automáticamente según la fórmula.');
                renderComponentesTable();
            } else {
                $('#sectionComponentesFormula').hide();
                $('#es_ensamblado_flag').val('0');
                $('.col-action').hide();
            }
            actualizarValidacionStock();
        });

        function renderComponentesTable() {
            let $tbody = $('#tbodyComponentesEnsamblado');
            if (formulaComponentes.length === 0) {
                $tbody.html('<tr><td colspan="5" class="text-center p-4 text-red-500">No se encontraron componentes en el almacén</td></tr>');
                return;
            }
            
            $tbody.empty();
            $.each(formulaComponentes, function(index, comp) {
                if (!comp.activo && isLimpiezaMode) return; // Hide completely if removed in limpieza mode

                let html = '<tr>';
                html += '<td class="p-2 border">' + comp.codigo_producto + '<br><span class="text-xs text-slate-500">' + comp.descripcion + '</span></td>';
                html += '<td class="p-2 border font-bold text-center">' + parseFloat(comp.stock_actual).toFixed(2) + ' <span class="text-xs text-slate-500 font-normal">' + (comp.codigo_unidad_medida || '') + '</span></td>';
                
                let readOnlyAttr = isLimpiezaMode ? 'readonly tabindex="-1" class="w-full border-gray-200 bg-gray-50 rounded-md py-1 px-2 pr-8 text-sm text-gray-500 pointer-events-none"' : 'class="w-full border-gray-300 rounded-md py-1 px-2 pr-8 text-sm cantidad-input"';
                
                let namePura = comp.activo ? `name="componentes[${comp.codigo_producto}][pura]"` : '';
                let nameRecu = comp.activo ? `name="componentes[${comp.codigo_producto}][recuperada]"` : '';

                html += `<td class="p-2 border"><div class="relative"><input type="number" ${namePura} id="calc_pura_${$.escapeSelector(comp.codigo_producto)}" step="0.01" min="0" max="${comp.stock_actual}" ${readOnlyAttr} placeholder="0.00"><span class="absolute right-2 top-1.5 text-xs text-slate-400">${comp.codigo_unidad_medida || ''}</span></div></td>`;
                html += `<td class="p-2 border"><div class="relative"><input type="number" ${nameRecu} id="calc_recu_${$.escapeSelector(comp.codigo_producto)}" step="0.01" min="0" max="${comp.stock_actual}" ${readOnlyAttr} placeholder="0.00"><span class="absolute right-2 top-1.5 text-xs text-slate-400">${comp.codigo_unidad_medida || ''}</span></div></td>`;
                
                if (isLimpiezaMode) {
                    html += `<td class="p-2 border text-center col-action"><button type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" onclick="toggleComponente('${comp.codigo_producto}')" title="Excluir componente"><i class="fas fa-trash-alt"></i></button></td>`;
                } else {
                    html += `<td class="p-2 border text-center col-action" style="display:none;"></td>`;
                }
                
                html += '</tr>';
                $tbody.append(html);
            });
            
            if (isLimpiezaMode) {
                actualizarDistribucionLimpieza();
            }
        }

        window.toggleComponente = function(codigo) {
            let comp = formulaComponentes.find(c => c.codigo_producto === codigo);
            if (comp) {
                comp.activo = !comp.activo;
                renderComponentesTable();
            }
        };

        function actualizarDistribucionLimpieza() {
            if (!isLimpiezaMode) return;
            
            let totalPura = parseFloat($('#inputCantidadPura').val()) || 0;
            let totalRecu = parseFloat($('#inputCantidadRecuperada').val()) || 0;
            
            let sumCantidadTotal = formulaComponentes.filter(c => c.activo).reduce((sum, c) => sum + parseFloat(c.cantidad_total || 0), 0);
            
            if (sumCantidadTotal > 0) {
                formulaComponentes.forEach(comp => {
                    if (comp.activo) {
                        let ratio = parseFloat(comp.cantidad_total || 0) / sumCantidadTotal;
                        let valPura = (totalPura * ratio).toFixed(4);
                        let valRecu = (totalRecu * ratio).toFixed(4);
                        
                        $(`#calc_pura_${$.escapeSelector(comp.codigo_producto)}`).val(valPura > 0 ? valPura : '');
                        $(`#calc_recu_${$.escapeSelector(comp.codigo_producto)}`).val(valRecu > 0 ? valRecu : '');
                    }
                });
            }
        }

        $(document).on('input', '.cantidad-input', function() {
            actualizarValidacionStock();
            actualizarDistribucionLimpieza();
        });

        function actualizarValidacionStock() {
            let isEnsamblado = $('#es_ensamblado_flag').val() === '1';
            
            if (isEnsamblado) {
                // Clear validation on standard inputs so they don't block submission
                $('.cantidad-input').get(0).setCustomValidity('');
            } else {
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
        }
    });
</script>
@endsection
