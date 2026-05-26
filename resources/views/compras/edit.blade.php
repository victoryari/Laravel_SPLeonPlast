@extends('layouts.app')
@section('title', 'Editar Compra')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="container mx-auto px-4 py-6 max-w-7xl">
    
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Editar Compra</h1>
            <p class="text-sm text-slate-500 mt-1">Modifique los datos del comprobante o el detalle de los insumos.</p>
        </div>
        <a href="{{ route('compras.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>

    <form action="{{ route('compras.update', $compra->id_compra) }}" method="POST" id="formCompra">
        @csrf
        @method('PUT')
        
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
                                <select name="tipo_documento" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg block p-2.5" required>
                                    @foreach(['FACTURA', 'BOLETA', 'GUIA', 'TICKET'] as $tipo)
                                        <option value="{{ $tipo }}" {{ $compra->tipo_documento == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Serie <span class="text-red-500">*</span></label>
                                <input type="text" name="serie_documento" value="{{ $compra->serie_documento }}" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg block p-2.5 uppercase" required>
                            </div>
                            <div class="md:col-span-5">
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">N° Documento <span class="text-red-500">*</span></label>
                                <input type="text" name="numero_documento" value="{{ $compra->numero_documento }}" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg block p-2.5" required>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Fecha de Emisión <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_compra" value="{{ $compra->fecha_compra }}" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg block p-2.5" required>
                            </div>
                            
                            <div class="md:col-span-8">
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Proveedor <span class="text-red-500">*</span></label>
                                <select name="ruc_proveedor" id="selectProveedor" class="w-full bg-slate-50 border border-slate-300 text-sm rounded-lg block p-2.5" required>
                                    @foreach($proveedores as $p)
                                        <option value="{{ $p->ruc }}" {{ $compra->ruc_proveedor == $p->ruc ? 'selected' : '' }}>{{ $p->ruc }} - {{ $p->razon_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50/50 border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-boxes text-blue-500"></i> Detalle de Productos
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="overflow-x-auto rounded-lg border border-slate-200 mb-4">
                            <table class="w-full text-left border-collapse" id="tablaProductos">
                                <thead>
                                    <tr class="bg-slate-100 text-xs uppercase text-slate-500 tracking-wider">
                                        <th class="p-3 font-semibold">Producto / Insumo</th>
                                        <th class="p-3 font-semibold text-center">Almacén Destino</th>
                                        <th class="p-3 font-semibold w-24 text-center">Cant.</th>
                                        <th class="p-3 font-semibold w-32 text-right">P. Unit.</th>
                                        <th class="p-3 font-semibold w-32 text-right">Subtotal</th>
                                        <th class="p-3 font-semibold w-10 text-center"><i class="fas fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach($compra->detalles as $index => $det)
                                    <tr class="fila-producto">
                                        <td class="p-2">
                                            <select name="productos[{{ $index }}][codigo]" class="w-full border-transparent bg-slate-50 rounded-lg text-sm select-prod" required>
                                                    @foreach($productos as $prod)
                                                    <option value="{{ $prod->codigo }}" {{ $det->codigo_producto == $prod->codigo ? 'selected' : '' }}>[{{ $prod->codigo }}] {{ $prod->descripcion }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <select name="productos[{{ $index }}][codigo_almacen]" class="w-full border-transparent bg-slate-50 rounded-lg text-sm select-alm" required>
                                                @foreach($almacenes as $a)
                                                    <option value="{{ $a->codigo_almacen }}" {{ $det->codigo_almacen == $a->codigo_almacen ? 'selected' : '' }}>{{ $a->descripcion }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" name="productos[{{ $index }}][cantidad]" value="{{ $det->cantidad }}" step="0.01" class="w-full border-transparent bg-slate-50 text-center rounded-lg text-sm input-cant" required>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" name="productos[{{ $index }}][precio]" value="{{ $det->precio_unitario }}" step="0.01" class="w-full border-transparent bg-slate-50 text-right rounded-lg text-sm text-blue-700 font-bold input-prec" required>
                                        </td>
                                        <td class="p-2 text-right">
                                            <input type="text" class="w-full bg-transparent border-none text-right font-bold out-sub" value="{{ number_format($det->subtotal, 2, '.', '') }}" readonly tabindex="-1">
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" class="text-slate-300 hover:text-red-500 btn-del"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="btnAgregarFila" class="w-full py-3 border-2 border-dashed border-slate-300 rounded-xl text-slate-500 font-semibold hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition-all flex justify-center items-center gap-2">
                            <i class="fas fa-plus-circle"></i> Agregar nueva línea
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 xl:col-span-3">
                <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 sticky top-6 p-6">
                    <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-calculator text-blue-400"></i> Totales
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
                            <span id="txt_total" class="text-2xl font-black text-blue-400">S/ {{ number_format($compra->total, 2) }}</span>
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-8 bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-xl font-bold transition-all shadow-lg">
                        <i class="fas fa-sync-alt mr-2"></i> Actualizar Compra
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let filaIdx = {{ count($compra->detalles) }};
    let tabla = document.querySelector('#tablaProductos tbody');

    document.getElementById('btnAgregarFila').addEventListener('click', () => {
        try { $('.select-prod').select2('destroy'); } catch(e) {}
        const tr = document.querySelector('.fila-producto').cloneNode(true);
        
        tr.querySelectorAll('input:not(.out-sub)').forEach(i => i.value = '');
        tr.querySelector('.out-sub').value = '0.00';
        tr.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        
        tr.querySelector('.select-prod').name = `productos[${filaIdx}][codigo]`;
        tr.querySelector('.select-alm').name = `productos[${filaIdx}][codigo_almacen]`;
        tr.querySelector('.input-cant').name = `productos[${filaIdx}][cantidad]`;
        tr.querySelector('.input-prec').name = `productos[${filaIdx}][precio]`;
        
        tabla.appendChild(tr);
        filaIdx++;
        try { $('.select-prod').select2(); } catch(e) {}
    });

    // Escuchar cambios para cálculos
    document.getElementById('tablaProductos').addEventListener('input', e => {
        if(e.target.classList.contains('input-cant') || e.target.classList.contains('input-prec')) {
            const fila = e.target.closest('tr');
            const cant = parseFloat(fila.querySelector('.input-cant').value) || 0;
            const prec = parseFloat(fila.querySelector('.input-prec').value) || 0;
            fila.querySelector('.out-sub').value = (cant * prec).toFixed(2);
            recalcularTotales();
        }
    });

    // Eliminar fila
    document.getElementById('tablaProductos').addEventListener('click', e => {
        if(e.target.closest('.btn-del')) {
            if(document.querySelectorAll('.fila-producto').length > 1) {
                e.target.closest('tr').remove();
                recalcularTotales();
            } else {
                alert('La compra debe tener al menos un ítem.');
            }
        }
    });

    function recalcularTotales() {
        let st = 0;
        document.querySelectorAll('.out-sub').forEach(el => st += parseFloat(el.value) || 0);
        const igv = st * 0.18;
        const total = st + igv;

        document.getElementById('txt_sub').innerText = 'S/ ' + st.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('txt_igv').innerText = 'S/ ' + igv.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('txt_total').innerText = 'S/ ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        document.getElementById('h_sub').value = st.toFixed(2);
        document.getElementById('h_igv').value = igv.toFixed(2);
        document.getElementById('h_total').value = total.toFixed(2);
    }

    try { $('.select-prod').select2({ width: 'resolve' }); } catch(e) {}
</script>
@endsection