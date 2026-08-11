import codecs
import re

path = r'C:\laragon\www\LeonPlast\LeonPlast-Laravel\resources\views\produccion\procesos\ejecucion.blade.php'
with codecs.open(path, 'r', 'utf-8') as f:
    content = f.read()

# Add the script for autocompletarMaximoReciclado
target_script = '''                <script>
                    function togglePanelReciclado() {'''

replacement_script = '''                <script>
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
                                // In mezclado, nominal is usually a fraction of 1 (e.g. 0.98)
                                // But if it's ensamblado/molido, it's different. This button is mostly for Inyectado/Mezclado.
                                // For Inyectado, pesoVirgin is usually 1. For Mezclado, it's 0.98 etc.
                                // If it's Inyectado, nominal is 1 per unit. So max is simply C.
                                const esInyectado = document.getElementById('tipo_operacion') && document.getElementById('tipo_operacion').value === 'inyectado';
                                let max = c;
                                if (!esInyectado) {
                                    max = c * pesoVirgin;
                                }
                                document.getElementById('cant_reciclado_global').value = max.toFixed(2);
                            }
                        });
                    }

                    function togglePanelReciclado() {'''

content = content.replace(target_script, replacement_script)


# Now update cargarComponentes math
target_math = '''                let nuevaCantEfectiva = Math.max(0, cantEfectiva - extraCantReciclado);
                
                let pesoTotalFormula = 0;
                if (esEnsamblado || esMolido) {
                    data.componentes.forEach(c => {
                        pesoTotalFormula += parseFloat(c.cantidad_nominal) || 0;
                    });
                }

                data.componentes.forEach(comp => {
                    let cant = nuevaCantEfectiva * parseFloat(comp.cantidad_nominal);
                    let um = comp.codigo_unidad_medida;
                    
                    if (esEnsamblado || esMolido) {
                        // Opci\u00f3n A: C\u01edlculo por Proporci\u00f3n de Kilos
                        if (pesoTotalFormula > 0) {
                            cant = nuevaCantEfectiva * (parseFloat(comp.cantidad_nominal) / pesoTotalFormula);
                        } else {
                            cant = 0;
                        }
                        comp.codigo_unidad_medida = 'KG';
                    } else {
                        // Convertir gramos a kilos ya que el inventario y UI manejan KG
                        if (um === 'GR') {
                            cant = cant / 1000;
                            comp.codigo_unidad_medida = 'KG';
                        }
                    }'''

# The actual file has "C\u01edlculo" for "Cálculo". I'll use regex to match it.
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
                    } else if (!esInyectado) { 
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
                    }'''

content = re.sub(
    r'                let nuevaCantEfectiva = Math\.max\(0, cantEfectiva - extraCantReciclado\);.*?(// Convertir gramos a kilos ya que el inventario y UI manejan KG\s*if \(um === \'GR\'\) \{\s*cant = cant / 1000;\s*comp\.codigo_unidad_medida = \'KG\';\s*\}\s*\})',
    replacement_math,
    content,
    flags=re.DOTALL
)

with codecs.open(path, 'w', 'utf-8') as f:
    f.write(content)
