@extends('layouts.app')
@section('title', 'Nueva Transferencia')

@section('content')
<div class="container mx-auto pb-8 md:pb-10" x-data="transferenciaApp()">
    
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('inventario.transferencias.index') }}" class="hover:text-blue-600 transition"><i class="fas fa-arrow-left"></i> Volver a Historial</a>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Registrar Transferencia</h1>
        </div>
        <button type="button" @click="submitForm" :disabled="isSubmitting || items.length === 0" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md shadow-blue-200 transition-all disabled:opacity-50">
            <span x-show="!isSubmitting"><i class="fas fa-save"></i> Procesar Transferencia</span>
            <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
        </button>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6">
            <ul class="list-disc pl-5 text-sm font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formTransferencia" method="POST" action="{{ route('inventario.transferencias.store') }}">
        @csrf
        
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 border-b pb-2">Detalles Generales</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fecha Transferencia</label>
                    <input type="date" name="fecha_transferencia" value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Almacén de Origen</label>
                    <select name="codigo_almacen_origen" x-model="origen" @change="fetchLotes()" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50" required>
                        <option value="">Seleccione Origen...</option>
                        @foreach($almacenes as $a)
                            <option value="{{ $a->codigo_almacen }}">{{ $a->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Almacén de Destino</label>
                    <select name="codigo_almacen_destino" x-model="destino" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50" required>
                        <option value="">Seleccione Destino...</option>
                        @foreach($almacenes as $a)
                            <option value="{{ $a->codigo_almacen }}" x-show="origen !== '{{ $a->codigo_almacen }}'">{{ $a->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Observaciones</label>
                    <input type="text" name="observaciones" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Motivo de la transferencia...">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Buscador de Lotes -->
            <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-[500px]">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Lotes en Origen</h3>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        <input type="text" x-model="search" @input.debounce.500ms="fetchLotes()" placeholder="Buscar producto o lote..." 
                            class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 outline-none disabled:opacity-50" :disabled="!origen">
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto p-2">
                    <div x-show="!origen" class="text-center py-10 text-gray-400 text-sm italic">
                        Seleccione un almacén de origen para ver el stock disponible.
                    </div>
                    <div x-show="loading" class="text-center py-10 text-blue-500">
                        <i class="fas fa-circle-notch fa-spin text-2xl"></i>
                    </div>
                    <div x-show="origen && !loading && lotesDisponibles.length === 0" class="text-center py-10 text-gray-400 text-sm">
                        No se encontraron lotes con stock.
                    </div>
                    
                    <template x-for="lote in lotesDisponibles" :key="lote.id_inventario">
                        <div class="p-3 mb-2 bg-white border border-gray-200 rounded-xl hover:border-blue-300 transition cursor-pointer flex justify-between items-center"
                             @click="agregarItem(lote)"
                             :class="{'opacity-50 pointer-events-none': isAdded(lote.id_inventario)}">
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-gray-800 truncate" x-text="lote.producto_descripcion"></p>
                                <p class="text-[10px] text-gray-500">Cód: <span x-text="lote.codigo_producto"></span> | Lote: <span class="font-bold text-gray-700" x-text="lote.lote || 'N/A'"></span></p>
                            </div>
                            <div class="text-right ml-2 flex-shrink-0">
                                <span class="block text-xs font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded" x-text="parseFloat(lote.stock_actual).toFixed(2) + ' ' + lote.codigo_unidad_medida"></span>
                                <i class="fas fa-plus-circle text-blue-400 mt-1" x-show="!isAdded(lote.id_inventario)"></i>
                                <i class="fas fa-check-circle text-green-500 mt-1" x-show="isAdded(lote.id_inventario)"></i>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Items a Transferir -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-[500px]">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Productos a Transferir</h3>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-gray-50 sticky top-0 border-b border-gray-200 shadow-sm z-10">
                            <tr>
                                <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider">Producto</th>
                                <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider text-center">Lote</th>
                                <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider text-center">Cant. Disp.</th>
                                <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider text-center w-32">A Transferir</th>
                                <th class="p-3 text-center w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr x-show="items.length === 0">
                                <td colspan="5" class="p-10 text-center text-gray-400 italic">
                                    <i class="fas fa-box-open text-4xl mb-3 block text-gray-300"></i>
                                    Agregue productos desde la lista de lotes.
                                </td>
                            </tr>
                            <template x-for="(item, index) in items" :key="item.id_inventario">
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="p-3">
                                        <p class="font-bold text-gray-800 text-xs" x-text="item.descripcion"></p>
                                        <p class="text-[10px] text-gray-500" x-text="item.codigo_producto"></p>
                                        <!-- Inputs Ocultos -->
                                        <input type="hidden" :name="`items[${index}][id_inventario]`" :value="item.id_inventario">
                                        <input type="hidden" :name="`items[${index}][codigo_producto]`" :value="item.codigo_producto">
                                        <input type="hidden" :name="`items[${index}][lote]`" :value="item.lote">
                                    </td>
                                    <td class="p-3 text-center">
                                        <span class="inline-block bg-gray-100 px-2 py-1 rounded text-xs font-mono font-bold text-gray-600" x-text="item.lote || '—'"></span>
                                    </td>
                                    <td class="p-3 text-center text-xs font-bold text-gray-500" x-text="parseFloat(item.stock_max).toFixed(2)"></td>
                                    <td class="p-3">
                                        <input type="number" step="0.0001" min="0.0001" :max="item.stock_max" :name="`items[${index}][cantidad]`" x-model="item.cantidad"
                                            class="w-full text-center border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500 py-1.5 font-bold"
                                            :class="{'text-red-600 border-red-300 bg-red-50': parseFloat(item.cantidad) > parseFloat(item.stock_max) || parseFloat(item.cantidad) <= 0}">
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" @click="quitarItem(index)" class="text-red-400 hover:text-red-600 transition p-1">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </form>
</div>

<!-- Alpine.js script (se asume que está cargado globalmente, o cargar por CDN si no está) -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function transferenciaApp() {
        return {
            origen: '',
            destino: '',
            search: '',
            loading: false,
            lotesDisponibles: [],
            items: [],
            isSubmitting: false,

            fetchLotes() {
                if (!this.origen) {
                    this.lotesDisponibles = [];
                    this.items = []; // Limpiar items si cambia el origen
                    return;
                }

                this.loading = true;
                fetch(`/admin/inventario/transferencias-api/lotes?codigo_almacen_origen=${this.origen}&search=${encodeURIComponent(this.search)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.lotesDisponibles = data.data;
                    })
                    .catch(err => console.error(err))
                    .finally(() => {
                        this.loading = false;
                    });
            },

            isAdded(id_inventario) {
                return this.items.some(i => i.id_inventario === id_inventario);
            },

            agregarItem(lote) {
                if (this.isAdded(lote.id_inventario)) return;
                
                this.items.push({
                    id_inventario: lote.id_inventario,
                    codigo_producto: lote.codigo_producto,
                    descripcion: lote.producto_descripcion,
                    lote: lote.lote,
                    stock_max: lote.stock_actual,
                    cantidad: lote.stock_actual // Por defecto transferir todo
                });
            },

            quitarItem(index) {
                this.items.splice(index, 1);
            },

            submitForm() {
                if (!this.origen || !this.destino) {
                    alert('Debe seleccionar almacén de origen y destino.');
                    return;
                }
                if (this.origen === this.destino) {
                    alert('El almacén de destino no puede ser igual al origen.');
                    return;
                }
                
                // Validar cantidades
                let hasErrors = false;
                this.items.forEach(item => {
                    let cant = parseFloat(item.cantidad);
                    let max = parseFloat(item.stock_max);
                    if (isNaN(cant) || cant <= 0) {
                        hasErrors = true;
                    }
                    if (cant > max) {
                        hasErrors = true;
                    }
                });

                if (hasErrors) {
                    alert('Hay cantidades inválidas. Asegúrese de que sean mayores a cero y no superen el stock disponible.');
                    return;
                }

                this.isSubmitting = true;
                document.getElementById('formTransferencia').submit();
            }
        }
    }
</script>
@endsection
