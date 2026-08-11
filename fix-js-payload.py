import codecs

path = r'C:\laragon\www\LeonPlast\LeonPlast-Laravel\resources\views\produccion\procesos\ejecucion.blade.php'
with codecs.open(path, 'r', 'utf-8') as f:
    content = f.read()

# 1. Revert fallback in cargarEjecucionAgrupada
content = content.replace(
    "if (!inputTipo || !inputTipo.value || inputTipo.value === 'mezclado') {",
    "if (!inputTipo || !inputTipo.value) {"
)

# 2. Add usar_reciclado and cantidad_reciclado to the payload in cargarEjecucionAgrupada
target_payload = '''        const payload = {
            _token: '{{ csrf_token() }}',
            tipo_operacion: tipo_operacion,
            codigo_formula: formula,
            cantidad_total: cantidad,
            codigo_molde: molde,
            codigo_centro_trabajo: centro,
            codigo_trabajador: trabajador,
            fecha: fecha,
            hora_inicio: hora_ini,
            hora_fin: hora_fin,
            codigo_almacen_consumo: almacen,
            codigo_color: color
        };'''

replacement_payload = '''        const chkReciclado = document.getElementById('chk_usar_reciclado');
        const inputCantReciclado = document.getElementById('cant_reciclado_global');
        const usar_reciclado = chkReciclado && chkReciclado.checked ? 1 : 0;
        const cantidad_reciclado = usar_reciclado ? (parseFloat(inputCantReciclado.value) || 0) : 0;

        const payload = {
            _token: '{{ csrf_token() }}',
            tipo_operacion: tipo_operacion,
            codigo_formula: formula,
            cantidad_total: cantidad,
            codigo_molde: molde,
            codigo_centro_trabajo: centro,
            codigo_trabajador: trabajador,
            fecha: fecha,
            hora_inicio: hora_ini,
            hora_fin: hora_fin,
            codigo_almacen_consumo: almacen,
            codigo_color: color,
            usar_reciclado: usar_reciclado,
            cantidad_reciclado: cantidad_reciclado
        };'''
content = content.replace(target_payload, replacement_payload)

# 3. Allow grouped components to render for Mezclado
target_table_if = '''@if($es_inyectado && isset($cargas_agrupadas))'''
replacement_table_if = '''@if(($es_inyectado || $es_mezclado || $es_troquelado || $es_horneado) && isset($cargas_agrupadas))'''
content = content.replace(target_table_if, replacement_table_if)

with codecs.open(path, 'w', 'utf-8') as f:
    f.write(content)
