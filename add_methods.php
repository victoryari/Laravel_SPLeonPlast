<?php
$file = __DIR__.'/app/Http/Controllers/GuiaRemisionCompraController.php';
$content = file_get_contents($file);

$methods = <<<PHP

    public function edit(\$id)
    {
        \$guia = GuiaRemisionCompra::with('detalles.producto')->findOrFail(\$id);
        
        if (\$guia->estado !== 'RECIBIDA') {
            return redirect()->route('guia_compras.show', \$id)->with('error', 'Solo se pueden editar guías en estado RECIBIDA.');
        }

        \$proveedores = Proveedor::where('activo', 1)->get();
        \$productos = Producto::where('estado', 1)->get();
        \$unidades_medida = \App\Models\UnidadMedida::where('estado', 1)->get();

        return view('guia_compras.edit', compact('guia', 'proveedores', 'productos', 'unidades_medida'));
    }

    public function update(Request \$request, \$id)
    {
        \$guia = GuiaRemisionCompra::findOrFail(\$id);
        
        if (\$guia->estado !== 'RECIBIDA') {
            return redirect()->route('guia_compras.show', \$id)->with('error', 'Solo se pueden editar guías en estado RECIBIDA.');
        }

        \$request->validate([
            'proveedor' => 'required|string|max:100',
            'ruc_proveedor' => 'nullable|string|max:11',
            'numero_guia' => 'required|string|max:20',
            'fecha_emision' => 'required|date',
            'productos' => 'required|array|min:1',
            'productos.*.codigo_producto' => 'required|string',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.lote' => 'nullable|string|max:50',
            'productos.*.fecha_vencimiento' => 'nullable|date',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            \$almacen_destino = 'ALM04';

            // 1. Revertir inventario de ALM04 de los detalles antiguos
            \$kardexService = app(KardexService::class);
            foreach (\$guia->detalles as \$detalle_antiguo) {
                // Obtener stock actual
                \$inventario = DB::table('inventario')
                    ->where('codigo_producto', \$detalle_antiguo->codigo_producto)
                    ->where('codigo_almacen', \$almacen_destino)
                    ->lockForUpdate()
                    ->first();
                    
                \$stock_actual = \$inventario ? \$inventario->stock_actual : 0;
                \$nuevo_saldo = max(0, \$stock_actual - \$detalle_antiguo->cantidad);
                
                if (\$inventario) {
                    DB::table('inventario')
                        ->where('id_inventario', \$inventario->id_inventario)
                        ->update([
                            'stock_actual' => \$nuevo_saldo,
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => Auth::id()
                        ]);
                }

                // Obtener ultimo costo para la salida (extorno)
                \$ultimoKardex = DB::table('kardex')
                    ->where('codigo_producto', \$detalle_antiguo->codigo_producto)
                    ->where('codigo_almacen', \$almacen_destino)
                    ->orderBy('id_kardex', 'desc')
                    ->first();
                \$costoPromedioActual = \$ultimoKardex ? \$ultimoKardex->costo_promedio : 0;
                
                \$costos = \$kardexService->calcularCostos(
                    \$detalle_antiguo->codigo_producto, 
                    \$almacen_destino,
                    0, \$costoPromedioActual,
                    \$detalle_antiguo->cantidad, \$nuevo_saldo
                );

                DB::table('kardex')->insert([
                    'codigo_producto'      => \$detalle_antiguo->codigo_producto,
                    'codigo_almacen'       => \$almacen_destino,
                    'codigo_unidad_medida' => \$detalle_antiguo->codigo_unidad_medida,
                    'fecha_movimiento'     => now(),
                    'tipo_movimiento'      => 'EXTORNO',
                    'documento'            => 'GUIA REMISION',
                    'numero_documento'     => \$guia->numero_guia,
                    'cantidad_entrada'     => 0,
                    'costo_entrada'        => 0,
                    'total_entrada'        => 0,
                    'cantidad_salida'      => \$detalle_antiguo->cantidad,
                    'costo_salida'         => \$costoPromedioActual,
                    'total_salida'         => \$detalle_antiguo->cantidad * \$costoPromedioActual,
                    'cantidad_saldo'       => \$nuevo_saldo,
                    'costo_promedio'       => \$costos['costo_promedio'],
                    'total_saldo'          => \$costos['total_saldo'],
                    'observaciones'        => 'EXTORNO POR EDICION DE GUIA',
                    'usuario_registro'     => Auth::id()
                ]);
            }

            // 2. Eliminar detalles antiguos
            DetalleGuiaCompra::where('id_guia', \$id)->delete();

            // 3. Actualizar la cabecera
            \$guia->update([
                'proveedor' => \$request->proveedor,
                'ruc_proveedor' => \$request->ruc_proveedor,
                'numero_guia' => \$request->numero_guia,
                'fecha_emision' => \$request->fecha_emision,
                'observaciones' => \$request->observaciones
            ]);

            // 4. Crear los nuevos detalles e ingresarlos a ALM04
            foreach (\$request->productos as \$item) {
                \$producto = Producto::where('codigo', \$item['codigo_producto'])->first();
                \$unidad_medida = \$item['codigo_unidad_medida'] ?? (\$producto->unidad_medida_codigo ?? 'NIU');

                DetalleGuiaCompra::create([
                    'id_guia' => \$guia->id_guia,
                    'codigo_producto' => \$item['codigo_producto'],
                    'descripcion_producto' => \$producto->descripcion ?? '',
                    'cantidad' => \$item['cantidad'],
                    'codigo_unidad_medida' => \$unidad_medida,
                    'codigo_almacen' => \$almacen_destino,
                    'lote' => \$item['lote'] ?? null,
                    'fecha_vencimiento' => \$item['fecha_vencimiento'] ?? null
                ]);

                // Ingresar stock de nuevo a ALM04
                \$inventario = DB::table('inventario')
                    ->where('codigo_producto', \$item['codigo_producto'])
                    ->where('codigo_almacen', \$almacen_destino)
                    ->lockForUpdate()
                    ->first();
                    
                \$stock_actual = \$inventario ? \$inventario->stock_actual : 0;
                \$nuevo_saldo = \$stock_actual + \$item['cantidad'];
                
                if (\$inventario) {
                    DB::table('inventario')
                        ->where('id_inventario', \$inventario->id_inventario)
                        ->update([
                            'stock_actual' => \$nuevo_saldo,
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => Auth::id()
                        ]);
                } else {
                    DB::table('inventario')->insert([
                        'codigo_producto' => \$item['codigo_producto'],
                        'codigo_almacen' => \$almacen_destino,
                        'stock_actual' => \$nuevo_saldo,
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]);
                }

                \$ultimoKardex = DB::table('kardex')
                    ->where('codigo_producto', \$item['codigo_producto'])
                    ->where('codigo_almacen', \$almacen_destino)
                    ->orderBy('id_kardex', 'desc')
                    ->first();
                \$costoPromedioActual = \$ultimoKardex ? \$ultimoKardex->costo_promedio : 0;
                
                \$costos = \$kardexService->calcularCostos(
                    \$item['codigo_producto'], 
                    \$almacen_destino,
                    \$item['cantidad'], \$costoPromedioActual,
                    0, \$nuevo_saldo
                );

                DB::table('kardex')->insert([
                    'codigo_producto'      => \$item['codigo_producto'],
                    'codigo_almacen'       => \$almacen_destino,
                    'codigo_unidad_medida' => \$unidad_medida,
                    'fecha_movimiento'     => now(),
                    'tipo_movimiento'      => 'INGRESO',
                    'documento'            => 'GUIA REMISION',
                    'numero_documento'     => \$guia->numero_guia,
                    'cantidad_entrada'     => \$item['cantidad'],
                    'costo_entrada'        => \$costoPromedioActual,
                    'total_entrada'        => \$item['cantidad'] * \$costoPromedioActual,
                    'cantidad_salida'      => 0,
                    'costo_salida'         => 0,
                    'total_salida'         => 0,
                    'cantidad_saldo'       => \$nuevo_saldo,
                    'costo_promedio'       => \$costos['costo_promedio'],
                    'total_saldo'          => \$costos['total_saldo'],
                    'lote'                 => \$item['lote'] ?? null,
                    'observaciones'        => 'INGRESO POR EDICION DE GUIA',
                    'usuario_registro'     => Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->route('guia_compras.show', \$id)->with('success_ask', 'Guía de Remisión actualizada exitosamente.');

        } catch (\Exception \$e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al actualizar guía: ' . \$e->getMessage());
        }
    }

    public function deshacerUbicacion(Request \$request, \$id)
    {
        try {
            DB::beginTransaction();

            \$guia = GuiaRemisionCompra::with('detalles')->findOrFail(\$id);
            
            if (\$guia->estado !== 'UBICADA') {
                throw new \Exception("La guía no está en estado UBICADA.");
            }

            \$kardexService = app(KardexService::class);
            \$almacen_origen = 'ALM04';

            // 1. Validar si todo el stock en los almacenes destino aun existe
            foreach (\$guia->detalles as \$detalle) {
                // Durante la reubicación, la guia vacio ALM04 y los envió a detalle->codigo_almacen (destino final)
                // Wait! When the user "Ubicó" the guide, where did it go?
                // The transfer in InventarioController changes the detalle->codigo_almacen to the destination!
                // Let's verify: In procesarUbicacionGuia, does it update detalle->codigo_almacen?
                // Actually, if they were sent to different warehouses, they are recorded somewhere. Let me check.
                // Wait! In procesarUbicacionGuia, we just read the Request and make Kardex inserts. Does it save the target warehouse in the detalle?
                // I need to confirm this.
            }

            // DB::commit();
            // return redirect()->back()->with('success_ask', 'Ubicación deshecha correctamente. La guía regresó a RECIBIDA.');

        } catch (\Exception \$e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al deshacer ubicación: ' . \$e->getMessage());
        }
    }
}
PHP;

$content = preg_replace('/\}\s*$/', $methods, $content);
file_put_contents($file, $content);
echo "OK";
