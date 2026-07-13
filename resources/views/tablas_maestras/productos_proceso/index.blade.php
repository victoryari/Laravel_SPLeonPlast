@extends('layouts.app')

@section('title', 'Productos de Proceso')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Productos de Proceso" subtitle="Gestión de la tabla maestra de productos en proceso (PEP)">
        <x-slot:actions>
            <a href="{{ route('productos_proceso.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-table :headers="['Código', 'Descripción', ['label' => 'Acciones', 'class' => 'text-center']]">
        @forelse ($productos_proceso as $producto)
            <tr class="hover:bg-slate-50/50 transition duration-150">
                <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-slate-900">{{ $producto->codigo }}</td>
                <td class="px-4 md:px-6 py-3 md:py-4 text-slate-700">{{ $producto->descripcion }}</td>
                <td class="px-4 md:px-6 py-3 md:py-4 text-center space-x-2">
                    <a href="{{ route('productos_proceso.edit', $producto->codigo) }}" class="btn-icon btn-icon-edit" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('productos_proceso.destroy', $producto->codigo) }}" method="POST" class="inline-block form-delete">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon btn-icon-delete" title="Anular">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="px-6 py-8 text-center text-slate-500 bg-slate-50">
                    <i class="fas fa-inbox text-3xl mb-3 text-slate-400"></i>
                    <p>No se encontraron productos de proceso activos.</p>
                </td>
            </tr>
        @endforelse
    </x-table>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.form-delete');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Anular Registro?',
                    text: "Este registro dejará de estar disponible en el sistema.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sí, anular',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection
