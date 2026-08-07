@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-emerald-900/20 uppercase ring-4 ring-emerald-500/20">
                {{ substr($user->nombre_usuario, 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $user->nombre_usuario }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-sm text-slate-500 uppercase tracking-wider font-semibold">{{ $user->rol }}</span>
                </div>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 transition-colors flex items-center gap-2 self-start">
            <i class="fas fa-arrow-left"></i>
            <span>Volver al Dashboard</span>
        </a>
    </div>

    <!-- Pestañas de navegación -->
    <div x-data="{ tab: '{{ request()->get('tab', 'info') }}' }" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="flex border-b border-slate-200 bg-slate-50/50 px-6 pt-4 gap-2 overflow-x-auto">
                <button @click="tab = 'info'" :class="tab === 'info' ? 'border-emerald-600 text-emerald-700 bg-white shadow-sm' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/50'"
                    class="px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors flex items-center gap-2 border-b-2 whitespace-nowrap">
                    <i class="fas fa-user"></i>
                    <span>Información Personal</span>
                </button>
                <button @click="tab = 'password'" :class="tab === 'password' ? 'border-emerald-600 text-emerald-700 bg-white shadow-sm' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/50'"
                    class="px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors flex items-center gap-2 border-b-2 whitespace-nowrap">
                    <i class="fas fa-lock"></i>
                    <span>Cambiar Contraseña</span>
                </button>
                <button @click="tab = 'reporte'" :class="tab === 'reporte' ? 'border-emerald-600 text-emerald-700 bg-white shadow-sm' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/50'"
                    class="px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors flex items-center gap-2 border-b-2 whitespace-nowrap">
                    <i class="fas fa-bug"></i>
                    <span>Reportar Problema</span>
                </button>
            </div>

            <!-- ======================== TAB: Información Personal ======================== -->
            <div x-show="tab === 'info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Datos de la Cuenta -->
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 space-y-4">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-id-card text-emerald-600"></i>
                            Datos de la Cuenta
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-slate-200">
                                <span class="text-sm text-slate-500 font-medium">Usuario</span>
                                <span class="text-sm font-bold text-slate-800">{{ $user->nombre_usuario }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200">
                                <span class="text-sm text-slate-500 font-medium">Email</span>
                                <span class="text-sm font-semibold text-slate-700">{{ $user->email ?? 'No registrado' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200">
                                <span class="text-sm text-slate-500 font-medium">Rol</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                    {{ $user->rol }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200">
                                <span class="text-sm text-slate-500 font-medium">Estado</span>
                                @if($user->activo)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200">
                                <span class="text-sm text-slate-500 font-medium">Fecha de Creación</span>
                                <span class="text-sm text-slate-700">{{ $user->fecha_creacion ? \Carbon\Carbon::parse($user->fecha_creacion)->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-slate-500 font-medium">Último Login</span>
                                <span class="text-sm text-slate-700">{{ $user->ultimo_login ? \Carbon\Carbon::parse($user->ultimo_login)->format('d/m/Y H:i') : 'Nunca' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Trabajador Asociado -->
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 space-y-4">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-hard-hat text-emerald-600"></i>
                            Trabajador Asociado
                        </h3>
                        @if($trabajador)
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-slate-200">
                                    <span class="text-sm text-slate-500 font-medium">Código</span>
                                    <span class="text-sm font-bold text-slate-800 font-mono">{{ $trabajador->codigo }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-slate-200">
                                    <span class="text-sm text-slate-500 font-medium">Nombre Completo</span>
                                    <span class="text-sm font-semibold text-slate-700">{{ $trabajador->nombre_completo }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-slate-200">
                                    <span class="text-sm text-slate-500 font-medium">DNI</span>
                                    <span class="text-sm text-slate-700">{{ $trabajador->dni ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-slate-500 font-medium">Área</span>
                                    <span class="text-sm text-slate-700">{{ $trabajador->area ?? 'N/A' }}</span>
                                </div>
                            </div>
                        @else
                            <div class="p-6 text-center space-y-2">
                                <div class="h-12 w-12 bg-slate-200 text-slate-400 rounded-full flex items-center justify-center mx-auto text-xl">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <p class="text-sm text-slate-500">No hay trabajador asociado a esta cuenta.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ======================== TAB: Cambiar Contraseña ======================== -->
            <div x-show="tab === 'password'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="p-6">
                <div class="max-w-lg mx-auto">
                    <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 space-y-5">
                        <div class="text-center space-y-2">
                            <div class="h-14 w-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">Cambiar Contraseña</h3>
                            <p class="text-sm text-slate-500">Ingresa tu contraseña actual y define una nueva contraseña segura.</p>
                        </div>

                        <form action="{{ route('perfil.update_password') }}" method="POST" class="space-y-4">
                            @csrf

                            <!-- Contraseña Actual -->
                            <div>
                                <label for="password_actual" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Contraseña Actual <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password_actual" id="password_actual" required
                                        class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('password_actual') border-red-400 ring-1 ring-red-400 @enderror"
                                        placeholder="Ingresa tu contraseña actual">
                                    <button type="button" onclick="togglePassword('password_actual', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password_actual')
                                    <p class="text-red-500 text-xs mt-1 font-medium flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Nueva Contraseña -->
                            <div>
                                <label for="password_nuevo" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Nueva Contraseña <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password_nuevo" id="password_nuevo" required minlength="6"
                                        class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('password_nuevo') border-red-400 ring-1 ring-red-400 @enderror"
                                        placeholder="Mínimo 6 caracteres">
                                    <button type="button" onclick="togglePassword('password_nuevo', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password_nuevo')
                                    <p class="text-red-500 text-xs mt-1 font-medium flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Confirmar Nueva Contraseña -->
                            <div>
                                <label for="password_nuevo_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Confirmar Nueva Contraseña <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="password_nuevo_confirmation" id="password_nuevo_confirmation" required minlength="6"
                                        class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                                        placeholder="Repite la nueva contraseña">
                                    <button type="button" onclick="togglePassword('password_nuevo_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Indicador de fortaleza -->
                            <div id="password-strength" class="hidden">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                        <div id="strength-bar" class="h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                    <span id="strength-text" class="text-xs font-semibold text-slate-500"></span>
                                </div>
                            </div>

                            <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-900/20 transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                                <i class="fas fa-key"></i>
                                Actualizar Contraseña
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ======================== TAB: Reportar Problema ======================== -->
            <div x-show="tab === 'reporte'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 space-y-6">

                <!-- Formulario de Reporte -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-lg">
                            <i class="fas fa-flag"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Reportar un Problema</h3>
                            <p class="text-sm text-slate-500">Describe el problema que experimentas. Los administradores serán notificados automáticamente.</p>
                        </div>
                    </div>

                    <form action="{{ route('perfil.store_reporte') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Título del Reporte -->
                        <div>
                            <label for="titulo" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Título del Reporte <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="titulo" id="titulo" required maxlength="255"
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('titulo') border-red-400 ring-1 ring-red-400 @enderror"
                                placeholder="Ej: Error al guardar la orden de producción"
                                value="{{ old('titulo') }}">
                            @error('titulo')
                                <p class="text-red-500 text-xs mt-1 font-medium flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Tipo de Problema -->
                            <div>
                                <label for="tipo_problema" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Tipo de Problema <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo_problema" id="tipo_problema" required
                                    class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('tipo_problema') border-red-400 ring-1 ring-red-400 @enderror">
                                    <option value="">Seleccionar...</option>
                                    <option value="ERROR_SISTEMA" {{ old('tipo_problema') == 'ERROR_SISTEMA' ? 'selected' : '' }}>🔴 Error del Sistema</option>
                                    <option value="CONSULTA_DUDA" {{ old('tipo_problema') == 'CONSULTA_DUDA' ? 'selected' : '' }}>🔵 Consulta / Duda</option>
                                    <option value="MEJORA_SOLICITUD" {{ old('tipo_problema') == 'MEJORA_SOLICITUD' ? 'selected' : '' }}>🟢 Solicitud de Mejora</option>
                                    <option value="FALLO_DATOS" {{ old('tipo_problema') == 'FALLO_DATOS' ? 'selected' : '' }}>🟠 Fallo en Datos</option>
                                    <option value="OTRO" {{ old('tipo_problema') == 'OTRO' ? 'selected' : '' }}>⚪ Otro</option>
                                </select>
                                @error('tipo_problema')
                                    <p class="text-red-500 text-xs mt-1 font-medium flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Prioridad -->
                            <div>
                                <label for="prioridad" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Prioridad <span class="text-red-500">*</span>
                                </label>
                                <select name="prioridad" id="prioridad" required
                                    class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('prioridad') border-red-400 ring-1 ring-red-400 @enderror">
                                    <option value="">Seleccionar...</option>
                                    <option value="BAJA" {{ old('prioridad') == 'BAJA' ? 'selected' : '' }}>Baja</option>
                                    <option value="MEDIA" {{ old('prioridad', 'MEDIA') == 'MEDIA' ? 'selected' : '' }}>Media</option>
                                    <option value="ALTA" {{ old('prioridad') == 'ALTA' ? 'selected' : '' }}>Alta</option>
                                    <option value="CRITICA" {{ old('prioridad') == 'CRITICA' ? 'selected' : '' }}>Crítica</option>
                                </select>
                                @error('prioridad')
                                    <p class="text-red-500 text-xs mt-1 font-medium flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Descripción Detallada <span class="text-red-500">*</span>
                            </label>
                            <textarea name="descripcion" id="descripcion" rows="5" required maxlength="5000"
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-none @error('descripcion') border-red-400 ring-1 ring-red-400 @enderror"
                                placeholder="Describe con detalle el problema, qué estabas haciendo cuando ocurrió, y qué resultado esperabas...">{{ old('descripcion') }}</textarea>
                            <div class="flex justify-between items-center mt-1">
                                @error('descripcion')
                                    <p class="text-red-500 text-xs font-medium flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @else
                                    <span></span>
                                @enderror
                                <span class="text-xs text-slate-400" id="char-count">0 / 5000</span>
                            </div>
                        </div>

                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold rounded-xl shadow-lg shadow-amber-900/20 transition-all duration-200 flex items-center gap-2 text-sm">
                            <i class="fas fa-paper-plane"></i>
                            Enviar Reporte
                        </button>
                    </form>
                </div>

                <!-- Historial de Reportes -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="p-5 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                        <i class="fas fa-history text-slate-600"></i>
                        <h3 class="text-base font-bold text-slate-800">Historial de Reportes Enviados</h3>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-slate-200 text-slate-700 font-bold">{{ $reportes->total() }}</span>
                    </div>

                    @if($reportes->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-900 text-slate-200 text-xs font-bold uppercase tracking-wider">
                                        <th class="px-4 py-3 text-left border-r border-slate-800">#</th>
                                        <th class="px-4 py-3 text-left border-r border-slate-800">Título</th>
                                        <th class="px-4 py-3 text-center border-r border-slate-800">Tipo</th>
                                        <th class="px-4 py-3 text-center border-r border-slate-800">Prioridad</th>
                                        <th class="px-4 py-3 text-center border-r border-slate-800">Estado</th>
                                        <th class="px-4 py-3 text-center">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($reportes as $reporte)
                                        @php
                                            $tipoBadge = match($reporte->tipo_problema) {
                                                'ERROR_SISTEMA'    => 'bg-red-100 text-red-800',
                                                'CONSULTA_DUDA'    => 'bg-blue-100 text-blue-800',
                                                'MEJORA_SOLICITUD' => 'bg-emerald-100 text-emerald-800',
                                                'FALLO_DATOS'      => 'bg-orange-100 text-orange-800',
                                                default            => 'bg-slate-100 text-slate-800',
                                            };
                                            $prioridadBadge = match($reporte->prioridad) {
                                                'BAJA'    => 'bg-slate-100 text-slate-700',
                                                'MEDIA'   => 'bg-blue-100 text-blue-700',
                                                'ALTA'    => 'bg-amber-100 text-amber-800',
                                                'CRITICA' => 'bg-red-100 text-red-800',
                                                default   => 'bg-slate-100 text-slate-700',
                                            };
                                            $estadoBadge = match($reporte->estado) {
                                                'PENDIENTE'   => 'bg-yellow-100 text-yellow-800',
                                                'EN_REVISION' => 'bg-blue-100 text-blue-800',
                                                'RESUELTO'    => 'bg-emerald-100 text-emerald-800',
                                                'RECHAZADO'   => 'bg-red-100 text-red-800',
                                                default       => 'bg-slate-100 text-slate-800',
                                            };
                                            $tiposLabel = \App\Models\ReporteProblema::tiposProblema();
                                            $prioridadesLabel = \App\Models\ReporteProblema::prioridades();
                                            $estadosLabel = \App\Models\ReporteProblema::estados();
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $reporte->id_reporte }}</td>
                                            <td class="px-4 py-3">
                                                <p class="font-semibold text-slate-800 text-sm">{{ $reporte->titulo }}</p>
                                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ Str::limit($reporte->descripcion, 80) }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold {{ $tipoBadge }}">
                                                    {{ $tiposLabel[$reporte->tipo_problema] ?? $reporte->tipo_problema }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold {{ $prioridadBadge }}">
                                                    {{ $prioridadesLabel[$reporte->prioridad] ?? $reporte->prioridad }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold {{ $estadoBadge }}">
                                                    {{ $estadosLabel[$reporte->estado] ?? $reporte->estado }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center text-xs text-slate-500">
                                                {{ $reporte->created_at ? $reporte->created_at->format('d/m/Y H:i') : 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($reportes->hasPages())
                            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                                {{ $reportes->appends(['tab' => 'reporte'])->links() }}
                            </div>
                        @endif
                    @else
                        <div class="p-12 text-center space-y-3">
                            <div class="h-16 w-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto text-2xl">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <h3 class="text-base font-semibold text-slate-700">Sin reportes enviados</h3>
                            <p class="text-slate-400 text-sm max-w-sm mx-auto">
                                Aún no has enviado ningún reporte de problema. Utiliza el formulario superior para describir cualquier incidencia.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    // Toggle password visibility
    function togglePassword(inputId, btn) {
        var input = document.getElementById(inputId);
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Character counter for description
    document.addEventListener('DOMContentLoaded', function() {
        var desc = document.getElementById('descripcion');
        var counter = document.getElementById('char-count');
        if (desc && counter) {
            function updateCount() {
                counter.textContent = desc.value.length + ' / 5000';
            }
            desc.addEventListener('input', updateCount);
            updateCount();
        }

        // Password strength indicator
        var passwordInput = document.getElementById('password_nuevo');
        var strengthContainer = document.getElementById('password-strength');
        var strengthBar = document.getElementById('strength-bar');
        var strengthText = document.getElementById('strength-text');

        if (passwordInput && strengthContainer) {
            passwordInput.addEventListener('input', function() {
                var val = this.value;
                if (val.length === 0) {
                    strengthContainer.classList.add('hidden');
                    return;
                }
                strengthContainer.classList.remove('hidden');

                var score = 0;
                if (val.length >= 6) score++;
                if (val.length >= 10) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;

                var labels = ['Muy débil', 'Débil', 'Regular', 'Fuerte', 'Muy fuerte'];
                var colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-emerald-500', 'bg-emerald-600'];
                var widths = ['20%', '40%', '60%', '80%', '100%'];

                var idx = Math.min(score, 4);
                strengthBar.className = 'h-full rounded-full transition-all duration-300 ' + colors[idx];
                strengthBar.style.width = widths[idx];
                strengthText.textContent = labels[idx];
            });
        }
    });
</script>
@endpush
