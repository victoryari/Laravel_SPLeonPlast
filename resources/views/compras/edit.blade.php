@extends('layouts.app')
@section('title', 'Editar Compra')

@section('content')
<link href="/vendor/select2/select2.min.css" rel="stylesheet" />
<div class="container mx-auto px-4 py-6 max-w-7xl">

    <x-page-header title="Editar Compra" subtitle="Modifique los datos del comprobante o el detalle de los insumos.">
        <x-slot:actions>
            <a href="{{ route('compras.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ route('compras.update', $compra->id_compra) }}" method="POST" id="formCompra">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                <x-card>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-primary"></i> Datos del Comprobante
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                            <x-form-group class="md:col-span-3" label="Tipo Doc." required>
                                <select name="tipo_documento" class="input-field" required>
                                    @foreach($tipos_comprobante as $tc)
                                        <option value="{{ $tc->valor }}" {{ $compra->tipo_documento == $tc->valor ? 'selected' : '' }}>{{ $tc->descripcion }}</option>
                                    @endforeach
                                </select>
                            </x-form-group>
                            <x-form-group class="md:col-span-2" label="Serie" required>
                                <input type="text" name="serie_documento" value="{{ $compra->serie_documento }}" class="input-field uppercase" required>
                            </x-form-group>
                            <x-form-group class="md:col-span-3" label="N° Documento" required>
                                <input type="text" name="numero_documento" value="{{ $compra->numero_documento }}" class="input-field" required>
                            </x-form-group>

                            <x-form-group class="md:col-span-2" label="Fecha de Emisión" required>
                                <input type="date" name="fecha_compra" value="{{ $compra->fecha_compra }}" class="input-field" required>
                            </x-form-group>

                            <x-form-group class="md:col-span-2" label="Moneda" required>
                                <select name="moneda" id="selectMoneda" class="input-field" required>
                                    <option value="PEN" {{ $compra->moneda == 'PEN' ? 'selected' : '' }}>Soles (PEN)</option>
                                    <option value="USD" {{ $compra->moneda == 'USD' ? 'selected' : '' }}>Dólares (USD)</option>
                                </select>
                            </x-form-group>

                            <x-form-group class="md:col-span-8" label="Proveedor" required>
                                <select name="ruc_proveedor" id="selectProveedor" class="input-field" required>
                                    @foreach($proveedores as $p)
                                        <option value="{{ $p->ruc }}" {{ $compra->ruc_proveedor == $p->ruc ? 'selected' : '' }}>{{ $p->ruc }} - {{ $p->razon_social }}</option>
                                    @endforeach
                                </select>
                            </x-form-group>

                            <x-form-group class="md:col-span-4" label="Tipo de Cambio" id="groupTipoCambio" style="display: {{ $compra->moneda == 'USD' ? 'block' : 'none' }};">
                                <input type="number" name="tipo_cambio" id="inputTipoCambio" step="0.001" min="0" class="input-field" placeholder="Ej. 3.800" value="{{ $compra->tipo_cambio ?? '1.000' }}" {{ $compra->moneda == 'USD' ? 'required' : '' }}>
                            </x-form-group>
                        </div>
                    </div>
                </x-card>

                <x-card>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-boxes text-primary"></i> Detalle de Productos
                        </h2>
                        <div class="bg-indigo-50 border border-indigo-200 px-4 py-2 rounded-lg shadow-sm">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="igv_incluido" id="checkIgv" class="form-checkbox rounded text-indigo-600 h-5 w-5 focus:ring-indigo-500 border-indigo-300 transition-colors" onchange="recalcularTotales()" {{ $compra->igv_incluido ? 'checked' : '' }}>
                                <span class="ml-2 text-xs text-indigo-800 font-bold tracking-wide uppercase">Precios Incluyen IGV</span>
                            </label>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="rounded-lg border border-slate-200 mb-3 overflow-x-auto">
                            <table class="w-full text-left border-collapse table-fixed" id="tablaProductos">
                                <colgroup>
                                    <col class="w-[22%]">
                                    <col class="w-[11%]">
                                    <col class="w-[8%]">
                                    <col class="w-[8%]">
                                    <col class="w-[11%]">
                                    <col class="w-[11%]">
                                    <col class="w-[11%]">
                                    <col class="w-[10%]">
                                    <col class="w-[8%]">
                                </colgroup>
                                <thead>
                                    <tr class="bg-slate-100 text-[11px] uppercase text-slate-500 tracking-wider">
                                        <th class="p-2 font-semibold">Producto</th>
                                        <th class="p-2 font-semibold">Almacén</th>
                                        <th class="p-2 font-semibold text-center">Cant.</th>
                                        <th class="p-2 font-semibold text-center">U.M.</th>
                                        <th class="p-2 font-semibold text-center">Lote</th>
                                        <th class="p-2 font-semibold text-center">Venc.</th>
                                        <th class="p-2 font-semibold text-right">P. Unit.</th>
                                        <th class="p-2 font-semibold text-right">Subtotal</th>
                                        <th class="p-2 font-semibold text-center"><i class="fas fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100" id="tbodyProductos">
                                    @foreach($compra->detalles as $index => $det)
                                    <tr class="fila-producto">
                                        <td class="p-1">
                                            <span class="texto-prod text-xs font-medium text-slate-800 truncate block" title="{{ $det->descripcion_producto }}">{{ $det->descripcion_producto }}</span>
                                            <input type="hidden" name="productos[{{ $index }}][codigo]" class="input-cod" value="{{ $det->codigo_producto }}">
                                        </td>
                                        <td class="p-1">
                                            <select name="productos[{{ $index }}][codigo_almacen]" class="w-full border border-slate-200 bg-slate-50 rounded-md text-xs select-alm" style="height:28px">
                                                @foreach($almacenes as $a)
                                                    <option value="{{ $a->codigo_almacen }}" {{ $det->codigo_almacen == $a->codigo_almacen ? 'selected' : '' }}>{{ $a->descripcion }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-1">
                                            <input type="number" name="productos[{{ $index }}][cantidad]" value="{{ $det->cantidad }}" step="0.01" class="w-full border border-slate-200 bg-slate-50 text-center rounded-md text-xs input-cant" style="height:28px">
                                        </td>
                                        <td class="p-1">
                                            <select name="productos[{{ $index }}][codigo_unidad_medida]" class="w-full border border-slate-200 bg-slate-50 rounded-md text-xs select-um" style="height:28px">
                                                @foreach($unidades_medida as $u)
                                                    <option value="{{ $u->codigo }}" {{ $det->codigo_unidad_medida == $u->codigo ? 'selected' : '' }}>{{ $u->codigo }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-1">
                                            <input type="text" name="productos[{{ $index }}][lote]" value="{{ $det->lote }}" class="w-full border border-slate-200 bg-slate-50 rounded-md text-xs text-center" style="height:28px" placeholder="Lote">
                                        </td>
                                        <td class="p-1">
                                            <input type="date" name="productos[{{ $index }}][fecha_vencimiento]" value="{{ $det->fecha_vencimiento }}" class="w-full border border-slate-200 bg-slate-50 rounded-md text-xs text-center" style="height:28px">
                                        </td>
                                        <td class="p-1">
                                            <input type="number" name="productos[{{ $index }}][precio]" value="{{ $compra->igv_incluido ? ($det->precio_unitario * 1.18) : $det->precio_unitario }}" step="any" class="w-full border border-slate-200 bg-slate-50 text-right rounded-md text-xs text-primary font-semibold input-prec" style="height:28px">
                                        </td>
                                        <td class="p-1">
                                            <input type="text" class="w-full bg-transparent border-none text-right font-semibold text-xs out-sub" value="{{ number_format($det->subtotal, 2, '.', '') }}" readonly tabindex="-1" style="height:28px">
                                        </td>
                                        <td class="p-1 text-center">
                                            <button type="button" class="text-slate-400 hover:text-red-500 btn-del text-xs" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="btnAgregarFila" class="w-full py-2 border-2 border-dashed border-slate-300 rounded-lg text-xs text-slate-500 font-semibold hover:border-primary hover:text-primary hover:bg-primary-50 transition-all flex justify-center items-center gap-1">
                            <i class="fas fa-plus-circle"></i> Buscar y agregar producto
                        </button>
                    </div>
                </x-card>
            </div>

            <div class="lg:col-span-4 xl:col-span-3">
                <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 sticky top-6 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i class="fas fa-calculator text-primary"></i> Totales
                        </h2>
                        <div class="space-y-4">
                            <div class="flex justify-between text-slate-300 text-sm">
                                <span>Subtotal:</span>
                                <input type="hidden" name="total_subtotal" id="h_sub" value="{{ $compra->subtotal }}">
                                <span id="txt_sub" class="font-medium text-white">S/ {{ number_format($compra->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-300 text-sm">
                                <span>IGV (18%):</span>
                                <input type="hidden" name="total_impuestos" id="h_igv" value="{{ $compra->igv }}">
                                <span id="txt_igv" class="font-medium text-white">S/ {{ number_format($compra->igv, 2) }}</span>
                            </div>
                            <div class="pt-4 mt-4 border-t border-slate-600 flex justify-between items-center">
                                <span class="text-slate-200 font-bold text-xl">TOTAL:</span>
                                <input type="hidden" name="total_general" id="h_total" value="{{ $compra->total }}">
                                <span id="txt_total" class="text-2xl font-black text-primary">S/ {{ number_format($compra->total, 2) }}</span>
                            </div>
                        </div>
                        <button type="submit" class="w-full mt-8 btn-primary py-3 rounded-xl font-bold text-base flex justify-center gap-2">
                            <i class="fas fa-sync-alt"></i> Actualizar Compra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<x-modal id="modalProducto" title="Buscar Producto" size="lg">
    <div>
        <select id="selectProductoModal" class="w-full" style="width:100%"></select>
    </div>
    <p class="text-xs text-slate-500 text-center mt-4">Seleccione un producto para agregarlo automáticamente a la tabla.</p>
    <x-slot:footer>
        <button type="button" id="btnCerrarProducto" class="btn-secondary">
            <i class="fas fa-check"></i> Finalizar
        </button>
    </x-slot:footer>
</x-modal>

<script src="/vendor/jquery/jquery.min.js"></script>
<script src="/vendor/select2/select2.min.js"></script>
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

    let filaIdx = {{ count($compra->detalles) }};
    let tablaBody;
    let searchUrl;
    let almacenesData;
    let unidadesData;
    function getProductName(text) {
        const m = text.match(/\]\s*(.*)/);
        return m ? m[1] : text;
    }

    function generarTemplateHTML(idx, codigo, nombre) {
        let opcionesAlmacen = almacenesData.map(a => `<option value="${a.codigo}">${a.descripcion}</option>`).join('');
        let opcionesUM = unidadesData.map(u => `<option value="${u.codigo}">${u.codigo}</option>`).join('');
        return `
        <tr class="fila-producto">
            <td class="p-1">
                <span class="texto-prod text-xs font-medium text-slate-800 truncate block" title="${nombre}">${nombre}</span>
                <input type="hidden" class="input-cod" name="productos[${idx}][codigo]" value="${codigo}">
            </td>
            <td class="p-1">
                <select class="w-full border border-slate-200 bg-slate-50 rounded-md text-xs select-alm" style="height:28px" name="productos[${idx}][codigo_almacen]">
                    ${opcionesAlmacen}
                </select>
            </td>
            <td class="p-1">
                <input type="number" step="0.01" min="0.01" class="w-full border border-slate-200 bg-slate-50 text-center rounded-md text-xs input-cant" style="height:28px" name="productos[${idx}][cantidad]">
            </td>
            <td class="p-1">
                <select class="w-full border border-slate-200 bg-slate-50 rounded-md text-xs select-um" style="height:28px" name="productos[${idx}][codigo_unidad_medida]">
                    ${opcionesUM}
                </select>
            </td>
            <td class="p-1">
                <input type="text" class="w-full border border-slate-200 bg-slate-50 rounded-md text-xs text-center" style="height:28px" placeholder="Lote" name="productos[${idx}][lote]">
            </td>
            <td class="p-1">
                <input type="date" class="w-full border border-slate-200 bg-slate-50 rounded-md text-xs text-center" style="height:28px" name="productos[${idx}][fecha_vencimiento]">
            </td>
            <td class="p-1">
                <input type="number" step="any" min="0" class="w-full border border-slate-200 bg-slate-50 text-right rounded-md text-xs text-primary font-semibold input-prec" style="height:28px" name="productos[${idx}][precio]">
            </td>
            <td class="p-1">
                <input type="text" class="w-full bg-transparent border-none text-right font-semibold text-xs out-sub" value="0.00" readonly tabindex="-1" style="height:28px">
            </td>
            <td class="p-1 text-center">
                <button type="button" class="text-slate-400 hover:text-red-500 btn-del text-xs" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>
    `;
    }

    function agregarFila(producto) {
        const codigo = producto.id;
        
        // Verificar si el producto ya existe en la tabla
        let existe = false;
        document.querySelectorAll('.input-cod').forEach(input => {
            if (input.value === codigo) {
                existe = true;
                // Incrementar cantidad si ya existe
                const fila = input.closest('tr');
                const cantInput = fila.querySelector('.input-cant');
                if (cantInput) {
                    cantInput.value = (parseFloat(cantInput.value) + 1).toFixed(2);
                    // Disparar evento input para recalcular subtotales
                    cantInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        });

        if (existe) {
            window.toast('El producto ya estaba en la lista. Se aumentó la cantidad.', 'info');
            return;
        }

        const idx = filaIdx++;
        const nombre = getProductName(producto.text);
        const html = generarTemplateHTML(idx, codigo, nombre);
        tablaBody.insertAdjacentHTML('beforeend', html);
    }

    function cerrarModalProducto() {
        cerrarModal('modalProducto');
    }

    function recalcularTotales() {
        let st = 0;
        document.querySelectorAll('.out-sub').forEach(el => st += parseFloat(el.value) || 0);
        
        let isIgvIncluido = document.getElementById('checkIgv') && document.getElementById('checkIgv').checked;
        
        let subtotal = 0;
        let igv = 0;
        let total = 0;
        
        if (isIgvIncluido) {
            total = st; // The sum is the Total
            subtotal = total / 1.18;
            igv = total - subtotal;
        } else {
            subtotal = st; // The sum is the Subtotal
            igv = subtotal * 0.18;
            total = subtotal + igv;
        }

        let currencySymbol = document.getElementById('selectMoneda').value === 'USD' ? '$ ' : 'S/ ';
        document.getElementById('txt_sub').innerText = currencySymbol + subtotal.toFixed(2);
        document.getElementById('txt_igv').innerText = currencySymbol + igv.toFixed(2);
        document.getElementById('txt_total').innerText = currencySymbol + total.toFixed(2);

        document.getElementById('h_sub').value = subtotal.toFixed(2);
        document.getElementById('h_igv').value = igv.toFixed(2);
        document.getElementById('h_total').value = total.toFixed(2);
    }

    $(document).ready(function () {
        tablaBody = document.getElementById('tbodyProductos');
        searchUrl = '/productos/search-ajax';
        almacenesData = {!! json_encode($almacenes->map(fn($a) => ['codigo' => $a->codigo_almacen, 'descripcion' => $a->descripcion])->all()) !!};
        unidadesData = {!! json_encode($unidades_medida->map(fn($u) => ['codigo' => $u->codigo, 'descripcion' => $u->descripcion])->all()) !!};

        if (typeof $().select2 !== 'undefined') {
            $('#selectProductoModal').select2({
                ajax: {
                    url: searchUrl,
                    dataType: 'json',
                    delay: 300,
                    data: function(p) { return {q: p.term}; },
                    processResults: function(d) { return {results: d}; },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: 'Buscar por código o nombre...',
                width: '100%',
                dropdownParent: $('#modalProducto')
            });

            $('#selectProductoModal').on('select2:select', function(e) {
                agregarFila(e.params.data);
                $('#selectProductoModal').val(null).trigger('change');
            });
        }

        $('#btnCerrarProducto').on('click', cerrarModalProducto);

        let tipoCambioActual = "{{ \App\Models\ParametroSistema::where('codigo_parametro', 'TIPO_CAMBIO_USD')->value('valor') ?? '1.000' }}";

        $('#selectMoneda').on('change', function() {
            if ($(this).val() === 'USD') {
                $('#groupTipoCambio').show();
                $('#inputTipoCambio').attr('required', true);
                if (parseFloat($('#inputTipoCambio').val()) === 1 || $('#inputTipoCambio').val() === '') {
                    $('#inputTipoCambio').val(tipoCambioActual);
                }
            } else {
                $('#groupTipoCambio').hide();
                $('#inputTipoCambio').removeAttr('required');
                $('#inputTipoCambio').val('1.000');
            }
            recalcularTotales();
        });

        $('#btnAgregarFila').on('click', function () {
            $('#selectProductoModal').val(null).trigger('change');
            abrirModal('modalProducto');
            setTimeout(() => {
                if (typeof $('#selectProductoModal').select2 === 'function') {
                    $('#selectProductoModal').select2('open');
                }
            }, 250);
        });

        $('#tablaProductos').on('input', function (e) {
            if ($(e.target).hasClass('input-cant') || $(e.target).hasClass('input-prec')) {
                const fila = e.target.closest('tr');
                const cant = parseFloat(fila.querySelector('.input-cant').value) || 0;
                const prec = parseFloat(fila.querySelector('.input-prec').value) || 0;
                fila.querySelector('.out-sub').value = (cant * prec).toFixed(2);
                recalcularTotales();
            }
        });

        $('#tablaProductos').on('click', function (e) {
            if ($(e.target).closest('.btn-del').length) {
                const tbody = document.getElementById('tbodyProductos');
                if (tbody.querySelectorAll('.fila-producto').length > 1) {
                    $(e.target).closest('tr').remove();
                    recalcularTotales();
                } else {
                    window.toast('La compra debe tener al menos un ítem.', 'warning');
                }
            }
        });
    });
</script>
@endsection
