@extends('layouts.app')

@section('title', 'Rutas de Producción')

@section('content')
<div class="container mx-auto max-w-5xl pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Rutas de Producción</h1>
            <p class="text-xs sm:text-sm text-slate-600">Asigne los procesos correspondientes a cada producto en proceso (PEP).</p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="col-span-1 md:col-span-1 bg-white rounded-xl shadow-md p-6 border-t-4 border-primary">
            <h3 class="text-lg font-bold text-slate-700 mb-4">Seleccionar Producto</h3>
            <ul class="space-y-2 h-[500px] overflow-y-auto pr-2">
                @foreach($productos as $producto)
                    <li>
                        <button type="button" class="w-full text-left px-4 py-3 rounded-lg border flex flex-col transition-colors
                            focus:outline-none focus:ring-2 focus:ring-primary producto-btn
                            hover:bg-slate-50 bg-white border-slate-200" 
                            data-codigo="{{ $producto->codigo }}"
                            data-procesos="{{ $producto->rutas->pluck('codigo')->toJson() }}">
                            <span class="font-bold text-sm text-slate-800">{{ $producto->descripcion }}</span>
                            <span class="text-xs text-slate-500 mt-1">
                                {{ $producto->rutas->count() }} proceso(s) asignados
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="col-span-1 md:col-span-2 bg-white rounded-xl shadow-md p-6 border-t-4 border-primary hidden" id="procesos-panel">
            <h3 class="text-lg font-bold text-slate-700 mb-2">Procesos Asignados</h3>
            <p class="text-sm text-slate-600 mb-6" id="producto-selected-name"></p>

            <form action="{{ route('admin.rutas_produccion.store') }}" method="POST">
                @csrf
                <input type="hidden" name="codigo_producto_proceso" id="codigo_producto_proceso">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    @foreach($procesos as $proceso)
                        <label class="flex items-start p-3 border rounded-lg hover:bg-slate-50 cursor-pointer transition-colors">
                            <input type="checkbox" name="procesos[]" value="{{ $proceso->codigo }}" class="proceso-checkbox mt-1 h-4 w-4 text-primary focus:ring-primary border-slate-300 rounded">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-slate-800">{{ $proceso->descripcion }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="border-t border-slate-200 pt-5 flex justify-end">
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-6 rounded-lg shadow transition flex items-center">
                        <i class="fas fa-save mr-2"></i> Guardar Ruta
                    </button>
                </div>
            </form>
        </div>
        
        <div class="col-span-1 md:col-span-2 bg-white rounded-xl shadow-md p-6 flex flex-col items-center justify-center text-center text-slate-500" id="empty-panel">
            <i class="fas fa-route text-5xl mb-4 text-slate-300"></i>
            <h3 class="text-lg font-bold text-slate-600">Seleccione un producto</h3>
            <p class="text-sm mt-2">Haga clic en un producto de la lista a la izquierda para configurar su ruta de producción.</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btns = document.querySelectorAll('.producto-btn');
        const procesosPanel = document.getElementById('procesos-panel');
        const emptyPanel = document.getElementById('empty-panel');
        const inputCodigo = document.getElementById('codigo_producto_proceso');
        const titleName = document.getElementById('producto-selected-name');
        const checkboxes = document.querySelectorAll('.proceso-checkbox');

        btns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Reset visual state
                btns.forEach(b => {
                    b.classList.remove('bg-blue-50', 'border-primary', 'ring-1', 'ring-primary');
                    b.classList.add('bg-white', 'border-slate-200');
                });
                
                // Set active visual state
                this.classList.remove('bg-white', 'border-slate-200');
                this.classList.add('bg-blue-50', 'border-primary', 'ring-1', 'ring-primary');

                // Load data
                const codigo = this.getAttribute('data-codigo');
                const nombre = this.querySelector('.font-bold').textContent;
                const asignados = JSON.parse(this.getAttribute('data-procesos') || '[]');

                inputCodigo.value = codigo;
                titleName.textContent = 'Configurando ruta para: ' + nombre;

                checkboxes.forEach(cb => {
                    cb.checked = asignados.includes(parseInt(cb.value)) || asignados.includes(cb.value);
                });

                // Show panel
                emptyPanel.classList.add('hidden');
                procesosPanel.classList.remove('hidden');
            });
        });
    });
</script>
@endsection
