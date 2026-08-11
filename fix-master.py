import codecs
import re

path = r'C:\laragon\www\LeonPlast\LeonPlast-Laravel\resources\views\produccion\procesos\ejecucion.blade.php'
with codecs.open(path, 'r', 'utf-8') as f:
    content = f.read()

# 1. Update Centro label and condition
target_centro = '''                @if($es_inyectado || $es_ensamblado || $es_molido || $es_troquelado || $es_horneado)
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1" id="lbl_centro">{{ $es_troquelado ? 'Troqueladora (Centro)' : ($es_horneado ? 'Horno (Centro)' : ($es_molido ? 'Molino (Centro)' : ($es_ensamblado ? 'Ensambladora (Centro)' : 'Inyectora (Centro)'))) }}</label>'''
replacement_centro = '''                @if($es_inyectado || $es_ensamblado || $es_molido || $es_troquelado || $es_horneado || $es_mezclado)
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1" id="lbl_centro">{{ $es_troquelado ? 'Troqueladora (Centro)' : ($es_horneado ? 'Horno (Centro)' : ($es_molido ? 'Molino (Centro)' : ($es_ensamblado ? 'Ensambladora (Centro)' : ($es_mezclado ? 'Mezcladora (Centro)' : 'Inyectora (Centro)')))) }}</label>'''
content = content.replace(target_centro, replacement_centro)

# 2. Add checkbox UI
target_checkbox = '''            <div class="flex flex-wrap items-end gap-4 mt-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Trabajador</label>'''
replacement_checkbox = '''            <div class="flex flex-wrap items-end gap-4 mt-2">
                @if($es_inyectado || $es_mezclado)
                <div class="flex items-center mt-2 border border-orange-200 bg-orange-50 rounded-lg p-2 gap-3" id="panel_reciclado">
                    <label class="flex items-center text-sm font-semibold text-orange-800 cursor-pointer">
                        <input type="checkbox" id="chk_usar_reciclado" class="rounded text-orange-600 focus:ring-orange-500 mr-2" onchange="togglePanelReciclado()">
                        Utilizar Material Reciclado (Equivalente de la F\u00f3rmula)
                    </label>
                    
                    <div id="container_cant_reciclado" class="flex items-center hidden ml-2">
                        <label class="text-xs text-orange-700 font-medium mr-2">Cant. Reciclado (KG):</label>
                        <input type="number" id="cant_reciclado_global" class="w-24 border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm py-1.5 px-2" step="0.01">
                        <button type="button" onclick="autocompletarMaximoReciclado()" class="ml-2 px-2 py-1 bg-orange-200 text-orange-800 text-xs font-semibold rounded hover:bg-orange-300 transition" title="Autocompletar el 100% equivalente a materia virgen">
                            M\u00e1x (100%)
                        </button>
                    </div>
                </div>
                
                <script>
                    function autocompletarMaximoReciclado() {
                        const f = document.getElementById('formula_selector').value;
                        const c = parseFloat(document.getElementById('cantidad_global').value || 0);
                        if (!f || c <= 0) return window.toast('Seleccione F\u00f3rmula y especifique Cant. Global.', 'warning');
                        
                        let url = `{{ $url_api_formula ?? '/api/formulas/composicion' }}?codigo_formula=${encodeURIComponent(f)}`;
                        fetch(url).then(r => r.json()).then(data => {
                            if (data.success) {
                                let pesoVirgin = 0;
                                data.componentes.forEach(comp => {
                                    const desc = (comp.descripcion_producto || '').toUpperCase();
                                    const esPigmento = desc.includes('COLOR') || desc.includes('MASTERBATCH') || desc.includes('PIGMENTO') || comp.codigo_tipo_producto === 'PIG';
                                    if (!esPigmento) {
                                        pesoVirgin += parseFloat(comp.cantidad_nominal) || 0;
                                    }
                                });
                                const esInyectado = document.getElementById('tipo_operacion') && document.getElementById('tipo_operacion').value === 'inyectado';
                                let max = c;
                                if (!esInyectado) {
                                    max = c * pesoVirgin;
                                }
                                document.getElementById('cant_reciclado_global').value = max.toFixed(2);
                            }
                        });
                    }

                    function togglePanelReciclado() {
                        const chk = document.getElementById('chk_usar_reciclado');
                        const container = document.getElementById('container_cant_reciclado');
                        const inputCant = document.getElementById('cant_reciclado_global');
                        if (chk.checked) {
                            container.classList.remove('hidden');
                            inputCant.focus();
                        } else {
                            container.classList.add('hidden');
                            inputCant.value = '';
                        }
                    }
                </script>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Trabajador</label>'''
