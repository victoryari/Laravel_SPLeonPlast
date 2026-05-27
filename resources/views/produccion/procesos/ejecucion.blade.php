@extends('layouts.app')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center">
                Ejecución: {{ strtoupper($proceso->proceso_desc) }}
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                Orden #{{ $orden->idop }} - <span class="font-semibold">{{ $orden->descripcion_producto_proceso }}</span>
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex flex-wrap items-center gap-3">
            <span class="px-3 py-1 text-xs font-semibold uppercase rounded-full text-white {{ $estado_proceso_actual == 'COMPLETADO' ? 'bg-green-600' : ($estado_proceso_actual == 'EN_PROCESO' ? 'bg-primary' : 'bg-slate-500') }}">
                ESTADO: {{ $estado_proceso_actual }}
            </span>
            <a href="{{ route('ordenes.procesos.index', $orden->idop) }}" class="shrink-0 flex items-center justify-center bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
                <i class="fas fa-arrow-left"></i>
                <span class="hidden sm:inline ml-2">Volver</span>
            </a>
        </div>
    </div>

    <!-- Cargador de Fórmulas -->
    @if(($es_mezclado || $es_inyectado) && $estado_proceso_actual !== 'COMPLETADO')
    <div class="bg-white rounded-xl shadow-md border-t-4 border-orange-500 mb-6 overflow-hidden">
        <div class="bg-slate-50 border-b border-gray-200 px-6 py-4 flex items-center">
            <h2 class="text-lg font-bold text-slate-800">
                <i class="fas fa-flask mr-2 text-orange-500"></i>{{ $es_inyectado ? 'Cargar Fórmula (Mezclado Directo)' : 'Cargar Fórmula de Mezclado' }}
            </h2>
        </div>
        <div class="p-6 bg-white">
            <div class="flex flex-wrap items-end gap-4">
                
                @if($es_inyectado)
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Inyectora (Centro)</label>
                    <select id="centro_global" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm py-2 px-3">
                        <option value="">-- Seleccione --</option>
                        @foreach($centros_trabajo as $ct)
                            <option value="{{ $ct->codigo }}">{{ $ct->codigo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Molde a usar</label>
                    <select id="molde_global" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm py-2 px-3" onchange="vincularFormula()">
                        <option value="">-- Seleccione --</option>
                        @foreach($moldes as $m)
                            <option value="{{ $m->codigo }}" data-formula="{{ $m->codigo_formula ?? '' }}">
                                {{ $m->codigo }} - {{ $m->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="flex-1 min-w-50">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Seleccione Fórmula</label>
                    <select id="formula_selector" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm py-2 px-3">
                        <option value="">-- Seleccione --</option>
                        @foreach($formulas_disponibles as $fm)
                            <option value="{{ $fm->codigo }}">{{ $fm->codigo }} - {{ $fm->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Cant. (KG)</label>
                    <input type="number" id="cantidad_global" class="w-24 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm py-2 px-3" step="0.01">
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-4 mt-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Trabajador</label>
                    <select id="trabajador_global" class="border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm py-2 px-3">
                        <option value="">-- Seleccione --</option>
                        @foreach($trabajadores as $t)
                            <option value="{{ $t->codigo }}">{{ $t->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Hora Inicio</label>
                    <input type="time" id="hora_ini_global" value="08:00"
                        class="w-28 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm py-2 px-3">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Hora Fin</label>
                    <input type="time" id="hora_fin_global" value="17:00"
                        class="w-28 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm py-2 px-3">
                </div>

                <div class="flex-1"></div>

                <button type="button" onclick="cargarComponentes()" class="px-5 py-2 {{ $es_inyectado ? 'bg-orange-600 hover:bg-orange-700' : 'bg-primary hover:bg-primary-dark' }} text-white font-medium rounded-md shadow-sm transition">
                    <i class="fas fa-box-open mr-2"></i>Cargar
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Detalle de Componentes -->
    <div class="bg-white rounded-xl shadow-md border-t-4 border-primary overflow-hidden mb-6">
        <div class="bg-slate-50 border-b border-gray-200 px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-lg font-bold text-slate-800">
                <i class="fas fa-list-alt mr-2 text-primary"></i>Detalle de Materiales / Actividades
            </h2>
            <button type="button" onclick="agregarFilaManual()" class="shrink-0 flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition {{ $estado_proceso_actual === 'COMPLETADO' ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $estado_proceso_actual === 'COMPLETADO' ? 'disabled' : '' }}>
                <i class="fas fa-plus"></i><span class="ml-2">Fila Manual</span>
            </button>
        </div>
        
        <div class="p-6">
            <form id="form_masivo" action="{{ route('ordenes.procesos.componentes.store', [$orden->idop, $proceso->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="componentes_json" id="componentes_json">
                
                <div class="overflow-x-auto min-h-87.5 pb-10">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tipo</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Material / Actividad</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Centro</th>
                                @if($es_inyectado) <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Molde</th> @endif
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Cant.</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">U.M.</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Trabajador</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Hora In/Fin</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_items" class="bg-white divide-y divide-gray-200">
                            @foreach($registrados as $r)
                            <tr id="row_display_{{ $r->id_op_componentes }}" class="bg-slate-50">
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-200 text-slate-800">
                                        {{ $r->codigo_tipo_producto }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-sm text-gray-900 font-bold">{{ $r->descripcion_producto }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $r->codigo_producto }}</div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ $r->codigo_centro_trabajo }}</td>
                                @if($es_inyectado) <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ $r->codigo_molde }}</td> @endif
                                <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-gray-900">{{ number_format($r->cantidad, 2) }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ $r->codigo_unidad_medida }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ $r->codigo_trabajador }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">{{ $r->hora_inicio }} - {{ $r->hora_fin }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-center text-sm font-medium">
                                    @if($estado_proceso_actual !== 'COMPLETADO')
                                    <button type="button" onclick="editarRegistrado({{ $r->id_op_componentes }})" class="text-primary hover:text-primary mr-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" onclick="eliminarRegistrado({{ $r->id_op_componentes }})" class="text-red-500 hover:text-red-700" title="Desactivar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @endif
                                    <x-badge color="emerald">OK</x-badge>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer del Formulario -->
                <div class="mt-6 pt-6 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div>
                        @if($estado_proceso_actual !== 'COMPLETADO')
                            <button type="button" onclick="confirmarCierre()" class="px-6 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition shadow-sm {{ !$tiene_componentes ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$tiene_componentes ? 'disabled' : '' }}>
                                🏁 Finalizar y Cerrar Proceso
                            </button>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        @if($estado_proceso_actual !== 'COMPLETADO')
                        <div class="bg-orange-50 px-4 py-2 rounded-lg border border-orange-200 flex items-center shadow-sm">
                            <label class="text-orange-700 font-bold text-sm mr-3">
                                Merma (KG):
                            </label>
                            <input type="number" name="merma_kg" id="merma_kg" class="w-24 text-center font-bold text-gray-900 border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500" value="0.00" step="0.01">
                        </div>
                        <button type="button" onclick="enviarGuardado()" class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark transition shadow-sm transform hover:-translate-y-0.5">
                            💾 Guardar Componentes
                        </button>
                        @endif
                    </div>
                </div>
            </form>

            @foreach($registrados as $r)
            <div id="row_edit_{{ $r->id_op_componentes }}" class="hidden mb-2">
                <form action="{{ route('ordenes.procesos.componentes.update', [$orden->idop, $proceso->id, $r->id_op_componentes]) }}" method="POST" class="flex flex-wrap items-end gap-3 p-4 bg-primary-50 border border-primary-50 rounded-lg">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Cantidad</label>
                        <input type="number" name="cantidad" step="0.01" min="0.01"
                            value="{{ $r->cantidad }}"
                            class="w-24 border-gray-300 rounded-md text-sm py-1.5 px-2 focus:ring-primary focus:border-primary"
                            {{ $r->codigo_tipo_producto === 'ACT' ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Trabajador</label>
                        <select name="codigo_trabajador"
                            class="border-gray-300 rounded-md text-sm py-1.5 px-2 focus:ring-primary focus:border-primary">
                            <option value="">--</option>
                            @foreach($trabajadores as $t)
                                <option value="{{ $t->codigo }}" {{ $r->codigo_trabajador == $t->codigo ? 'selected' : '' }}>
                                    {{ $t->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" value="{{ $r->fecha_inicio ?? '' }}"
                            class="w-36 border-gray-300 rounded-md text-sm py-1.5 px-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Fecha Fin</label>
                        <input type="date" name="fecha_fin" value="{{ $r->fecha_fin ?? '' }}"
                            class="w-36 border-gray-300 rounded-md text-sm py-1.5 px-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Hora Inicio</label>
                        <input type="time" name="hora_inicio" value="{{ $r->hora_inicio ?? '' }}"
                            class="w-24 border-gray-300 rounded-md text-sm py-1.5 px-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Hora Fin</label>
                        <input type="time" name="hora_fin" value="{{ $r->hora_fin ?? '' }}"
                            class="w-24 border-gray-300 rounded-md text-sm py-1.5 px-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div class="flex gap-2 items-center">
                        <button type="submit"
                            class="px-4 py-1.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg shadow transition">
                            <i class="fas fa-save mr-1"></i> Actualizar
                        </button>
                        <button type="button" onclick="cancelarEdicion({{ $r->id_op_componentes }})"
                            class="px-4 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>

<form id="form_finalizar" action="{{ route('ordenes.procesos.finalizar', [$orden->idop, $proceso->id]) }}" method="POST">
    @csrf
</form>

<form id="form_delete" action="" method="POST">
    @csrf
    @method('DELETE')
</form>

<script>
    const urlApiFormula = '/produccion/api/formulas/composicion';
    const urlDeleteBase = "{{ url("produccion/ordenes/{$orden->idop}/procesos/{$proceso->id}/componentes") }}";
    
    const centros = @json($centros_trabajo);
    const trabajadores = @json($trabajadores);
    const tiposData = @json($tipos_producto);
    const unidadesData = @json($unidades);
    const moldesData = @json($moldes);
    const formulasData = @json($formulas_disponibles);
    const esInyectado = {{ $es_inyectado ? 'true' : 'false' }};

    function vincularFormula() {
        const sm = document.getElementById('molde_global');
        const sf = document.getElementById('formula_selector');
        if (sm && sm.selectedIndex > 0 && sm.options[sm.selectedIndex].getAttribute('data-formula')) {
            sf.value = sm.options[sm.selectedIndex].getAttribute('data-formula');
        }
    }

    function setupSearchableDropdown(rowId) {
        const row = document.getElementById(rowId);
        const searchInput = row.querySelector('.c-prod-search');
        const hiddenInput = row.querySelector('.c-prod');
        const optionsContainer = row.querySelector('.custom-options');
        const tipoSelect = row.querySelector('.c-tipo');

        let searchTimeout;

        tipoSelect.onchange = () => {
            searchInput.value = '';
            hiddenInput.value = '';
            optionsContainer.style.display = 'none';
        };

        searchInput.onfocus = () => {
            buscarProductos(searchInput.value);
            optionsContainer.style.display = 'block';
        };

        searchInput.oninput = () => {
            hiddenInput.value = '';
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => buscarProductos(searchInput.value), 300);
            optionsContainer.style.display = 'block';
        };

        function buscarProductos(filter) {
            const tipo = tipoSelect.value;
            let url = `/productos/search-ajax?q=${encodeURIComponent(filter)}`;
            if (tipo) url += `&tipo=${encodeURIComponent(tipo)}`;

            optionsContainer.innerHTML = '<div class="p-2 text-sm text-gray-400">Buscando...</div>';

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    optionsContainer.innerHTML = '';
                    if (data.length === 0) {
                        optionsContainer.innerHTML = '<div class="p-2 text-sm text-gray-500">Sin resultados</div>';
                        return;
                    }
                    data.forEach(p => {
                        const div = document.createElement('div');
                        div.className = 'p-2 cursor-pointer border-b border-gray-100 text-xs hover:bg-primary-50 text-gray-700';
                        div.textContent = `${p.id} - ${getProdName(p.text)}`;
                        div.onclick = () => {
                            searchInput.value = div.textContent;
                            hiddenInput.value = p.id;
                            if (p.codigo_tipo_producto) tipoSelect.value = p.codigo_tipo_producto;
                            optionsContainer.style.display = 'none';
                        };
                        optionsContainer.appendChild(div);
                    });
                })
                .catch(() => {
                    optionsContainer.innerHTML = '<div class="p-2 text-sm text-red-500">Error al buscar</div>';
                });
        }

        function getProdName(text) {
            const m = text.match(/\]\s*(.*)/);
            return m ? m[1] : text;
        }

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !optionsContainer.contains(e.target)) {
                optionsContainer.style.display = 'none';
            }
        });
    }

    function cargarComponentes() {
        const f = document.getElementById('formula_selector').value;
        const c = parseFloat(document.getElementById('cantidad_global').value || 0);
        let centro = '', molde = '';
        
        let url = `${urlApiFormula}?codigo_formula=${encodeURIComponent(f)}`;
        if (esInyectado) {
            centro = document.getElementById('centro_global').value;
            molde = document.getElementById('molde_global').value;
            if (!centro || !molde) return window.toast('Seleccione Inyectora y Molde.', 'warning');
            url += `&codigo_molde=${encodeURIComponent(molde)}`;
        }
        if(!f || c <= 0) return window.toast('Seleccione Fórmula y especifique una cantidad mayor a 0.', 'warning');
        
        const trabajador = document.getElementById('trabajador_global').value;
        const horaIni    = document.getElementById('hora_ini_global').value;
        const horaFin    = document.getElementById('hora_fin_global').value;

        fetch(url).then(r => r.json()).then(data => {
            if (data.success) {
                data.componentes.forEach(comp => {
                    const cant = (c * parseFloat(comp.cantidad_nominal)).toFixed(2);
                    agregarFila({ ...comp, cantidad: cant, formula: f, centro, molde,
                                 codigo_trabajador: trabajador, hora_ini: horaIni, hora_fin: horaFin });
                });
            } else {
                window.toast(data.message, 'error');
            }
        }).catch(err => {
            window.toast('Error al comunicarse con el servidor.', 'error');
            console.error(err);
        });
    }

    function agregarFilaManual() { agregarFila({}); }

    function agregarFila(item = {}) {
        const tbody = document.getElementById('tbody_items');
        const rowId = 'row_' + Date.now() + Math.floor(Math.random()*1000);
        const today = new Date().toISOString().split('T')[0];
        
        let tiposHtml = tiposData.map(t=>`<option value="${t.codigo}" ${item.codigo_tipo_producto==t.codigo?'selected':''}>${t.codigo}</option>`).join('');
        let centrosHtml = '<option value="">--</option>' + centros.map(c=>`<option value="${c.codigo}" ${item.centro==c.codigo?'selected':''}>${c.codigo}</option>`).join('');
        let moldesHtml = esInyectado ? `<td><select class="text-xs py-1 border border-gray-300 rounded c-molde">${moldesData.map(m=>`<option value="${m.codigo}" ${item.molde==m.codigo?'selected':''}>${m.codigo}</option>`).join('')}</select></td>` : '';
        let unitsHtml = unidadesData.map(u=>`<option value="${u.codigo}" ${u.codigo=='KG'?'selected':''}>${u.codigo}</option>`).join('');
        let trabsHtml = '<option value="">--</option>' + trabajadores.map(t=>
            `<option value="${t.codigo}" ${item.codigo_trabajador==t.codigo?'selected':''}>${t.nombre}</option>`
        ).join('');

        let html = `<tr id="${rowId}" class="nueva-fila bg-white">
            <td class="px-2 py-2"><select class="text-xs py-1 border border-gray-300 rounded c-tipo" style="width: 70px;">${tiposHtml}</select></td>
            
            <td class="px-2 py-2 relative">
                <input type="text" class="text-xs py-1 border border-gray-300 rounded c-prod-search w-full min-w-50" placeholder="Buscar..." value="${item.codigo_producto||''}">
                <input type="hidden" class="c-prod" value="${item.codigo_producto||''}">
                <div class="custom-options hidden absolute bg-white border border-gray-200 max-h-48 overflow-y-auto w-[300px] z-50 shadow-lg rounded mt-1"></div>
            </td>
            
            <td class="px-2 py-2"><select class="text-xs py-1 border border-gray-300 rounded c-centro" style="width: 80px;">${centrosHtml}</select></td>
            
            ${moldesHtml}
            
            <td class="px-2 py-2"><input type="number" class="text-xs py-1 border border-gray-300 rounded c-cant" style="width: 70px;" value="${item.cantidad||''}" step="0.01"></td>
            
            <td class="px-2 py-2"><select class="text-xs py-1 border border-gray-300 rounded c-um" style="width: 60px;">${unitsHtml}</select></td>
            
            <td class="px-2 py-2"><select class="text-xs py-1 border border-gray-300 rounded c-trab" style="width: 100px;">${trabsHtml}</select></td>
            
            <td style="display:none;">
                <input type="hidden" class="c-formula" value="${item.formula||document.getElementById('formula_selector')?.value||''}">
                <input type="date" class="c-fecha_ini" value="${today}"><input type="date" class="c-fecha_fin" value="${today}">
            </td>
            
            <td class="px-2 py-2 flex space-x-1">
                <input type="time" class="text-xs py-1 border border-gray-300 rounded c-hora_ini w-20" value="${item.hora_ini||'08:00'}">
                <input type="time" class="text-xs py-1 border border-gray-300 rounded c-hora_fin w-20" value="${item.hora_fin||'17:00'}">
            </td>
            
            <td class="px-2 py-2 text-center">
                <button type="button" onclick="document.getElementById('${rowId}').remove()" class="text-red-500 hover:text-red-700"><i class="fas fa-trash-alt"></i></button>
            </td>
        </tr>`;
        
        tbody.insertAdjacentHTML('afterbegin', html);
        setupSearchableDropdown(rowId);
    }

    function enviarGuardado() {
        const filas = document.querySelectorAll('.nueva-fila');
        if (filas.length === 0) return window.toast("No hay datos nuevos para guardar.", 'warning');
        
        let data = [];
        let error = false;
        
        filas.forEach(r => {
            const prod = r.querySelector('.c-prod').value;
            const cant = r.querySelector('.c-cant').value;
            if (!prod || !cant) error = true;
            
            data.push({
                codigo_tipo_producto: r.querySelector('.c-tipo').value,
                codigo_producto: prod,
                codigo_centro_trabajo: r.querySelector('.c-centro').value,
                codigo_molde: r.querySelector('.c-molde')?.value || null,
                cantidad: cant,
                codigo_unidad_medida: r.querySelector('.c-um').value,
                codigo_color: null,
                codigo_formula: r.querySelector('.c-formula').value || null,
                codigo_trabajador: r.querySelector('.c-trab').value,
                fecha_inicio: r.querySelector('.c-fecha_ini').value,
                hora_inicio: r.querySelector('.c-hora_ini').value,
                fecha_fin: r.querySelector('.c-fecha_fin').value,
                hora_fin: r.querySelector('.c-hora_fin').value
            });
        });
        
        if (error) return window.toast("Por favor seleccione un producto y especifique una cantidad en todas las filas.", 'warning');
        
        document.getElementById('componentes_json').value = JSON.stringify(data);
        document.getElementById('form_masivo').submit();
    }

    function eliminarRegistrado(id) {
        if (confirm('¿Está seguro de anular este registro?')) {
            const form = document.getElementById('form_delete');
            form.action = `${urlDeleteBase}/${id}`;
            form.submit();
        }
    }

    function editarRegistrado(id) {
        document.getElementById('row_display_' + id).classList.add('hidden');
        document.getElementById('row_edit_' + id).classList.remove('hidden');
    }

    function cancelarEdicion(id) {
        document.getElementById('row_edit_' + id).classList.add('hidden');
        document.getElementById('row_display_' + id).classList.remove('hidden');
    }
    
    function confirmarCierre() { 
        if(confirm('¿Finalizar proceso? Ya no podrá agregar más materiales y cambiará a estado COMPLETADO.')) {
            document.getElementById('form_finalizar').submit(); 
        }
    }
</script>
@endsection
