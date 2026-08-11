import codecs
import re

path = r'C:\laragon\www\LeonPlast\LeonPlast-Laravel\app\Http\Controllers\OrdenProcesoController.php'
with codecs.open(path, 'r', 'utf-8') as f:
    content = f.read()

target = '''            // Segundo pase: Preparar los componentes a insertar con la distribucion correcta
            $componentes_a_insertar = [];
            foreach ($componentes_a_procesar as $comp) {
                $cant = floatval($comp['cantidad_nominal']);
                $um = $comp['codigo_unidad_medida'];

                if ($es_ensamblado_molido || $es_limpieza) {
                    if ($pesoTotalFormula > 0) {
                        $cant = $nueva_cantidad_total * ($cant / $pesoTotalFormula);
                    } else {
                        $cant = 0;
                    }
                    $um = 'KG';
                } else {
                    $cant = $nueva_cantidad_total * $cant;
                    if ($um === 'GR') {
                        $cant = $cant / 1000;
                        $um = 'KG';
                    }
                }

                if ($cant > 0) {
                    $componentes_a_insertar[] = [
                        'codigo_producto' => $comp['codigo_producto'],
                        'cantidad' => $cant,
                        'codigo_unidad_medida' => $um,
                        'codigo_molde' => $comp['codigo_molde'] ?? $codigo_molde,
                        'codigo_tipo_producto' => $comp['codigo_tipo_producto']
                    ];
                }
            }'''

replacement = '''            // Segundo pase: Preparar los componentes a insertar con la distribucion correcta
            $pesoVirgin = 0;
            foreach ($componentes_a_procesar as $comp) {
                $desc = strtoupper($comp['descripcion_producto'] ?? '');
                $esPigmento = str_contains($desc, 'COLOR') || str_contains($desc, 'MASTERBATCH') || str_contains($desc, 'PIGMENTO') || $comp['codigo_tipo_producto'] === 'PIG';
                if (!$esPigmento) {
                    $pesoVirgin += floatval($comp['cantidad_nominal']);
                }
            }

            $componentes_a_insertar = [];
            foreach ($componentes_a_procesar as $comp) {
                $cant = floatval($comp['cantidad_nominal']);
                $um = $comp['codigo_unidad_medida'];
                $desc = strtoupper($comp['descripcion_producto'] ?? '');
                $esPigmento = str_contains($desc, 'COLOR') || str_contains($desc, 'MASTERBATCH') || str_contains($desc, 'PIGMENTO') || $comp['codigo_tipo_producto'] === 'PIG';

                if ($es_ensamblado_molido || $es_limpieza) {
                    if ($pesoTotalFormula > 0) {
                        $cant = $nueva_cantidad_total * ($cant / $pesoTotalFormula);
                    } else {
                        $cant = 0;
                    }
                    $um = 'KG';
                } else if ($tipo_operacion === 'mezclado') {
                    if ($esPigmento) {
                        $cant = $cantidad_total * $cant;
                    } else {
                        if ($pesoVirgin > 0) {
                            $totalVirgenRequerido = $cantidad_total * $pesoVirgin;
                            $virgenRestante = max(0, $totalVirgenRequerido - $extraCantReciclado);
                            $cant = $virgenRestante * ($cant / $pesoVirgin);
                        } else {
                            $cant = 0;
                        }
                    }
                    if ($um === 'GR') {
                        $cant = $cant / 1000;
                        $um = 'KG';
                    }
                } else {
                    // Es Inyectado
                    $cant = $nueva_cantidad_total * $cant;
                    if ($um === 'GR') {
                        $cant = $cant / 1000;
                        $um = 'KG';
                    }
                }

                if ($cant > 0) {
                    $componentes_a_insertar[] = [
                        'codigo_producto' => $comp['codigo_producto'],
                        'cantidad' => $cant,
                        'codigo_unidad_medida' => $um,
                        'codigo_molde' => $comp['codigo_molde'] ?? $codigo_molde,
                        'codigo_tipo_producto' => $comp['codigo_tipo_producto']
                    ];
                }
            }'''

if target in content:
    content = content.replace(target, replacement)
else:
    print("WARNING: TARGET NOT FOUND. Attempting regex.")
    content = re.sub(
        r'// Segundo pase: Preparar los componentes a insertar con la distribucion correcta.*?\}\s*\}',
        replacement,
        content,
        flags=re.DOTALL
    )

with codecs.open(path, 'w', 'utf-8') as f:
    f.write(content)
