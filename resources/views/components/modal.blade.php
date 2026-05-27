@props(['id' => 'modal', 'title' => '', 'size' => 'md', 'submit' => null])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
    ];
    $maxW = $sizes[$size] ?? $sizes['md'];
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm overflow-y-auto flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full {{ $maxW }} mx-4 overflow-hidden">
        @if($title)
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-bold text-slate-800">{{ $title }}</h3>
            <button type="button" onclick="cerrarModal('{{ $id }}')" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        @endif

        @if($submit)
        <form method="POST" action="{{ $submit }}" id="form-{{ $id }}">
            @csrf
            <div class="px-6 py-4">
                {{ $slot }}
            </div>
        </form>
        @else
        <div class="px-6 py-4">
            {{ $slot }}
        </div>
        @endif

        @if(isset($footer))
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    window.cerrarModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            const form = document.getElementById('form-' + id);
            if (form) form.reset();
        }
    };
    window.abrirModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    };
</script>
@endpush