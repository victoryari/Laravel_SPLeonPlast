@extends('layouts.app')
@section('title', 'Composición de Fórmula')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Estilizamos Select2 para que coincida con Tailwind */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        display: flex;
        align-items: center;
        background-color: #fff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; right: 8px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { 
        color: #374151 !important; padding-left: 0.75rem !important; line-height: normal !important; font-size: 0.875rem !important; 
    }
    /* Variante oscura para la cabecera de la fórmula */
    .select-dark + .select2-container .select2-selection--single {
        background-color: #1e293b !important; /* bg-slate-800 */
        border-color: #475569 !important; /* border-slate-600 */
    }
    .select-dark + .select2-container .select2-selection--single .select2-selection__rendered {
        color: #ffffff !important;
    }
</style>

<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Diseño de Fórmula</h1>
        </div>
        <a href="{{ route('formulas.index') }}" class="text-sm md:text-base text-gray-600 hover:text-purple-600 transition font-medium flex items-center w-fit">
            <i class="fas fa-arrow-left mr-2"></i> Volver al Maestro
        </a>
        
    </div>

    <form action="{{ route('formulas.storeComposicion', $formula->codigo) }}" method="POST" id="formComposicion">
        @csrf
        
        <div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-xl shadow-lg p-4 md:p-6 mb-4 md:mb-6 text-white">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 md:gap-6">
                <div class="flex-1 w-full">
                    <p class="text-slate-300 text-xs md:text-sm font-semibold uppercase tracking-wider mb-1">Fórmula en proceso</p>
                    <h2 class="text-2xl md:text-3xl font-extrabold">{{ $formula->codigo }}</h2>
                    <p class="text-slate-200 mt-1 text-sm md:text-lg">{{ $formula->descripcion }}</p>
                </div>

                <div class="bg-slate-900/50 p-3 md:p-4 rounded-xl border border-slate-600 w-full lg:w-auto">
                    <label class="block text-[10px] md:text-xs font-bold text-slate-400 uppercase mb-2">Asignación Global de Molde</label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="w-full sm:w-72 text-gray-800">
                            <select id="moldeGlobal" class="w-full select-dark">
                                <option value="">Seleccione molde...</option>
                                @foreach($moldes as $molde)
                                    <option value="{{ $molde->codigo }}">{{ $molde->descripcion }} ({{ $molde->codigo }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" onclick="aplicarMoldeGlobal()" class="w-full sm:w-auto bg-purple-600 hover:bg-purple-700 text-white px-4 h-[42px] rounded-lg text-sm font-bold shadow transition flex items-center justify-center whitespace-nowrap">
                            <i class="fas fa-sync-alt mr-2"></i> Aplicar
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4 md:mt-6 pt-4 md:pt-6 border-t border-slate-600 flex justify-start lg:justify-end">
                <button type="button" onclick="abrirModal()" class="w-fit bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 md:px-6 rounded-lg shadow transition transform hover:scale-105 flex items-center justify-center text-sm md:text-base">
                    <i class="fas fa-plus-circle mr-2 text-lg"></i> Agregar Insumo
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-4 md:mb-6 w-full">
            <div class="overflow-x-auto pb-2">
                <table class="w-full text-left whitespace-nowrap min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 border-b border-gray-200 text-[11px] md:text-xs">
                            <th class="px-3 md:px-6 py-2 md:py-4 font-bold uppercase tracking-wider">Producto (Materia Prima)</th>
                            <th class="px-3 md:px-6 py-2 md:py-4 font-bold uppercase tracking-wider">Tipo</th>
                            <th class="px-3 md:px-6 py-2 md:py-4 font-bold uppercase tracking-wider text-center">C. Nominal</th>
                            <th class="px-3 md:px-6 py-2 md:py-4 font-bold uppercase tracking-wider text-center">C. Real</th>
                            <th class="px-3 md:px-6 py-2 md:py-4 font-bold uppercase tracking-wider">U.M.</th>
                            <th class="px-3 md:px-6 py-2 md:py-4 font-bold uppercase tracking-wider">Molde Asignado</th>
                            <th class="px-3 md:px-6 py-2 md:py-4 font-bold uppercase tracking-wider text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs md:text-sm" id="tbComposicion">
                        @foreach($composiciones as $idx => $comp)
                            <tr class="hover:bg-slate-50 transition duration-150" data-id="row-{{ $idx }}">
                                <td class="px-3 md:px-6 py-2 md:py-4 text-producto">{{ $comp->producto ? $comp->producto->descripcion : $comp->codigo_producto }}</td>
                                <td class="px-3 md:px-6 py-2 md:py-4 text-tipo-desc">{{ $comp->producto && $comp->producto->tipo ? $comp->producto->tipo->descripcion : $comp->codigo_tipo_producto }}</td>
                                <td class="px-3 md:px-6 py-2 md:py-4 text-center text-nominal">{{ number_format($comp->cantidad_nominal, 4, '.', '') }}</td>
                                <td class="px-3 md:px-6 py-2 md:py-4 text-center font-bold text-real">{{ number_format($comp->cantidad_real, 4, '.', '') }}</td>
                                <td class="px-3 md:px-6 py-2 md:py-4 text-unidad">{{ $comp->codigo_unidad_medida ?? 'N/A' }}</td>
                                <td class="px-3 md:px-6 py-2 md:py-4 font-semibold text-purple-600 text-molde">{{ $comp->codigo_molde ?? 'Sin Molde' }}</td>
                                <td class="px-3 md:px-6 py-2 md:py-4 text-center space-x-2 md:space-x-3">
                                    <input type="hidden" name="productos[]" value="{{ $comp->codigo_producto }}" class="input-producto">
                                    <input type="hidden" name="tipos[]" value="{{ $comp->codigo_tipo_producto }}" class="input-tipo">
                                    <input type="hidden" name="cantidades_nominales[]" value="{{ $comp->cantidad_nominal }}" class="input-nominal">
                                    <input type="hidden" name="cantidades_reales[]" value="{{ $comp->cantidad_real }}" class="input-real">
                                    <input type="hidden" name="unidades[]" value="{{ $comp->codigo_unidad_medida }}" class="input-unidad">
                                    <input type="hidden" name="moldes[]" value="{{ $comp->codigo_molde }}" class="input-molde">
                                    <input type="hidden" class="input-tipo-desc" value="{{ $comp->producto && $comp->producto->tipo ? $comp->producto->tipo->descripcion : '' }}">
                                    
                                    <button type="button" class="text-primary hover:text-primary transition p-1" onclick="abrirModal('row-{{ $idx }}')">
                                        <i class="fas fa-edit text-base md:text-lg"></i>
                                    </button>
                                    <button type="button" class="text-red-500 hover:text-red-700 transition btn-eliminar p-1">
                                        <i class="fas fa-trash-alt text-base md:text-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="msgVacio" class="{{ $composiciones->isEmpty() ? 'flex' : 'hidden' }} flex-col items-center justify-center py-8 md:py-12 text-center px-4">
                <div class="h-12 w-12 md:h-16 md:w-16 bg-slate-100 rounded-full flex items-center justify-center mb-3 md:mb-4 text-slate-400">
                    <i class="fas fa-layer-group text-xl md:text-2xl"></i>
                </div>
                <p class="text-slate-500 text-sm md:text-base">No hay componentes en esta fórmula.</p>
            </div>
            <div class="flex justify-between items-center bg-white p-3 border-t border-gray-100" id="paginationWrapper" style="display: none;">
                <div class="text-xs md:text-sm text-gray-500" id="paginationInfo">
                    Mostrando <span id="pageStart"></span> a <span id="pageEnd"></span> de <span id="pageTotal"></span> registros
                </div>
                <div class="flex gap-1 flex-wrap" id="paginationButtons"></div>
            </div>
        </div>
        
        <div class="bg-white p-3 md:p-4 rounded-xl shadow-lg border border-gray-200 flex justify-end">
            <button type="submit" class="w-fit bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 md:py-3 px-6 md:px-10 rounded-lg shadow-md transition transform hover:-translate-y-0.5 text-sm md:text-base">
                <i class="fas fa-save mr-2"></i> Finalizar Composición
            </button>
        </div>
    </form>
</div>

<div id="modalComponente" class="fixed inset-0 bg-slate-900 bg-opacity-60 hidden z-50 flex items-center justify-center backdrop-blur-sm px-2 py-4">
    <div class="bg-white rounded-xl md:rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[95vh] md:max-h-[90vh]">
        
        <div class="bg-slate-50 px-4 md:px-6 py-3 md:py-4 border-b border-gray-200 flex justify-between items-center flex-shrink-0">
            <h3 class="text-lg md:text-xl font-bold text-gray-800" id="modalTitle">Nuevo Componente</h3>
            <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-red-500 transition p-2"><i class="fas fa-times text-lg md:text-xl"></i></button>
        </div>
        
        <form id="formModal" class="p-4 md:p-6 overflow-y-auto flex-1">
            <input type="hidden" id="modalRowId">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                <div class="col-span-1 sm:col-span-2 text-gray-800">
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Materia Prima / Producto <span class="text-red-500">*</span></label>
                    <select id="modalProducto" class="w-full" required>
                        <option value="">Seleccione producto...</option>
                    </select>
                </div>

                <div class="text-gray-800">
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Tipo de Componente</label>
                    <input type="text" id="modalTipoDesc" class="w-full border border-gray-300 rounded-lg px-3 py-2 h-[42px] text-sm md:text-base bg-gray-100 cursor-not-allowed outline-none" readonly placeholder="Automático...">
                    <input type="hidden" id="modalTipo">
                </div>

                <div class="text-gray-800">
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Unidad de Medida</label>
                    <select id="modalUnidad" class="w-full border border-gray-300 rounded-lg px-3 py-2 h-[42px] text-sm md:text-base">
                        @foreach($unidades as $um)
                            <option value="{{ $um->codigo }}">{{ $um->descripcion }} ({{ $um->codigo }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">C. Nominal</label>
                    <input type="number" step="0.0001" id="modalNominal" class="w-full border border-gray-300 rounded-lg px-3 py-2 h-[42px] text-sm md:text-base outline-none focus:ring-2 focus:ring-purple-500" placeholder="0.0000">
                </div>

                <div>
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">C. Real <span class="text-red-500">*</span></label>
                    <input type="number" step="0.0001" id="modalReal" class="w-full border-2 border-purple-200 rounded-lg px-3 py-2 h-[42px] text-sm md:text-base font-bold outline-none focus:ring-2 focus:ring-purple-500" placeholder="0.0000" required>
                </div>

                <div class="col-span-1 sm:col-span-2 text-gray-800">
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Molde Individual <span class="text-[10px] md:text-xs text-gray-400">(Opcional)</span></label>
                    <select id="modalMolde" class="w-full border border-gray-300 rounded-lg px-3 py-2 h-[42px] text-sm md:text-base">
                        <option value="">Ninguno</option>
                        @foreach($moldes as $molde)
                            <option value="{{ $molde->codigo }}">{{ $molde->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 pt-4 md:pt-5 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="cerrarModal()" class="w-fit bg-gray-100 text-gray-700 font-semibold py-2 px-6 rounded-lg transition text-sm md:text-base">Cancelar</button>
                <button type="submit" class="w-fit bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg shadow transition text-sm md:text-base">Confirmar ítem</button>
            </div>
        </form>
    </div>
</div>

<template id="rowTemplate">
    <tr class="hover:bg-slate-50 transition border-b border-gray-50">
        <td class="px-3 md:px-6 py-2 md:py-4 text-producto"></td>
        <td class="px-3 md:px-6 py-2 md:py-4 text-tipo-desc"></td>
        <td class="px-3 md:px-6 py-2 md:py-4 text-center text-nominal"></td>
        <td class="px-3 md:px-6 py-2 md:py-4 text-center font-bold text-real"></td>
        <td class="px-3 md:px-6 py-2 md:py-4 text-unidad"></td>
        <td class="px-3 md:px-6 py-2 md:py-4 font-semibold text-purple-600 text-molde"></td>
        <td class="px-3 md:px-6 py-2 md:py-4 text-center space-x-2 md:space-x-3">
            <input type="hidden" name="productos[]" class="input-producto">
            <input type="hidden" name="tipos[]" class="input-tipo">
            <input type="hidden" name="cantidades_nominales[]" class="input-nominal">
            <input type="hidden" name="cantidades_reales[]" class="input-real">
            <input type="hidden" name="unidades[]" class="input-unidad">
            <input type="hidden" name="moldes[]" class="input-molde">
            <input type="hidden" class="input-tipo-desc">
            
            <button type="button" class="text-primary hover:text-primary transition btn-editar p-1"><i class="fas fa-edit text-base md:text-lg"></i></button>
            <button type="button" class="text-red-500 hover:text-red-700 transition btn-eliminar p-1"><i class="fas fa-trash-alt text-base md:text-lg"></i></button>
        </td>
    </tr>
</template>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar Select2
        $('#moldeGlobal').select2({ width: '100%' });
        $('#modalProducto').select2({
            width: '100%',
            dropdownParent: $('#modalComponente'),
            ajax: {
                url: '/productos/search-ajax',
                dataType: 'json',
                delay: 300,
                data: function(p) { return { q: p.term }; },
                processResults: function(d) { return { results: d }; },
                cache: true
            },
            minimumInputLength: 0,
            placeholder: 'Seleccione producto...'
        });

        $('#modalProducto').on('select2:select', function(e) {
            const d = e.params.data;
            $('#modalTipo').val(d.codigo_tipo_producto || '');
            $('#modalTipoDesc').val(d.descripcion_tipo_producto || d.codigo_tipo_producto || '');
        });
    });

    const modal = document.getElementById('modalComponente');
    const tbComposicion = document.getElementById('tbComposicion');
    const msgVacio = document.getElementById('msgVacio');
    const template = document.getElementById('rowTemplate');
    let rowCounter = 1000;
    let currentPage = 1;
    const rowsPerPage = 10;

    function updatePagination() {
        const rows = Array.from(tbComposicion.querySelectorAll('tr'));
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
        
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        let start = (currentPage - 1) * rowsPerPage;
        let end = start + rowsPerPage;

        rows.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        if (totalRows > rowsPerPage) {
            document.getElementById('paginationWrapper').style.display = 'flex';
            document.getElementById('pageStart').innerText = totalRows === 0 ? 0 : start + 1;
            document.getElementById('pageEnd').innerText = Math.min(end, totalRows);
            document.getElementById('pageTotal').innerText = totalRows;

            let buttonsHtml = '';
            buttonsHtml += `<button type="button" onclick="changePage(${currentPage - 1})" class="px-3 py-1 rounded border ${currentPage === 1 ? 'text-gray-400 bg-gray-50 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'}" ${currentPage === 1 ? 'disabled' : ''}>Anterior</button>`;
            
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    buttonsHtml += `<button type="button" class="px-3 py-1 rounded bg-purple-600 text-white font-bold">${i}</button>`;
                } else if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    buttonsHtml += `<button type="button" onclick="changePage(${i})" class="px-3 py-1 rounded border text-gray-700 hover:bg-gray-100">${i}</button>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    buttonsHtml += `<span class="px-2 py-1">...</span>`;
                }
            }

            buttonsHtml += `<button type="button" onclick="changePage(${currentPage + 1})" class="px-3 py-1 rounded border ${currentPage === totalPages ? 'text-gray-400 bg-gray-50 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'}" ${currentPage === totalPages ? 'disabled' : ''}>Siguiente</button>`;
            
            document.getElementById('paginationButtons').innerHTML = buttonsHtml;
        } else {
            document.getElementById('paginationWrapper').style.display = 'none';
        }
    }

    function changePage(page) {
        currentPage = page;
        updatePagination();
    }

    $(document).ready(function() {
        updatePagination();
    });

    function aplicarMoldeGlobal() {
        const moldeCodigo = $('#moldeGlobal').val();
        const moldeTexto = $('#moldeGlobal option:selected').text();
        if (!moldeCodigo) { window.toast('Seleccione un molde global primero.', 'warning'); return; }

        const filas = tbComposicion.querySelectorAll('tr');
        if (filas.length === 0) { window.toast('La grilla está vacía.', 'warning'); return; }

        if (confirm(`¿Asignar "${moldeTexto}" a todos los items?`)) {
            filas.forEach(f => {
                f.querySelector('.text-molde').innerText = moldeCodigo;
                f.querySelector('.input-molde').value = moldeCodigo;
            });
        }
    }

    function abrirModal(rowId = null) {
        const form = document.getElementById('formModal');
        form.reset();
        
        if (rowId) {
            document.getElementById('modalTitle').innerText = 'Editar Componente';
            const fila = document.querySelector(`tr[data-id="${rowId}"]`);
            document.getElementById('modalRowId').value = rowId;
            document.getElementById('modalNominal').value = fila.querySelector('.input-nominal').value;
            document.getElementById('modalReal').value = fila.querySelector('.input-real').value;
            document.getElementById('modalUnidad').value = fila.querySelector('.input-unidad').value;
            document.getElementById('modalMolde').value = fila.querySelector('.input-molde').value;
            
            $('#modalProducto').val(fila.querySelector('.input-producto').value).trigger('change');
        } else {
            document.getElementById('modalTitle').innerText = 'Nuevo Componente';
            document.getElementById('modalRowId').value = '';
            $('#modalProducto').val('').trigger('change');
            document.getElementById('modalMolde').value = $('#moldeGlobal').val();
        }
        modal.classList.remove('hidden');
    }

    function cerrarModal() { modal.classList.add('hidden'); }

    document.getElementById('formModal').addEventListener('submit', function(e) {
        e.preventDefault();
        const rowId = document.getElementById('modalRowId').value;
        
        const data = {
            prodVal: $('#modalProducto').val(),
            prodTxt: $('#modalProducto option:selected').text(),
            tipoVal: $('#modalTipo').val(),
            tipoTxt: $('#modalTipoDesc').val(),
            nominal: parseFloat(document.getElementById('modalNominal').value || 0).toFixed(4),
            real: parseFloat(document.getElementById('modalReal').value).toFixed(4),
            uniVal: document.getElementById('modalUnidad').value,
            molde: document.getElementById('modalMolde').value || 'Sin Molde'
        };

        let fila;
        if (rowId) {
            fila = document.querySelector(`tr[data-id="${rowId}"]`);
        } else {
            rowCounter++;
            const newId = `row-${rowCounter}`;
            fila = template.content.cloneNode(true).querySelector('tr');
            fila.dataset.id = newId;
            fila.querySelector('.btn-editar').onclick = () => abrirModal(newId);
            msgVacio.classList.add('hidden');
        }

        fila.querySelector('.text-producto').innerText = data.prodTxt;
        fila.querySelector('.text-tipo-desc').innerText = data.tipoTxt;
        fila.querySelector('.text-nominal').innerText = data.nominal;
        fila.querySelector('.text-real').innerText = data.real;
        fila.querySelector('.text-unidad').innerText = data.uniVal;
        fila.querySelector('.text-molde').innerText = data.molde;

        fila.querySelector('.input-producto').value = data.prodVal;
        fila.querySelector('.input-tipo').value = data.tipoVal;
        fila.querySelector('.input-tipo-desc').value = data.tipoTxt;
        fila.querySelector('.input-nominal').value = data.nominal;
        fila.querySelector('.input-real').value = data.real;
        fila.querySelector('.input-unidad').value = data.uniVal;
        fila.querySelector('.input-molde').value = data.molde === 'Sin Molde' ? '' : data.molde;

        if (!rowId) tbComposicion.appendChild(fila);
        updatePagination();
        cerrarModal();
    });

    tbComposicion.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-eliminar');
        if (btn) {
            btn.closest('tr').remove();
            if (tbComposicion.children.length === 0) msgVacio.classList.remove('hidden');
            updatePagination();
        }
    });
</script>
@endsection