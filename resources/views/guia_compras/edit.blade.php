@extends('layouts.app')
@section('title', 'Editar Guía de Remisión')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl" x-data="guiaForm()">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Editar Guía de Remisión</h1>
            <p class="text-sm text-slate-500 mt-1">Registra la recepción física en el almacén de tránsito.</p>
        </div>
        <a href="{{ route('guia_compras.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    @if ($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
        <h3 class="text-red-800 font-bold text-sm mb-2">Se encontraron los siguientes errores:</h3>
        <ul class="list-disc list-inside text-xs text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('guia_compras.update', $guia->id_guia) }}" method="POST" id="form-guia">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">1. Datos Generales</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Proveedor <span class="text-red-500">*</span></label>
                    <select name="ruc_proveedor" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20" x-model="ruc_proveedor" @change="updateProveedorName" required>
                        <option value="">Seleccione un proveedor...</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->ruc }}" data-razon="{{ $prov->razon_social }}">{{ $prov->ruc }} - {{ $prov->razon_social }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="proveedor" x-model="proveedor_nombre">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Guía N° <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_guia" value="{{ old('numero_guia', $guia->numero_guia) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Ej. T001-00045" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Fecha Emisión <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $guia->fecha_emision) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20" required>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">2. Detalle de Productos</h2>

            <div class="flex items-end gap-3 mb-5 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Buscar Producto</label>
                    <select id="select-producto" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">Seleccione un producto...</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->codigo }}" data-desc="{{ $prod->descripcion }}" data-um="{{ $prod->unidad_medida_codigo ?? 'NIU' }}">
                                {{ $prod->codigo }} - {{ $prod->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="button" @click="agregarProducto()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition whitespace-nowrap">
                    <i class="fas fa-plus mr-1"></i> Agregar
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 text-xs uppercase tracking-wider font-bold">
                            <th class="p-3 border-b border-slate-200 w-12 text-center">Item</th>
                            <th class="p-3 border-b border-slate-200">Código - Descripción</th>
                            <th class="p-3 border-b border-slate-200 w-32 text-center">Cant. <span class="text-red-500">*</span></th>
                            <th class="p-3 border-b border-slate-200 w-24 text-center">U.M.</th>
                            <th class="p-3 border-b border-slate-200 w-32 text-center">Lote</th>
                            <th class="p-3 border-b border-slate-200 w-32 text-center">Venc.</th>
                            <th class="p-3 border-b border-slate-200 w-16 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="tabla-detalles">
                        <template x-for="(item, index) in detalles" :key="item.id">
                            <tr class="hover:bg-slate-50 group">
                                <td class="p-2 text-center text-xs font-bold text-slate-400" x-text="index + 1"></td>
                                <td class="p-2">
                                    <input type="hidden" :name="`productos[${index}][codigo_producto]`" :value="item.codigo">
                                    <div class="text-sm font-bold text-slate-800" x-text="item.codigo"></div>
                                    <div class="text-xs text-slate-500 truncate w-48 lg:w-auto" x-text="item.descripcion" :title="item.descripcion"></div>
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" min="0.01" class="w-full border border-slate-200 rounded-md text-sm text-center px-2 py-1 font-semibold focus:border-primary focus:ring-1 focus:ring-primary" :name="`productos[${index}][cantidad]`" x-model="item.cantidad" required>
                                </td>
                                <td class="p-2 text-center">
                                    <select class="w-full border border-slate-200 bg-white rounded-md text-xs text-center focus:border-primary focus:ring-1 focus:ring-primary px-1 py-1" :name="`productos[${index}][codigo_unidad_medida]`" x-model="item.um">
                                        @foreach($unidades_medida as $um)
                                            <option value="{{ $um->codigo }}">{{ $um->codigo }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input type="text" class="w-full border border-slate-200 rounded-md text-xs text-center px-2 py-1 focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Lote..." :name="`productos[${index}][lote]`" x-model="item.lote">
                                </td>
                                <td class="p-2">
                                    <input type="date" class="w-full border border-slate-200 rounded-md text-xs text-center px-1 py-1 focus:border-primary focus:ring-1 focus:ring-primary" :name="`productos[${index}][fecha_vencimiento]`" x-model="item.vencimiento">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="eliminarProducto(index)" class="text-slate-300 hover:text-red-500 transition-colors p-1" title="Eliminar fila">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="detalles.length === 0">
                            <td colspan="7" class="p-8 text-center text-slate-400 text-sm">
                                <i class="fas fa-list-ol text-2xl mb-2 opacity-50 block"></i>
                                Agregue productos a la guía
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-slate-800 mb-3 border-b border-slate-100 pb-2">3. Observaciones</h2>
            <textarea name="observaciones" rows="2" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Anotaciones adicionales..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pb-10">
            <a href="{{ route('guia_compras.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">Cancelar</a>
            <button type="button" @click="guardar()" class="px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="fas fa-save mr-2"></i>Guardar e Ingresar a Kardex
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('guiaForm', () => ({
            ruc_proveedor: '{{ old('ruc_proveedor', $guia->ruc_proveedor) }}',
            proveedor_nombre: '{{ addslashes(old('proveedor', $guia->proveedor)) }}',
            detalles: [
                @foreach($guia->detalles as $index => $det)
                {
                    id: {{ $index }},
                    codigo: '{{ $det->codigo_producto }}',
                    descripcion: '{{ addslashes($det->producto->descripcion ?? $det->descripcion_producto ?? '') }}',
                    um: '{{ $det->codigo_unidad_medida }}',
                    lote: '{{ $det->lote }}',
                    vencimiento: '{{ $det->fecha_vencimiento }}',
                    cantidad: {{ floatval($det->cantidad) }}
                },
                @endforeach
            ],
            counter: {{ count($guia->detalles) }},
            
            updateProveedorName() {
                const select = document.querySelector('select[name="ruc_proveedor"]');
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    this.proveedor_nombre = selectedOption.getAttribute('data-razon');
                } else {
                    this.proveedor_nombre = '';
                }
            },

            agregarProducto() {
                const select = document.getElementById('select-producto');
                if(!select.value) {
                    alert('Por favor seleccione un producto primero.');
                    return;
                }
                const option = select.options[select.selectedIndex];
                
                // Verificar si ya existe
                const existe = this.detalles.find(d => d.codigo === select.value);
                if(existe) {
                    alert('El producto ya está en la lista. Si son diferentes lotes, agréguelo y cambie el lote.');
                }

                this.detalles.push({
                    id: this.counter++,
                    codigo: select.value,
                    descripcion: option.getAttribute('data-desc'),
                    um: option.getAttribute('data-um'),
                    lote: '',
                    vencimiento: '',
                    cantidad: 1
                });
                select.value = '';
            },

            eliminarProducto(index) {
                this.detalles.splice(index, 1);
            },

            guardar() {
                if(this.detalles.length === 0) {
                    alert('Debe agregar al menos un producto.');
                    return;
                }
                const form = document.getElementById('form-guia');
                if(form.reportValidity()) {
                    if(confirm('¿Está seguro de registrar esta Guía de Remisión? Se actualizará el inventario inmediatamente.')){
                        form.submit();
                    }
                }
            }
        }));
    });

</script>

@if(session('success_ask'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if(!confirm("{{ session('success_ask') }}")) {
                window.location.href = "{{ route('guia_compras.index') }}";
            }
        }, 100);
    });

</script>
@endif

@endpush
@endsection
