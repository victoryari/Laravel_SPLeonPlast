@extends('layouts.app')

@section('title', 'Dashboard de Producción')

@section('content')
<div class="container mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard de Producción</h1>
            <p class="text-slate-600">Indicadores Clave de Rendimiento (KPIs) y control de planta.</p>
        </div>
        <div>
            <form id="filtroRangoForm" method="GET" action="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <label for="rango" class="text-sm font-semibold text-slate-700">Rango Temporal:</label>
                <select name="rango" id="rango" onchange="document.getElementById('filtroRangoForm').submit()" class="border-slate-300 rounded-md shadow-sm focus:ring-primary focus:border-primary py-2 text-sm font-medium">
                    <option value="hoy" {{ $rango == 'hoy' ? 'selected' : '' }}>Hoy</option>
                    <option value="semana" {{ $rango == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                    <option value="mes" {{ $rango == 'mes' ? 'selected' : '' }}>Este Mes</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Widgets (Tarjetas de Resumen) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase">Órdenes Activas</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $stats['ordenes_activas'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-tasks text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase">Nivel de Merma</p>
                    <p class="text-2xl font-bold {{ $stats['porcentaje_merma'] > 5 ? 'text-red-600' : 'text-slate-800' }}">
                        {{ $stats['porcentaje_merma'] }}%
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Total: {{ number_format($stats['total_merma'], 2) }} kg</p>
                </div>
                <div class="bg-red-100 p-3 rounded-lg">
                    <i class="fas fa-trash text-red-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase">Horas Hombre</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $stats['horas_hombre'] }} h</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-users text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase">Horas Máquina</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $stats['horas_maquina'] }} h</p>
                    <p class="text-xs text-slate-500 mt-1">Costo: S/ {{ number_format($stats['costo_maquina'], 2) }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-cogs text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Gráfico Dona: Estados OP -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="font-bold text-slate-700 uppercase text-sm tracking-wide mb-4 border-b pb-2">Estado de Producción</h3>
            <div class="relative h-64">
                <canvas id="chartEstados"></canvas>
            </div>
            @if(empty($chartEstados['data']))
                <p class="text-center text-slate-400 text-sm mt-4">No hay datos en este rango.</p>
            @endif
        </div>

        <!-- Gráfico Barras Horizontal: Centros de Trabajo -->
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
            <h3 class="font-bold text-slate-700 uppercase text-sm tracking-wide mb-4 border-b pb-2">Volumen Producido por Centro de Trabajo</h3>
            <div class="relative h-64">
                <canvas id="chartCentros"></canvas>
            </div>
            @if(empty($chartCentros['data']))
                <p class="text-center text-slate-400 text-sm mt-4">No hay datos en este rango.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Gráfico Líneas: Tendencia Mermas -->
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
            <h3 class="font-bold text-slate-700 uppercase text-sm tracking-wide mb-4 border-b pb-2">Tendencia de Merma (Últimos 30 días)</h3>
            <div class="relative h-64">
                <canvas id="chartMermas"></canvas>
            </div>
            @if(empty($chartMermas['data']))
                <p class="text-center text-slate-400 text-sm mt-4">No hay reportes de merma recientes.</p>
            @endif
        </div>

        <!-- Tabla: OPs Demoradas -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-red-50 flex justify-between items-center">
                <h3 class="font-bold text-red-700 uppercase text-sm tracking-wide"><i class="fas fa-exclamation-circle mr-2"></i>Alertas de Retraso</h3>
            </div>
            <div class="p-0 flex-1 overflow-y-auto max-h-72">
                @if($ordenesDemoradas->count() > 0)
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @foreach($ordenesDemoradas as $od)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-bold text-red-600">{{ $od->codigo_op }}</div>
                                <div class="text-xs text-slate-500">{{ $od->descripcion_producto_proceso }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-xs font-semibold text-slate-700">Prog: {{ \Carbon\Carbon::parse($od->fecha)->format('d/m/Y') }}</div>
                                <div class="mt-1"><span class="px-2 py-1 text-[10px] font-bold rounded-full bg-red-100 text-red-700">{{ $od->estado }}</span></div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="flex flex-col items-center justify-center h-full p-6 text-slate-400">
                    <i class="fas fa-check-circle text-4xl text-green-300 mb-3"></i>
                    <p class="text-sm font-medium">No hay órdenes demoradas</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6b7280';
    
    // 1. Gráfico Dona: Estados
    const ctxEstados = document.getElementById('chartEstados');
    if (ctxEstados && @json(!empty($chartEstados['data']))) {
        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: @json($chartEstados['labels']),
                datasets: [{
                    data: @json($chartEstados['data']),
                    backgroundColor: [
                        '#3b82f6', // blue
                        '#10b981', // green
                        '#f59e0b', // amber
                        '#ef4444', // red
                        '#8b5cf6'  // violet
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                },
                cutout: '70%'
            }
        });
    }

    // 2. Gráfico Barras: Centros de Trabajo
    const ctxCentros = document.getElementById('chartCentros');
    if (ctxCentros && @json(!empty($chartCentros['data']))) {
        new Chart(ctxCentros, {
            type: 'bar',
            data: {
                labels: @json($chartCentros['labels']),
                datasets: [{
                    label: 'Volumen (Unidades/Kg)',
                    data: @json($chartCentros['data']),
                    backgroundColor: [
                        '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
                        '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#6366f1', 
                        '#84cc16', '#d946ef'
                    ],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // Barra horizontal
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { borderDash: [2, 4], color: '#f3f4f6' } }
                }
            }
        });
    }

    // 3. Gráfico Líneas: Tendencia Mermas
    const ctxMermas = document.getElementById('chartMermas');
    if (ctxMermas && @json(!empty($chartMermas['data']))) {
        new Chart(ctxMermas, {
            type: 'line',
            data: {
                labels: @json($chartMermas['labels']),
                datasets: [{
                    label: 'Merma Pura (Kg)',
                    data: @json($chartMermas['data']),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#ef4444'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { borderDash: [2, 4], color: '#f3f4f6' }, beginAtZero: true }
                }
            }
        });
    }
});
</script>
@endsection