content = content.replace(target_checkbox, replacement_checkbox)

# 3. Update button text
target_btn = '''<button type="button" onclick="cargarEjecucionAgrupada()" class="px-5 py-2 {{ $es_inyectado || $es_troquelado || $es_horneado ? 'bg-orange-600 hover:bg-orange-700' : 'bg-primary hover:bg-primary-dark' }} text-white font-medium rounded-md shadow-sm transition" id="btn_cargar">
                    <i class="fas fa-box-open mr-2"></i>Cargar {{ $es_troquelado ? 'Troquelado' : ($es_horneado ? 'Horneado' : 'Inyectado') }}
                </button>'''
replacement_btn = '''<button type="button" onclick="cargarEjecucionAgrupada()" class="px-5 py-2 {{ $es_inyectado || $es_troquelado || $es_horneado ? 'bg-orange-600 hover:bg-orange-700' : 'bg-primary hover:bg-primary-dark' }} text-white font-medium rounded-md shadow-sm transition" id="btn_cargar">
                    <i class="fas fa-box-open mr-2"></i>Cargar {{ $es_troquelado ? 'Troquelado' : ($es_horneado ? 'Horneado' : ($es_mezclado ? 'F\u00f3rmula' : ($es_ensamblado ? 'Ensamblado' : ($es_molido ? 'Molido' : 'Inyectado')))) }}
                </button>'''
content = content.replace(target_btn, replacement_btn)

# 4. Update tipo_operacion value so it reflects mezclado
target_tipo = '''<input type="hidden" id="tipo_operacion" value="inyectado">'''
replacement_tipo = '''<input type="hidden" id="tipo_operacion" value="{{ $es_inyectado || $es_troquelado || $es_horneado ? 'inyectado' : ($es_mezclado ? 'mezclado' : '') }}">'''
content = content.replace(target_tipo, replacement_tipo)

# 5. Fix cargarEjecucionAgrupada fallback
target_fallback = '''        const inputTipo = document.getElementById('tipo_operacion');
        if (!inputTipo || !inputTipo.value) {
            // Fallback al flujo anterior si es mezclado/ensamblado
            cargarComponentes();
            return;
        }'''
replacement_fallback = '''        const inputTipo = document.getElementById('tipo_operacion');
        if (!inputTipo || !inputTipo.value || inputTipo.value === 'mezclado') {
            // Fallback al flujo anterior si es mezclado/ensamblado
            cargarComponentes();
            return;
        }'''
content = content.replace(target_fallback, replacement_fallback)

# 6. Update cargarComponentes Centro logic
target_centro_js = '''        } else if (esEnsamblado || esMolido) {
            centro = document.getElementById('centro_global').value;
            if (!centro) return window.toast(`Seleccione ${esMolido ? 'Molino' : 'Ensambladora'} (Centro).`, 'warning');'''
replacement_centro_js = '''        } else if (esEnsamblado || esMolido || (typeof esMezclado !== 'undefined' && esMezclado) || document.getElementById('tipo_operacion')?.value === 'mezclado' || document.getElementById('centro_global')) {
            centro = document.getElementById('centro_global') ? document.getElementById('centro_global').value : '';
            if (!centro) return window.toast(`Seleccione ${esMolido ? 'Molino' : 'Centro de Trabajo'}.`, 'warning');'''
content = content.replace(target_centro_js, replacement_centro_js)

# 7. Add Reciclado variables to cargarComponentes
target_rec_vars = '''        const trabajador = document.getElementById('trabajador_global').value;
        const fechaGlobal = document.getElementById('fecha_global') ? document.getElementById('fecha_global').value : '';
        const horaIni    = document.getElementById('hora_ini_global').value;
        const horaFin    = document.getElementById('hora_fin_global').value;'''
replacement_rec_vars = '''        const trabajador = document.getElementById('trabajador_global').value;
        const fechaGlobal = document.getElementById('fecha_global') ? document.getElementById('fecha_global').value : '';
        const horaIni    = document.getElementById('hora_ini_global').value;
        const horaFin    = document.getElementById('hora_fin_global').value;
        
        const chkReciclado = document.getElementById('chk_usar_reciclado');
        const inputCantReciclado = document.getElementById('cant_reciclado_global');
        const usar_reciclado = chkReciclado && chkReciclado.checked ? 1 : 0;
        const extraCantReciclado = usar_reciclado ? (parseFloat(inputCantReciclado.value) || 0) : 0;'''
