@extends('layouts.app')
@section('title', 'Registrar Nueva Compra')

@section('content')
<link href="/vendor/select2/select2.min.css" rel="stylesheet" />
<div class="container mx-auto px-4 py-6 max-w-7xl">
    
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Registrar Nueva Compra</h1>
            <p class="text-sm text-slate-500 mt-1">Complete los datos del comprobante y asigne el almacén por cada insumo.</p>
        </div>
        <a href="{{ route('compras.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>

    <form action="{{ route('compras.store') }}" method="POST" id="formCompra">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50/50 border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-blue-500"></i> Datos del Comprobante
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Tipo Doc. <span class="text-red-500">*</span></label>
                                <select name="tipo_documento" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" required>
                                    <option value="FACTURA">FACTURA</option>
                                    <option value="BOLETA">BOLETA</option>
                                    <option value="GUIA_REMISION">GUÍA DE REMISIÓN</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Serie <span class="text-red-500">*</span></label>
                                <input type="text" name="serie_documento" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 uppercase" placeholder="F001" required>
                            </div>
                            <div class="md:col-span-5">
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">N° Documento <span class="text-red-500">*</span></label>
                                <input type="text" name="numero_documento" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" placeholder="0004512" required>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Fecha de Emisión <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_compra" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" required>
                            </div>
                            
                            <div class="md:col-span-8">
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Proveedor <span class="text-red-500">*</span></label>
                                <div class="flex gap-2">
                                    <select name="ruc_proveedor" id="selectProveedor" class="flex-1 bg-slate-50 border border-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" required>
                                        <option value="">Seleccione Proveedor...</option>
                                        @foreach($proveedores as $p)
                                            <option value="{{ $p->ruc }}">{{ $p->ruc }} - {{ $p->razon_social }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="document.getElementById('modalProveedor').classList.remove('hidden')" class="bg-slate-800 text-white px-4 rounded-lg hover:bg-slate-700 transition shadow-sm" title="Registrar Nuevo Proveedor">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50/50 border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-boxes text-blue-500"></i> Detalle de Recepción
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="overflow-x-auto rounded-lg border border-slate-200 mb-4">
                            <table class="w-full text-left border-collapse" id="tablaProductos">
                                <thead>
                                    <tr class="bg-slate-100 text-xs uppercase text-slate-500 tracking-wider">
                                        <th class="p-3 font-semibold">Producto / Insumo</th>
                                        <th class="p-3 font-semibold">Almacén Destino</th>
                                        <th class="p-3 font-semibold w-24 text-center">Cant.</th>
                                        <th class="p-3 font-semibold w-32 text-right">P. Unit.</th>
                                        <th class="p-3 font-semibold w-32 text-right">Subtotal</th>
                                        <th class="p-3 font-semibold w-10 text-center"><i class="fas fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    <tr class="fila-producto">
                                        <td class="p-2">
                                            <select name="productos[0][codigo]" class="w-full border-transparent bg-slate-50 rounded-lg text-sm select-prod" required>
                                                <option value="">Seleccionar...</option>
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <select name="productos[0][codigo_almacen]" class="w-full border-transparent bg-slate-50 rounded-lg text-sm select-alm" required>
                                                <option value="">Seleccionar...</option>
                                                @foreach($almacenes as $a)
                                                    <option value="{{ $a->codigo_almacen }}">{{ $a->descripcion }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" name="productos[0][cantidad]" step="0.01" min="0.01" class="w-full border-transparent bg-slate-50 text-center rounded-lg text-sm input-cant" required>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" name="productos[0][precio]" step="0.01" min="0" class="w-full border-transparent bg-slate-50 text-right rounded-lg text-sm text-blue-700 font-bold input-prec" required>
                                        </td>
                                        <td class="p-2">
                                            <input type="text" class="w-full bg-transparent border-none text-right font-bold out-sub" value="0.00" readonly tabindex="-1">
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" class="text-slate-300 hover:text-red-500 btn-del"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="btnAgregarFila" class="w-full py-3 border-2 border-dashed border-slate-300 rounded-xl text-slate-500 font-semibold hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition-all flex justify-center items-center gap-2">
                            <i class="fas fa-plus-circle"></i> Agregar nueva línea de producto
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 xl:col-span-3">
                <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 sticky top-6 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i class="fas fa-calculator text-blue-400"></i> Resumen
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
                                <span id="txt_total" class="text-2xl font-black text-blue-400">S/ 0.00</span>
                            </div>
                        </div>
                        <button type="submit" class="w-full mt-8 bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-xl font-bold transition-all flex justify-center gap-2">
                            <i class="fas fa-save"></i> Registrar Compra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="modalProveedor" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200">
        
        <!-- Cabecera adaptada al diseño de tus secciones con fondo claro y borde inferior -->
        <div class="bg-slate-50/50 border-b border-slate-100 px-6 py-4 flex justify-between items-center">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-building text-blue-500"></i> Nuevo Proveedor
            </h3>
            <button type="button" onclick="document.getElementById('modalProveedor').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <form id="formNuevoProveedor" class="p-6 space-y-5">
            <div>
                <!-- Etiqueta ajustada con mb-2 para mantener el mismo espaciado -->
                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">RUC <span class="text-red-500">*</span></label>
                <!-- Inputs adaptados al estilo principal (bg-slate-50, focus en borde azul y padding de 2.5) -->
                <input type="text" name="ruc" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" required maxlength="11" placeholder="Ej. 20123456789">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Razón Social <span class="text-red-500">*</span></label>
                <input type="text" name="razon_social" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" required placeholder="Nombre de la empresa">
            </div>
            
            <!-- Botón adaptado al estilo del botón "Registrar Compra" (py-3, rounded-xl) -->
            <button type="submit" class="w-full mt-4 bg-green-600 hover:bg-green-500 text-white py-3 rounded-xl font-bold transition-all flex justify-center items-center gap-2">
                <i class="fas fa-save"></i> Guardar y Seleccionar
            </button>
        </form>
    </div>
</div>

<script src="/vendor/jquery/jquery.min.js"></script>
<script src="/vendor/select2/select2.min.js"></script>
<script>
    let filaIdx = 1;
    let tabla = document.querySelector('#tablaProductos tbody');

    const searchUrl = '{{ route("api.productos.search") }}';

    function initSelect2() {
        try {
            $('.select-prod').select2({
                ajax: {
                    url: searchUrl, dataType: 'json', delay: 300,
                    data: function(p) { return {q: p.term}; },
                    processResults: function(d) { return {results: d}; },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: 'Buscar por código o nombre...',
                width: '100%'
            });
        } catch(e) {}
    }

    document.addEventListener('DOMContentLoaded', initSelect2);

    document.getElementById('btnAgregarFila').addEventListener('click', () => {
        try { $('.select-prod').select2('destroy'); } catch(e) {}
        const tr = document.querySelector('.fila-producto').cloneNode(true);
        tr.querySelectorAll('input:not(.out-sub)').forEach(i => i.value = '');
        tr.querySelector('.out-sub').value = '0.00';
        tr.querySelector('.select-prod').value = '';
        tr.querySelector('.select-alm').value = '';
        
        tr.querySelector('.select-prod').name = `productos[${filaIdx}][codigo]`;
        tr.querySelector('.select-alm').name = `productos[${filaIdx}][codigo_almacen]`;
        tr.querySelector('.input-cant').name = `productos[${filaIdx}][cantidad]`;
        tr.querySelector('.input-prec').name = `productos[${filaIdx}][precio]`;
        
        tabla.appendChild(tr);
        filaIdx++;
        initSelect2();
    });

    document.getElementById('tablaProductos').addEventListener('input', e => {
        if(e.target.classList.contains('input-cant') || e.target.classList.contains('input-prec')) {
            const fila = e.target.closest('tr');
            const cant = parseFloat(fila.querySelector('.input-cant').value) || 0;
            const prec = parseFloat(fila.querySelector('.input-prec').value) || 0;
            fila.querySelector('.out-sub').value = (cant * prec).toFixed(2);
            recalcularTotales();
        }
    });

    document.getElementById('tablaProductos').addEventListener('click', e => {
        if(e.target.closest('.btn-del')) {
            if(tabla.querySelectorAll('.fila-producto').length > 1) {
                e.target.closest('tr').remove();
                recalcularTotales();
            } else alert('Debe haber al menos un producto.');
        }
    });

    function recalcularTotales() {
        let st = 0;
        document.querySelectorAll('.out-sub').forEach(el => st += parseFloat(el.value));
        const igv = st * 0.18;
        const total = st + igv;
        document.getElementById('txt_sub').innerText = 'S/ ' + st.toFixed(2);
        document.getElementById('txt_igv').innerText = 'S/ ' + igv.toFixed(2);
        document.getElementById('txt_total').innerText = 'S/ ' + total.toFixed(2);
        document.getElementById('h_sub').value = st.toFixed(2);
        document.getElementById('h_igv').value = igv.toFixed(2);
        document.getElementById('h_total').value = total.toFixed(2);
    }

    document.getElementById('formNuevoProveedor').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            const response = await fetch("{{ route('proveedores.storeAjax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            });
            const data = await response.json();
            
            if(data.success) {
                const select = document.getElementById('selectProveedor');
                select.add(new Option(data.proveedor.ruc + ' - ' + data.proveedor.razon_social, data.proveedor.ruc, true, true));
                document.getElementById('modalProveedor').classList.add('hidden');
                form.reset();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Ocurrió un error en la conexión.');
        }
    });
</script>
@endsection