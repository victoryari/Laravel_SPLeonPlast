import codecs

path = r'C:\laragon\www\LeonPlast\LeonPlast-Laravel\app\Http\Controllers\OrdenProcesoController.php'
with codecs.open(path, 'r', 'utf-8') as f:
    content = f.read()

target = '''        if ($componentes->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'La f\u00f3rmula no tiene componentes o no coinciden los par\u01edmetros de stock/molde.']);
        }

        return response()->json(['success' => true, 'componentes' => $componentes->values()]);'''
# Note: since we don't know the exact corrupt string due to decoding issues on the console, 
# let's just do a regex replace or shorter replace.

import re
content = re.sub(
    r"return response\(\)->json\(\['success' => true, 'componentes' => \$componentes->values\(\)\]\);",
    r'''$formulaData = DB::table('formula_produccion')->where('codigo', $codigo_formula)->first();
        $codigo_material_reciclado = $formulaData ? $formulaData->codigo_material_reciclado : null;

        $descripcion_material_reciclado = null;
        if ($codigo_material_reciclado) {
            $descripcion_material_reciclado = DB::table('producto')->where('codigo', $codigo_material_reciclado)->value('descripcion');
        }

        return response()->json([
            'success' => true, 
            'componentes' => $componentes->values(),
            'codigo_material_reciclado' => $codigo_material_reciclado,
            'descripcion_material_reciclado' => $descripcion_material_reciclado
        ]);''',
    content
)

with codecs.open(path, 'w', 'utf-8') as f:
    f.write(content)
