@extends('layouts.app')
@section('title', 'Nueva Guía de Salida a Tercero')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl" x-data="guiaSalidaForm()">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Nueva Guía de Salida a Tercero</h1>
            <p class="text-sm text-slate-500 mt-1">Registra el envío físico de inventario para maquila/servicios externos.</p>
        </div>
        <a href="{{ route('terceros.salidas.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    <form id="form-guia-salida">
        @csrf
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">1. Datos Generales</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Proveedor Destino <span class="text-red-500">*</span></label>
                    <select name="proveedor_destino" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20" required>
                        <option value="">Seleccione Proveedor...</option>
                        @foreach($proveedores as $prov)
                        <option value="{{ $prov->razon_social }}">{{ $prov->ruc }} - {{ $prov->razon_social }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Guía N° <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_guia" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Ej. T001-00045" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Fecha Emisión <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_emision" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20" required>
                </div>
                
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Almacén Origen <span class="text-red-500">*</span></label>
                    <select name="codigo_almacen_origen" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20" required>
                        <option value="">Seleccione Almacén...</option>
                        @foreach($almacenes as $almacen)
                        <option value="{{ $almacen->codigo_almacen }}">{{ $almacen->codigo_almacen }} - {{ $almacen->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">2. Detalle de Productos (PEPs)</h2>

            <div class="flex items-end gap-3 mb-5 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Buscar Producto PEP</label>
                    <select id="select-producto" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">Seleccione un producto...</option>
                        @foreach($productos as $prod)
                        <option value="{{ $prod->codigo }}" data-desc="{{ $prod->descripcion }}">
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
                <table class="w-full text-left border-collapse min-w-[500px]">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 text-xs uppercase tracking-wider font-bold">
                            <th class="p-3 border-b border-slate-200 w-12 text-center">Item</th>
                            <th class="p-3 border-b border-slate-200">Código - Descripción</th>
                            <th class="p-3 border-b border-slate-200 w-32 text-center">Cant. (KG) <span class="text-red-500">*</span></th>
                            <th class="p-3 border-b border-slate-200 w-16 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in detalles" :key="item.id">
                            <tr class="hover:bg-slate-50 group">
                                <td class="p-2 text-center text-xs font-bold text-slate-400" x-text="index + 1"></td>
                                <td class="p-2">
                                    <input type="hidden" :name="`productos[${index}][codigo]`" :value="item.codigo">
                                    <div class="text-sm font-bold text-slate-800" x-text="item.codigo"></div>
                                    <div class="text-xs text-slate-500 truncate w-64 lg:w-auto" x-text="item.descripcion" :title="item.descripcion"></div>
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" min="0.01" class="w-full border border-slate-200 rounded-md text-sm text-center px-2 py-1 font-semibold focus:border-primary focus:ring-1 focus:ring-primary" :name="`productos[${index}][cantidad]`" x-model="item.cantidad" required>
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="eliminarProducto(index)" class="text-slate-300 hover:text-red-500 transition-colors p-1" title="Eliminar fila">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="detalles.length === 0">
                            <td colspan="4" class="p-8 text-center text-slate-400 text-sm">
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
            <a href="{{ route('terceros.salidas.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">Cancelar</a>
            <button type="button" @click="guardar()" class="px-5 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="fas fa-save mr-2"></i>Guardar y Descontar Stock
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('guiaSalidaForm', () => ({
            detalles: [],
            counter: 0,

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
                    alert('El producto ya está en la lista.');
                    return;
                }

                this.detalles.push({
                    id: this.counter++,
                    codigo: select.value,
                    descripcion: option.getAttribute('data-desc'),
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
                const form = document.getElementById('form-guia-salida');
                if(form.reportValidity()) {
                    if(confirm('¿Está seguro de registrar esta Guía de Salida a Tercero? Se actualizará el inventario inmediatamente.')){
                        let formData = new FormData(form);
                        fetch("{{ route('terceros.salidas.store') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                alert(data.message);
                                window.location.href = "{{ route('terceros.salidas.index') }}";
                            } else {
                                alert(data.message || 'Ocurrió un error.');
                            }
                        })
                        .catch(error => {
                            alert('Error de conexión.');
                        });
                    }
                }
            }
        }));
    });
</script>
@endpush
@endsection
