@php 
    $pendientesCount = \App\Models\Compra::where('estado', 'PENDIENTE')->count(); 
@endphp

<div class="bg-white border-b border-slate-200 shadow-sm mb-6 rounded-2xl overflow-hidden">
    <nav class="flex overflow-x-auto">
        <a href="{{ route('inventario.index') }}" class="whitespace-nowrap py-4 px-6 font-bold text-sm border-b-2 {{ request()->routeIs('inventario.index') ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors' }}">
            <i class="fas fa-boxes mr-2"></i> Existencias
        </a>
        
        <a href="{{ route('inventario.recepciones') }}" class="whitespace-nowrap py-4 px-6 font-bold text-sm border-b-2 {{ request()->routeIs('inventario.recepciones') ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors' }}">
            <i class="fas fa-truck-loading mr-2"></i> Recepciones
            @if($pendientesCount > 0)
                <span class="ml-2 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full shadow-sm">{{ $pendientesCount }}</span>
            @endif
        </a>
        
        <a href="{{ route('inventario.kardex') }}" class="whitespace-nowrap py-4 px-6 font-bold text-sm border-b-2 {{ request()->routeIs('inventario.kardex') ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors' }}">
            <i class="fas fa-history mr-2"></i> Kardex
        </a>
        
        <a href="{{ route('inventario.ajuste') }}" class="whitespace-nowrap py-4 px-6 font-bold text-sm border-b-2 {{ request()->routeIs('inventario.ajuste') ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors' }}">
            <i class="fas fa-sliders-h mr-2"></i> Ajuste Manual
        </a>
    </nav>
</div>