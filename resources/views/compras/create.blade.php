@extends('layouts.app')
@section('title', 'Registrar Nueva Compra')

@section('content')
<link href="/vendor/select2/select2.min.css" rel="stylesheet" />
<div class="container mx-auto px-4 py-6 max-w-7xl">

    <x-page-header title="Registrar Nueva Compra" subtitle="Complete los datos del comprobante y asigne el almacén por cada insumo.">
        <a href="{{ route('compras.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </x-page-header>

    <form action="{{ route('compras.store') }}" method="POST" id="formCompra">
        @csrf
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
                            <x-form-group class="md:col-span-4" label="Tipo Doc." required>
                                <select name="tipo_documento" class="input-field" required>
                                    <option value="FACTURA">FACTURA</option>
                                    <option value="BOLETA">BOLETA</option>
                                    <option value="GUIA_REMISION">GUÍA DE REMISIÓN</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </x-form-group>
                            <x-form-group class="md:col-span-3" label="Serie" required>
                                <input type="text" name="serie_documento" class="input-field uppercase" placeholder="F001" required>
                            </x-form-group>
                            <x-form-group class="md:col-span-5" label="N° Documento" required>
                                <input type="text" name="numero_documento" class="input-field" placeholder="0004512" required>
                            </x-form-group>

                            <x-form-group class="md:col-span-4" label="Fecha de Emisión" required>
                                <input type="date" name="fecha_compra" value="{{ date('Y-m-d') }}" class="input-field" required>
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
                        </div>
                    </div>
                </x-card>

                <x-card>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-boxes text-primary"></i> Detalle de Recepción
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="rounded-lg border border-slate-200 mb-3 overflow-x-auto">
                            <table class="w-full text-left border-collapse table-fixed" id="tablaProductos">
                                <colgroup>
                                    <col class="w-[28%]">
                                    <col class="w-[15%]">
                                    <col class="w-[10%]">
                                    <col class="w-[10%]">
                                    <col class="w-[15%]">
                                    <col class="w-[14%]">
                                    <col class="w-[8%]">
                                </colgroup>
                                <thead>
                                    <tr class="bg-slate-100 text-[11px] uppercase text-slate-500 tracking-wider">
                                        <th class="p-2 font-semibold">Producto</th>
                                        <th class="p-2 font-semibold">Almacén</th>
                                        <th class="p-2 font-semibold text-center">Cant.</th>
                                        <th class="p-2 font-semibold text-center">U.M.</th>
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
                <input type="number" step="0.01" min="0" class="w-full border border-slate-200 bg-slate-50 text-right rounded-md text-xs text-primary font-semibold input-prec" style="height:28px" name="productos[${idx}][precio]">
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
        const idx = filaIdx++;
        const nombre = getProductName(producto.text);
        const html = generarTemplateHTML(idx, producto.id, nombre);
        tablaBody.insertAdjacentHTML('beforeend', html);
    }

    function cerrarModalProducto() {
        cerrarModal('modalProducto');
    }

    function recalcularTotales() {
        let st = 0;
        document.querySelectorAll('.out-sub').forEach(el => st += parseFloat(el.value) || 0);
        const igv = st * 0.18;
        const total = st + igv;
        document.getElementById('txt_sub').innerText = 'S/ ' + st.toFixed(2);
        document.getElementById('txt_igv').innerText = 'S/ ' + igv.toFixed(2);
        document.getElementById('txt_total').innerText = 'S/ ' + total.toFixed(2);
        document.getElementById('h_sub').value = st.toFixed(2);
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