content = content.replace(target_rec_vars, replacement_rec_vars)

# 8. Update math logic in cargarComponentes using regex, also adding `if (cant > 0)` for zero row suppression
replacement_math = '''                let pesoVirgin = 0;
                let pesoTotalFormula = 0;
                data.componentes.forEach(comp => {
                    pesoTotalFormula += parseFloat(comp.cantidad_nominal) || 0;
                    const desc = (comp.descripcion_producto || '').toUpperCase();
                    const esPigmento = desc.includes('COLOR') || desc.includes('MASTERBATCH') || desc.includes('PIGMENTO') || comp.codigo_tipo_producto === 'PIG';
                    if (!esPigmento) {
                        pesoVirgin += parseFloat(comp.cantidad_nominal) || 0;
                    }
                });

                // nuevaCantEfectiva represents remaining Virgen material overall
                let nuevaCantEfectiva = Math.max(0, cantEfectiva - extraCantReciclado);

                data.componentes.forEach(comp => {
                    const desc = (comp.descripcion_producto || '').toUpperCase();
                    const esPigmento = desc.includes('COLOR') || desc.includes('MASTERBATCH') || desc.includes('PIGMENTO') || comp.codigo_tipo_producto === 'PIG';
                    
                    let cant = 0;
                    let um = comp.codigo_unidad_medida;
                    
                    if (esEnsamblado || esMolido) {
                        if (pesoTotalFormula > 0) {
                            cant = nuevaCantEfectiva * (parseFloat(comp.cantidad_nominal) / pesoTotalFormula);
                        }
                        comp.codigo_unidad_medida = 'KG';
                    } else if (document.getElementById('tipo_operacion') && document.getElementById('tipo_operacion').value === 'mezclado') { 
                        // Es Mezclado
                        if (esPigmento) {
                            cant = cantEfectiva * parseFloat(comp.cantidad_nominal);
                        } else {
                            if (pesoVirgin > 0) {
                                const totalVirgenRequerido = cantEfectiva * pesoVirgin;
                                const virgenRestante = Math.max(0, totalVirgenRequerido - extraCantReciclado);
                                cant = virgenRestante * (parseFloat(comp.cantidad_nominal) / pesoVirgin);
                            } else {
                                cant = 0;
                            }
                        }
                        if (um === 'GR') {
                            cant = cant / 1000;
                            comp.codigo_unidad_medida = 'KG';
                        }
                    } else {
                        // Es Inyectado
                        cant = nuevaCantEfectiva * parseFloat(comp.cantidad_nominal);
                        if (um === 'GR') {
                            cant = cant / 1000;
                            comp.codigo_unidad_medida = 'KG';
                        }
                    }

                    if (cant > 0) {
                        // Forzar a mostrar 4 decimales para mayor precisi\u00f3n en KG
                        agregarFila({ ...comp, cantidad: cant.toFixed(4), formula: f, centro, molde,
                                     codigo_trabajador: trabajador, hora_ini: horaIni, hora_fin: horaFin, fecha: fechaGlobal });
                    }
                });
                
                if (extraCantReciclado > 0 && data.codigo_material_reciclado) {
                    agregarFila({ 
                        codigo_producto: data.codigo_material_reciclado,
                        descripcion_producto: data.descripcion_material_reciclado || 'MATERIAL RECICLADO / MEZCLADO',
                        codigo_tipo_producto: 'REC', 
                        descripcion_tipo_producto: 'RECICLADO',
                        cantidad: extraCantReciclado.toFixed(4), 
                        codigo_unidad_medida: 'KG',
                        formula: f, centro, molde,
                        codigo_trabajador: trabajador, hora_ini: horaIni, hora_fin: horaFin, fecha: fechaGlobal 
                    });
                }'''

content = re.sub(
    r'                let pesoTotalFormula = 0;.*?hora_fin: horaFin, fecha: fechaGlobal \}\);\s*\}\);',
    replacement_math,
    content,
    flags=re.DOTALL
)

with codecs.open(path, 'w', 'utf-8') as f:
    f.write(content)
