@extends('layouts.app')
@section('title', 'Registrar Nueva Compra')

@section('content')
<link href="/vendor/select2/select2.min.css" rel="stylesheet" />
<div class="container mx-auto px-4 py-6 max-w-7xl">

    <x-page-header title="Registrar Nueva Compra" subtitle="Complete los datos del comprobante y asigne el almacén por cada insumo.">
        <x-slot:actions>
            <a href="{{ route('compras.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ route('compras.store') }}" method="POST" id="formCompra">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                <x-card>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-primary"></i> Datos del Comprobante
                        </h2>
                        
                        <!-- Selector de Guía de Remisión -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-slate-500">¿Vincular a Guía?</label>
                            <select name="ids_guias[]" id="selectGuia" multiple="multiple" class="border border-slate-300 rounded-lg text-sm px-2 py-1 bg-white focus:ring-primary focus:border-primary" style="width: 350px;">
                                @foreach($guiasPendientes as $guia)
                                    <option value="{{ $guia->id_guia }}" data-ruc="{{ $guia->ruc_proveedor }}">
                                        Guía {{ $guia->numero_guia }} - {{ Str::limit($guia->proveedor, 20) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                            <x-form-group class="md:col-span-3" label="Tipo Doc." required>
                                <select name="tipo_documento" class="input-field" required>
                                    @foreach($tipos_comprobante as $tc)
                                        <option value="{{ $tc->valor }}">{{ $tc->descripcion }}</option>
                                    @endforeach
                                </select>
                            </x-form-group>
                            <x-form-group class="md:col-span-2" label="Serie" required>
                                <input type="text" name="serie_documento" class="input-field uppercase" placeholder="F001" required>
                            </x-form-group>
                            <x-form-group class="md:col-span-3" label="N° Documento" required>
                                <input type="text" name="numero_documento" class="input-field" placeholder="0004512" required>
                            </x-form-group>

                            <x-form-group class="md:col-span-2" label="Fecha de Emisión" required>
                                <input type="date" name="fecha_compra" value="{{ date('Y-m-d') }}" class="input-field" required max="{{ date('Y-m-d') }}">
                            </x-form-group>

                            <x-form-group class="md:col-span-2" label="Moneda" required>
                                <select name="moneda" id="selectMoneda" class="input-field" required>
                                    <option value="PEN">Soles (PEN)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                            </x-form-group>



                            <x-form-group class="md:col-span-8" label="Proveedor" required>
                                <div class="flex gap-2">
                                    <select name="ruc_proveedor" id="selectProveedor" class="input-field" required>
                                        <option value="">Seleccione Proveedor...</option>
                                        @foreach($proveedores as $p)
                                            <option value="{{ $p->ruc }}">{{ $p->ruc }} - {{ $p->razon_social }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="abrirModal('modalProveedor')" class="bg-slate-800 text-white px-4 rounded-lg hover:bg-slate-700 transition shadow-sm" title="Registrar Nuevo Proveedor">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </x-form-group>

                            <x-form-group class="md:col-span-4" label="Tipo de Cambio" id="groupTipoCambio" style="display: none;">
                                <input type="number" name="tipo_cambio" id="inputTipoCambio" step="0.001" min="0" class="input-field" placeholder="Ej. 3.800" value="1.000">
                            </x-form-group>
                        </div>
                    </div>
                </x-card>

                <x-card>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-boxes text-primary"></i> Detalle de Recepción
                        </h2>
                        <div class="bg-indigo-50 border border-indigo-200 px-4 py-2 rounded-lg shadow-sm">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="igv_incluido" id="checkIgv" class="form-checkbox rounded text-indigo-600 h-5 w-5 focus:ring-indigo-500 border-indigo-300 transition-colors" onchange="recalcularTotales()">
                                <span class="ml-2 text-xs text-indigo-800 font-bold tracking-wide uppercase">Precios Incluyen IGV</span>
                            </label>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="rounded-lg border border-slate-200 mb-3 overflow-x-auto">
                            <table class="w-full text-left border-collapse table-fixed" id="tablaProductos">
                                <colgroup>
                                    <col class="w-[5%]">
                                    <col class="w-[20%]">
                                    <col class="w-[11%]">
                                    <col class="w-[8%]">
                                    <col class="w-[8%]">
                                    <col class="w-[11%]">
                                    <col class="w-[11%]">
                                    <col class="w-[10%]">
                                    <col class="w-[10%]">
                                    <col class="w-[6%]">
                                </colgroup>
                                <thead>
                                    <tr class="bg-slate-100 text-[11px] uppercase text-slate-500 tracking-wider">
                                        <th class="p-2 font-semibold text-center">#</th>
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
                            <i class="fas fa-calculator text-primary"></i> Resumen
                        </h2>
                        <div class="space-y-4">
                            <div class="flex justify-between text-slate-300 text-sm">
                                <span>Subtotal:</span>
                                <input type="hidden" name="total_subtotal" id="h_sub" value="0.00">
                                <span id="txt_sub" class="font-medium text-white">S/ 0.00</span>
                            </div>
                            <div class="flex justify-between text-slate-300 text-sm">
                                <span>IGV (18%):</span>
                                <input type="hidden" name="total_impuestos" id="h_igv" value="0.00">
                                <span id="txt_igv" class="font-medium text-white">S/ 0.00</span>
                            </div>
                            <div class="pt-4 mt-4 border-t border-slate-600 flex justify-between items-center">
                                <span class="text-slate-200 font-bold">TOTAL:</span>
                                <input type="hidden" name="total_general" id="h_total" value="0.00">
                                <span id="txt_total" class="text-2xl font-black text-primary">S/ 0.00</span>
                            </div>
                        </div>
                        <button type="submit" class="w-full mt-8 btn-primary py-3 rounded-xl font-bold text-base flex justify-center gap-2">
                            <i class="fas fa-save"></i> Registrar Compra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<x-modal id="modalProveedor" title="Nuevo Proveedor">
    <form id="form-modalProveedor" class="space-y-5">
        <x-form-group label="RUC" required>
            <input type="text" name="ruc" id="ruc" class="input-field" required maxlength="11" placeholder="Ej. 20123456789">
        </x-form-group>
        <x-form-group label="Razón Social" required>
            <input type="text" name="razon_social" id="razon_social" class="input-field" required placeholder="Nombre de la empresa">
        </x-form-group>
        <x-slot:footer>
            <button type="submit" form="form-modalProveedor" class="btn-primary w-full">
                <i class="fas fa-save"></i> Guardar y Seleccionar
            </button>
        </x-slot:footer>
    </form>
</x-modal>

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

    let filaIdx = 0;
    let tablaBody;
    let searchUrl;
    let almacenesData;
    let unidadesData;
    function getProductName(text) {
        const m = text.match(/\]\s*(.*)/);
        return m ? m[1] : text;
    }

    function generarTemplateHTML(idx, codigo, nombre, um) {
        let opcionesAlmacen = almacenesData.map(a => `<option value="${a.codigo}" ${a.codigo === 'ALM04' ? 'selected' : ''}>${a.descripcion}</option>`).join('');
        let opcionesUM = unidadesData.map(u => `<option value="${u.codigo}" ${u.codigo === um ? 'selected' : ''}>${u.codigo}</option>`).join('');
        return `
        <tr class="fila-producto">
            <td class="p-1 text-center">
                <span class="text-xs font-bold text-slate-500 row-item-number">${idx + 1}</span>
            </td>
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
                <input type="date" class="w-full border border-slate-200 bg-slate-50 rounded-md text-xs text-center" style="height:28px" name="productos[${idx}][fecha_vencimiento]" max="{{ date('Y-m-d') }}">
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
        const um = producto.codigo_unidad_medida || 'CAJ';
        const html = generarTemplateHTML(idx, codigo, nombre, um);
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

        $('#btnAgregarFila').on('click', function () {
            $('#selectProductoModal').val(null).trigger('change');
            abrirModal('modalProducto');
            setTimeout(() => {
                if (typeof $('#selectProductoModal').select2 === 'function') {
                    $('#selectProductoModal').select2('open');
                }
            }, 250);
        });

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
            recalcularTotales(); // Refrescar símbolos de la vista resumen
        });

        if (typeof $().select2 !== 'undefined') {
            $('#selectGuia').select2({
                placeholder: 'Seleccionar guías',
                allowClear: true
            });
        }

        // Lógica para autocompletar desde Guía de Remisión Múltiples
        $('#selectGuia').on('change', async function() {
            const idsGuias = $(this).val();
            
            // Validar proveedor único
            let valid = true;
            let currentRuc = null;
            $('#selectGuia option:selected').each(function() {
                let ruc = $(this).data('ruc');
                if (currentRuc === null) {
                    currentRuc = ruc;
                } else if (currentRuc !== ruc) {
                    valid = false;
                }
            });

            if (!valid) {
                alert('Las guías seleccionadas deben pertenecer al mismo proveedor.');
                // Quitar la última selección (la que rompió la regla)
                let selected = $(this).val();
                selected.pop();
                $(this).val(selected).trigger('change');
                return;
            }

            if(!idsGuias || idsGuias.length === 0) {
                $('#tbodyProductos').empty();
                recalcularTotales();
                return;
            }

            try {
                // Hacer POST con el array de IDs
                const response = await fetch(`/admin/compras/api/guias-multi`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids: idsGuias })
                });
                
                const dataArray = await response.json();
                
                // Setear proveedor con el primero encontrado
                if(dataArray.length > 0) {
                    $('#selectProveedor').val(dataArray[0].ruc_proveedor).trigger('change');
                }
                
                // Limpiar tabla actual
                $('#tbodyProductos').empty();
                filaIdx = 0;

                // Llenar detalles iterando sobre cada guía y sus detalles
                dataArray.forEach(data => {
                    data.detalles.forEach(d => {
                        const idx = filaIdx++;
                        const nombre = d.producto ? d.producto.descripcion : d.descripcion_producto;
                        let opcionesAlmacen = almacenesData.map(a => `<option value="${a.codigo}" ${a.codigo === d.codigo_almacen ? 'selected' : ''}>${a.descripcion}</option>`).join('');
                        let opcionesUM = unidadesData.map(u => `<option value="${u.codigo}" ${u.codigo === d.codigo_unidad_medida ? 'selected' : ''}>${u.codigo}</option>`).join('');
                        
                        const html = `
                        <tr class="fila-producto bg-yellow-50/30">
                            <td class="p-1 text-center">
                                <span class="text-xs font-bold text-slate-500 row-item-number">${idx + 1}</span>
                            </td>
                            <td class="p-1">
                                <span class="texto-prod text-xs font-medium text-slate-800 truncate block" title="${nombre}">${d.codigo_producto} - ${nombre}</span>
                                <input type="hidden" class="input-cod" name="productos[${idx}][codigo]" value="${d.codigo_producto}">
                            </td>
                            <td class="p-1">
                                <select class="w-full border border-slate-200 bg-slate-100 rounded-md text-xs select-alm pointer-events-none" style="height:28px" name="productos[${idx}][codigo_almacen]" readonly tabindex="-1">
                                    ${opcionesAlmacen}
                                </select>
                            </td>
                            <td class="p-1">
                                <input type="number" step="0.01" min="0.01" class="w-full border border-slate-200 bg-slate-100 text-center rounded-md text-xs input-cant font-bold" style="height:28px" name="productos[${idx}][cantidad]" value="${d.cantidad}" readonly tabindex="-1">
                            </td>
                            <td class="p-1">
                                <select class="w-full border border-slate-200 bg-slate-100 rounded-md text-xs select-um pointer-events-none" style="height:28px" name="productos[${idx}][codigo_unidad_medida]" readonly tabindex="-1">
                                    ${opcionesUM}
                                </select>
                            </td>
                            <td class="p-1">
                                <input type="text" class="w-full border border-slate-200 bg-slate-100 rounded-md text-xs text-center" style="height:28px" name="productos[${idx}][lote]" value="${d.lote || ''}" readonly tabindex="-1">
                            </td>
                            <td class="p-1">
                                <input type="date" class="w-full border border-slate-200 bg-slate-100 rounded-md text-xs text-center" style="height:28px" name="productos[${idx}][fecha_vencimiento]" value="${d.fecha_vencimiento ? d.fecha_vencimiento.split('T')[0] : ''}" readonly tabindex="-1" max="{{ date('Y-m-d') }}">
                            </td>
                        <td class="p-1">
                            <input type="number" step="any" min="0" class="w-full border border-primary bg-white text-right rounded-md text-xs text-primary font-bold input-prec focus:ring-primary shadow-inner" style="height:28px" name="productos[${idx}][precio]" placeholder="0.00" required autofocus>
                        </td>
                        <td class="p-1">
                            <input type="text" class="w-full bg-transparent border-none text-right font-semibold text-xs out-sub" value="0.00" readonly tabindex="-1" style="height:28px">
                        </td>
                        <td class="p-1 text-center">
                            <!-- No se puede eliminar items de una guia -->
                            <i class="fas fa-lock text-slate-300 text-xs" title="Vinculado a Guía"></i>
                        </td>
                    </tr>`;
                    tablaBody.insertAdjacentHTML('beforeend', html);
                });
            });
            recalcularTotales();
            window.toast('Datos de las guías cargados. Por favor ingrese los precios unitarios.', 'success');
                
                // Focus el primer precio
                setTimeout(() => {
                    const firstPrice = document.querySelector('.input-prec');
                    if(firstPrice) firstPrice.focus();
                }, 100);

            } catch (err) {
                console.error(err);
                window.toast('Error al cargar la guía.', 'error');
            }
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
                    
                    // Reenumerar filas
                    document.querySelectorAll('.fila-producto').forEach((fila, index) => {
                        const itemNumber = fila.querySelector('.row-item-number');
                        if (itemNumber) {
                            itemNumber.textContent = index + 1;
                        }
                    });
                } else {
                    window.toast('Debe haber al menos un producto.', 'warning');
                }
            }
        });

        $('#form-modalProveedor').on('submit', async function (e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            try {
                const response = await fetch('/admin/proveedores/ajax', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    const select = document.getElementById('selectProveedor');
                    select.add(new Option(data.proveedor.ruc + ' - ' + data.proveedor.razon_social, data.proveedor.ruc, true, true));
                    cerrarModal('modalProveedor');
                } else {
                    window.toast('Error: ' + data.message, 'error');
                }
            } catch (error) {
                window.toast('Ocurrió un error en la conexión.', 'error');
            }
        });
    });
</script>
@endsection
