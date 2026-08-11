import codecs

path = r'C:\laragon\www\LeonPlast\LeonPlast-Laravel\resources\views\produccion\procesos\ejecucion.blade.php'
with codecs.open(path, 'r', 'utf-8') as f:
    content = f.read()

target = '''            <div class="flex flex-wrap items-end gap-4 mt-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Trabajador</label>'''

replacement = '''            <div class="flex flex-wrap items-end gap-4 mt-2">
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

content = content.replace(target, replacement)

with codecs.open(path, 'w', 'utf-8') as f:
    f.write(content)
