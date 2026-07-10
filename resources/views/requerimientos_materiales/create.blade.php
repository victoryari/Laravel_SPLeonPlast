@extends('layouts.app')
@section('title', 'Nuevo Requerimiento de Materiales')

@section('content')
<link href="/vendor/select2/select2.min.css" rel="stylesheet" />
<div class="container mx-auto px-4 py-6 max-w-7xl">

    <x-page-header title="Nuevo Requerimiento de Materiales" subtitle="Solicite la transferencia de materiales entre almacenes">
        <x-slot:actions>
            <a href="{{ route('requerimientos_materiales.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ route('requerimientos_materiales.store') }}" method="POST" id="formRequerimiento">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                <x-card>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-clipboard-list text-primary"></i> Datos del Requerimiento
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <x-form-group label="Fecha de Requerimiento" required>
                                    <input type="date" name="fecha_requerimiento" 
                                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none" 
                                           value="{{ date('Y-m-d') }}" required max="{{ date('Y-m-d') }}">
                                </x-form-group>
                            </div>
                            <div>
                                <x-form-group label="Orden de Producción (opcional)">
                                    <select name="idop" id="select_idop" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                                        <option value="">-- Requerimiento Libre --</option>
                                        @foreach($ordenes as $op)
                                            <option value="{{ $op->idop }}">{{ $op->codigo_op ?? 'OP#' . $op->idop }}</option>
                                        @endforeach
                                    </select>
                                </x-form-group>
                            </div>
                            <div id="div_proceso" class="hidden">
                                <x-form-group label="Proceso a Abastecer">
                                    <select name="id_proceso" id="select_proceso" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                                        <option value="">-- Seleccione Proceso --</option>
                                    </select>
                                </x-form-group>
                            </div>
                            <div>
                                <x-form-group label="Motivo">
                                    <input type="text" name="motivo" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none" placeholder="Ej: Abastecimiento para producción" maxlength="500">
                                </x-form-group>
                            </div>
                        </div>
                        <x-form-group label="Observaciones">
                            <textarea name="observaciones" class="input-field" rows="2" placeholder="Notas adicionales..."></textarea>
                        </x-form-group>
                    </div>
                </x-card>

                <x-card>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-boxes text-primary"></i> Productos Solicitados
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto mb-3">
                            <table class="w-full text-left text-xs" id="tablaProductos">
                                <colgroup>
                                    <col class="w-[50%]">
                                    <col class="w-[20%]">
                                    <col class="w-[15%]">
                                    <col class="w-[15%]">
                                </colgroup>
                                <thead>
                                    <tr class="bg-slate-100 text-[11px] uppercase text-slate-500 tracking-wider">
                                        <th class="p-2 font-semibold">Producto</th>
                                        <th class="p-2 font-semibold text-center">Cantidad</th>
                                        <th class="p-2 font-semibold text-center">U.M.</th>
                                        <th class="p-2 font-semibold text-center"><i class="fas fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100" id="tbodyProductos">
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="btnAgregarFila" class="w-full py-2 border-2 border-dashed border-slate-300 rounded-lg text-xs text-slate-500 font-semibold hover:border-primary hover:text-primary hover:bg-primary-50 transition-all flex justify-center items-center gap-1">
                            <i class="fas fa-plus-circle"></i> Agregar producto
                        </button>
                    </div>
                </x-card>
            </div>

            <div class="lg:col-span-4 xl:col-span-3">
                <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 sticky top-6 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i class="fas fa-check-circle text-primary"></i> Acciones
                        </h2>
                        <button type="submit" class="w-full btn-primary py-3 rounded-xl font-bold text-base flex justify-center gap-2">
                            <i class="fas fa-save"></i> Guardar Borrador
                        </button>
                        <p class="text-xs text-slate-400 text-center mt-3">Luego podrá enviarlo a aprobación.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<x-modal id="modalProducto" title="Seleccionar Producto">
    <div class="space-y-4">
        <x-form-group label="Buscar producto">
            <select id="selectProductoModal" class="w-full input-field" style="width: 100%;"></select>
        </x-form-group>
        <x-slot:footer>
            <button type="button" id="btnAgregarProductoModal" class="btn-primary w-full">
                <i class="fas fa-plus"></i> Agregar a la lista
            </button>
        </x-slot:footer>
    </div>
</x-modal>

<script src="/vendor/jquery/jquery.min.js"></script>
<script src="/vendor/select2/select2.min.js"></script>
<script>
    let filaIdx = 0;
    let tablaBody;
    let almacenesData;

    function getProductName(text) {
        const m = text.match(/\]\s*(.*)/);
        return m ? m[1] : text;
    }

    function generarTemplateHTML(idx, codigo, nombre, unidad) {
        return `
        <tr class="fila-producto">
            <td class="p-2">
                <span class="texto-prod text-xs font-medium text-slate-800 truncate block" title="${nombre}">${nombre}</span>
                <input type="hidden" class="input-cod" name="productos[${idx}][codigo_producto]" value="${codigo}">
            </td>
            <td class="p-2 text-center">
                <input type="number" step="0.01" min="0.01" class="w-full border border-slate-200 bg-slate-50 text-center rounded-md text-xs px-2" style="height:32px" name="productos[${idx}][cantidad]" required>
            </td>
            <td class="p-2 text-center">
                <span class="text-xs text-slate-500 font-semibold">${unidad || 'N/A'}</span>
            </td>
            <td class="p-2 text-center">
                <button type="button" class="text-slate-400 hover:text-red-500 btn-del text-xs" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>
        `;
    }

    function agregarFila(producto) {
        const idx = filaIdx++;
        const nombre = getProductName(producto.text);
        const unidad = producto.unidad_medida;
        const html = generarTemplateHTML(idx, producto.id, nombre, unidad);
        tablaBody.insertAdjacentHTML('beforeend', html);
    }

    function cerrarModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    $(document).ready(function () {
        tablaBody = document.getElementById('tbodyProductos');

        $('#select_idop').on('change', function() {
            const idop = $(this).val();
            const selectProceso = $('#select_proceso');
            const divProceso = $('#div_proceso');
            
            selectProceso.empty().append('<option value="">-- Seleccione Proceso --</option>');
            
            if (idop) {
                divProceso.removeClass('hidden');
                // Fetch procesos
                $.get(`/produccion/ordenes/${idop}/procesos-ajax`, function(data) {
                    data.forEach(function(p) {
                        selectProceso.append(`<option value="${p.id}">${p.descripcion_proceso}</option>`);
                    });
                });
            } else {
                divProceso.addClass('hidden');
            }
        });

        if (typeof $().select2 !== 'undefined') {
            $('#selectProductoModal').select2({
                dropdownParent: $('#modalProducto'),
                ajax: {
                    url: '/productos/search-ajax',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return { q: params.term || '', page: params.page || 1 };
                    },
                    processResults: function (data) {
                        return { results: data, pagination: { more: false } };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                placeholder: 'Buscar producto por código o descripción...',
                language: 'es'
            });
        }

        $('#btnAgregarFila').on('click', function () {
            $('#selectProductoModal').val(null).trigger('change');
            window.abrirModal('modalProducto');
        });

        $('#btnAgregarProductoModal').on('click', function () {
            const selected = $('#selectProductoModal').select2('data');
            if (selected.length) {
                agregarFila(selected[0]);
                cerrarModal('modalProducto');
            }
        });

        $(document).on('click', '.btn-del', function () {
            $(this).closest('tr').remove();
        });
    });

    window.abrirModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    };
</script>
@endsection
