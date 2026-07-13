@props(['action' => '', 'method' => 'GET'])

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
    <form action="{{ $action }}" method="{{ $method }}" class="flex flex-col sm:flex-row flex-wrap gap-4 items-end" id="filter-bar-form">
        {{ $slot }}
        
        <div class="flex gap-2 ml-auto">
            <button type="submit" class="btn-primary flex items-center justify-center">
                <i class="fas fa-search mr-2"></i> Filtrar
            </button>
            <a href="{{ $action }}" class="btn-secondary flex items-center justify-center" title="Limpiar Filtros">
                <i class="fas fa-eraser"></i>
            </a>
        </div>
    </form>
</div>
