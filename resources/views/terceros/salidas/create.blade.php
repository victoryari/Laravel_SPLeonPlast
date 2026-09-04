@extends('layouts.app')
@section('title', 'Nueva Guía de Salida a Tercero')

@section('content')
<div class="container mx-auto max-w-5xl pb-8 md:pb-10" x-data="guiaSalidaForm()">
    <!-- Header de Página -->
    <x-page-header title="Nueva Guía de Salida a Tercero" subtitle="Registra el envío físico de inventario para maquila/servicios externos">
        <x-slot:actions>
            <a href="{{ route('terceros.salidas.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span class="hidden sm:inline ml-2">Volver</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <form id="form-guia-salida">
        @csrf
        
        <!-- 1. Datos Generales -->
        <div class="bg-white rounded-xl shadow-md border border-slate-200/80 p-5 md:p-6 mb-5">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                <i class="fas fa-file-export text-emerald-600 text-base"></i>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide">1. Datos Generales</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                        🏢 Proveedor Destino <span class="text-red-500">*</span>
                    </label>
                    <select name="proveedor_destino" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition bg-white shadow-xs" required>
                        <option value="">Seleccione Proveedor...</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->razon_social }}">{{ $prov->ruc }} - {{ $prov->razon_social }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                        📄 Guía N° <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="numero_guia" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition bg-white shadow-xs uppercase" placeholder="Ej. T001-00045" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                        📅 Fecha Emisión <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="fecha_emision" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition bg-white shadow-xs" required max="{{ date('Y-m-d') }}">
                </div>
                
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                        🏭 Almacén Origen <span class="text-red-500">*</span>
                    </label>
                    <select name="codigo_almacen_origen" @change="fetchProductos($event.target.value)" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition bg-white shadow-xs" required>
                        <option value="">Seleccione Almacén...</option>
                        @foreach($almacenes as $almacen)
                            <option value="{{ $almacen->codigo_almacen }}">{{ $almacen->codigo_almacen }} - {{ $almacen->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Detalle de Productos -->
        <div class="bg-white rounded-xl shadow-md border border-slate-200/80 p-5 md:p-6 mb-5">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                <i class="fas fa-boxes text-emerald-600 text-base"></i>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide">2. Detalle de Productos (PEPs)</h2>
            </div>

            <!-- Buscador y Agregar -->
            <div class="flex flex-col sm:flex-row sm:items-end gap-3 mb-4 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/80">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">📌 Buscar Producto PEP</label>
                    <select id="select-producto" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition bg-white shadow-xs">
                        <option value="">Seleccione un producto...</option>
                        <template x-for="prod in productosDisponibles" :key="prod.codigo">
                            <option :value="prod.codigo" :data-desc="prod.descripcion" x-text="prod.codigo + ' - ' + prod.descripcion"></option>
                        </template>
                    </select>
                </div>
                <button type="button" @click="agregarProducto()" class="btn-primary h-10 px-4 whitespace-nowrap">
                    <i class="fas fa-plus"></i>
                    <span>Agregar</span>
                </button>
            </div>

            <!-- Tabla de Detalles -->
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-left border-collapse min-w-[500px]">
                    <thead>
                        <tr class="bg-slate-800 text-white text-[11px] uppercase tracking-wider font-bold">
                            <th class="py-2.5 px-3 border-r border-slate-700/60 w-12 text-center">Item</th>
                            <th class="py-2.5 px-3 border-r border-slate-700/60">Código - Descripción</th>
                            <th class="py-2.5 px-3 border-r border-slate-700/60 w-36 text-center">Cant. (KG) <span class="text-red-400">*</span></th>
                            <th class="py-2.5 px-3 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="(item, index) in detalles" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                <td class="py-2 px-3 text-center text-xs font-bold text-slate-500" x-text="index + 1"></td>
                                <td class="py-2 px-3">
                                    <input type="hidden" :name="`productos[${index}][codigo]`" :value="item.codigo">
                                    <div class="text-xs font-bold text-slate-900" x-text="item.codigo"></div>
                                    <div class="text-xs text-slate-600 truncate max-w-xs lg:max-w-md" x-text="item.descripcion" :title="item.descripcion"></div>
                                </td>
                                <td class="py-2 px-3">
                                    <input type="number" step="0.01" min="0.01" class="w-full border border-slate-300 rounded-md text-xs text-center px-2 py-1.5 font-semibold text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary shadow-xs" :name="`productos[${index}][cantidad]`" x-model="item.cantidad" required>
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <button type="button" @click="eliminarProducto(index)" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Eliminar fila">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="detalles.length === 0">
                            <td colspan="4" class="py-8 px-4 text-center text-slate-400 text-xs bg-slate-50/50">
                                <i class="fas fa-list-ol text-2xl mb-2 opacity-50 block text-slate-400"></i>
                                Agregue productos a la guía
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Observaciones -->
        <div class="bg-white rounded-xl shadow-md border border-slate-200/80 p-5 md:p-6 mb-6">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                <i class="fas fa-comment-alt text-emerald-600 text-base"></i>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide">3. Observaciones</h2>
            </div>
            <textarea name="observaciones" rows="2.5" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition bg-white shadow-xs" placeholder="Anotaciones adicionales sobre la salida a terceros..."></textarea>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end space-x-3 pt-2">
            <a href="{{ route('terceros.salidas.index') }}" class="btn-secondary">Cancelar</a>
            <button type="button" @click="guardar()" class="btn-primary">
                <i class="fas fa-save"></i>
                <span>Guardar y Descontar Stock</span>
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
            productosDisponibles: [],
            counter: 0,

            fetchProductos(almacen) {
                if (!almacen) {
                    this.productosDisponibles = [];
                    return;
                }
                fetch(`{{ route('terceros.salidas.productos_con_stock') }}?almacen=${almacen}`)
                    .then(res => res.json())
                    .then(data => {
                        this.productosDisponibles = data;
                    });
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
