<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, AdminController, UnidadMedidaController,
    TipoProductoController, ProductoController, ProcesoProduccionController,
    FormulaController, OperacionProduccionController, CentroTrabajoController,
    TrabajadorController, ProveedorController, ActividadProduccionController,
    MoldeController, ColorController, UsuarioController, CompraController,
    InventarioController, AlmacenController, RolController,
    OrdenProduccionController, OrdenProcesoController, ReporteController
};
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Web Routes - Leon Plast Producción
|--------------------------------------------------------------------------
*/

// RUTA TEMPORAL PARA ACTUALIZAR LA CONTRASEÑA
Route::get('/fix-password', function () {
    $usuario = Usuario::where('nombre_usuario', 'admin')->first();
    if ($usuario) {
        $usuario->contrasena_hash = Hash::make('admin123');
        $usuario->save();
        return '¡Contraseña actualizada con éxito!';
    }
    return 'No se encontró al usuario admin.';
});

// Rutas para usuarios NO autenticados
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
});

// Rutas Protegidas (Requieren Login)
Route::middleware('auth')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/productos/search-ajax', [ProductoController::class, 'searchAjax'])->name('api.productos.search');

    // =========================================================
    // MODULO ADMINISTRATIVO (Tablas Maestras)
    // =========================================================
    Route::middleware('role:Administrador')->prefix('admin')->group(function () {
        
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

        // Unidades de Medida
        Route::resource('unidades-medida', UnidadMedidaController::class)->names('unidades_medida')->parameters(['unidades-medida' => 'codigo']);
        
        // Tipos de Producto
        Route::resource('tipos-producto', TipoProductoController::class)->names('tipos_producto')->parameters(['tipos-producto' => 'codigo']);
        
        // Productos
        Route::resource('productos', ProductoController::class)->names('productos')->parameters(['productos' => 'codigo']);
        
        // Procesos de Producción
        Route::resource('procesos-produccion', ProcesoProduccionController::class)->names('procesos_produccion')->parameters(['procesos-produccion' => 'codigo']);
        
        // Fórmulas
        Route::resource('formulas', FormulaController::class)->names('formulas')->parameters(['formulas' => 'codigo']);
        Route::get('formulas/{codigo}/composicion', [FormulaController::class, 'composicion'])->name('formulas.composicion');
        Route::post('formulas/{codigo}/composicion', [FormulaController::class, 'storeComposicion'])->name('formulas.storeComposicion');

        // Otros Maestros
        Route::resource('operaciones-produccion', OperacionProduccionController::class)->names('operaciones_produccion');
        Route::resource('centros-trabajo', CentroTrabajoController::class)->names('centros_trabajo');
        Route::resource('trabajadores', TrabajadorController::class)->names('trabajadores');
        Route::resource('actividades', ActividadProduccionController::class)->names('actividades');
        Route::resource('moldes', MoldeController::class)->names('moldes');
        Route::resource('colores', ColorController::class)->names('colores');
        Route::resource('usuarios', UsuarioController::class)->names('usuarios');
        Route::resource('roles', RolController::class)->names('roles');

        // Proveedores (Con soporte AJAX)
        Route::post('proveedores/ajax', [ProveedorController::class, 'storeAjax'])->name('proveedores.storeAjax');
        Route::resource('proveedores', ProveedorController::class)->names('proveedores');

        // Almacenes
        Route::resource('almacenes', AlmacenController::class)->names('almacenes')->parameters(['almacenes' => 'codigo']);
    });

    // =========================================================
    // MÓDULOS DE COMPRAS (Administrador y Supervisor)
    // =========================================================
    Route::middleware('role:Administrador,Supervisor')->prefix('admin')->group(function () {
        Route::resource('compras', CompraController::class)->names('compras');
        Route::post('compras/{id}/anular', [CompraController::class, 'anular'])->name('compras.anular');
    });

    // =========================================================
    // MÓDULO DE INVENTARIO SEPARADO (Consolidado y sin errores)
    // =========================================================
    Route::prefix('admin/inventario')->group(function () {
        
        // 1. Stock Actual (Disponible para todos los autenticados)
        Route::get('/', [InventarioController::class, 'index'])->name('inventario.index');

        // 2. Kardex / Historial de Movimientos
        // Nota: Cambiamos el nombre a plural 'inventario.kardex' para ser consistentes
        Route::get('/kardex', [InventarioController::class, 'kardex'])->name('inventario.kardex');

        // 3. Acciones de Almacén (Restringidas a Administrador y Supervisor)
        Route::middleware('role:Administrador,Supervisor')->group(function () {
            // Recepciones
            Route::get('/recepciones', [InventarioController::class, 'recepciones'])->name('inventario.recepciones');
            Route::post('/recibir/{id}', [InventarioController::class, 'procesarRecepcion'])->name('inventario.procesar_recepcion');
            Route::post('/recibir-produccion/{id}', [InventarioController::class, 'procesarRecepcionProduccion'])->name('inventario.procesar_recepcion_produccion');

            // Ajustes Manuales
            Route::get('/ajuste', [InventarioController::class, 'ajuste'])->name('inventario.ajuste');
            Route::post('/ajuste', [InventarioController::class, 'storeAjuste'])->name('inventario.store_ajuste');

            // Bandeja de Ajustes (CRUD)
            Route::get('/ajuste/lista', [InventarioController::class, 'ajustesIndex'])->name('inventario.ajuste.lista');
            Route::get('/ajuste/{kardex}', [InventarioController::class, 'showAjuste'])->name('inventario.ajuste.show');
            Route::get('/ajuste/{kardex}/editar', [InventarioController::class, 'editAjuste'])->name('inventario.ajuste.edit');
            Route::put('/ajuste/{kardex}', [InventarioController::class, 'updateAjuste'])->name('inventario.ajuste.update');
            Route::delete('/ajuste/{kardex}', [InventarioController::class, 'destroyAjuste'])->name('inventario.ajuste.destroy');
        });

        // 4. Extornos y Auditoría (SOLO Administrador)
        Route::middleware('role:Administrador')->group(function () {
            Route::get('/extornos', [InventarioController::class, 'extornos'])->name('inventario.extornos');
            Route::post('/extornos/procesar/{id}', [InventarioController::class, 'procesarExtorno'])->name('inventario.procesar_extorno');
        });
    });

    // =========================================================
    // MÓDULO DE PRODUCCIÓN
    // =========================================================
    Route::prefix('produccion')->group(function () {
        // Órdenes de Producción
        Route::resource('ordenes', OrdenProduccionController::class)->names([
            'index' => 'produccion.ordenes.index',
            'create' => 'produccion.ordenes.create',
            'store' => 'produccion.ordenes.store',
            'destroy' => 'produccion.ordenes.destroy',
        ])->except(['show', 'edit', 'update']);
        
        // Procesos de la Orden
        Route::get('ordenes/{orden}/procesos', [OrdenProcesoController::class, 'index'])->name('ordenes.procesos.index');
        Route::get('ordenes/{orden}/procesos/create', [OrdenProcesoController::class, 'create'])->name('ordenes.procesos.create');
        Route::post('ordenes/{orden}/procesos', [OrdenProcesoController::class, 'store'])->name('ordenes.procesos.store');
        Route::delete('procesos/{proceso}', [OrdenProcesoController::class, 'destroy'])->name('ordenes.procesos.destroy');
        Route::get('ordenes/{orden}/procesos/{proceso}/ejecutar', [OrdenProcesoController::class, 'ejecutar'])->name('ordenes.procesos.ejecutar');
        Route::post('ordenes/{orden}/procesos/{proceso}/componentes', [OrdenProcesoController::class, 'storeComponentes'])->name('ordenes.procesos.componentes.store');
        Route::delete('ordenes/{orden}/procesos/{proceso}/componentes/{componente}', [OrdenProcesoController::class, 'destroyComponente'])->name('ordenes.procesos.componentes.destroy');
        Route::put('ordenes/{orden}/procesos/{proceso}/componentes/{componente}', [OrdenProcesoController::class, 'updateComponente'])->name('ordenes.procesos.componentes.update');
        Route::post('ordenes/{orden}/procesos/{proceso}/finalizar', [OrdenProcesoController::class, 'finalizar'])->name('ordenes.procesos.finalizar');
        Route::get('api/formulas/composicion', [OrdenProcesoController::class, 'getFormulaComponents'])->name('api.formulas.composicion');
    });

    // =========================================================
    // OTROS ROLES (Dashboards específicos)
    // =========================================================
    Route::get('/supervisor/dashboard', function () {
        $ordenesActivas = DB::table('orden_produccion_global')
            ->where('activo', 1)
            ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
            ->count();

        $pendientesValidar = DB::table('produccion_ingresos_proceso')
            ->where('estado', 'PENDIENTE')
            ->count();

        $produccionDia = DB::table('componentes_orden_produccion_global')
            ->whereDate('fecha_creacion', now()->toDateString())
            ->where('estado', 1)
            ->sum('cantidad');

        return view('supervisor.dashboard', compact('ordenesActivas', 'pendientesValidar', 'produccionDia'));
    })->name('supervisor.dashboard')->middleware('role:Supervisor');

    Route::get('/especialista/dashboard', function () {
        $totalFormulas = DB::table('formula_produccion')->where('estado', 1)->count();
        $totalComposiciones = DB::table('composicion_formula')->count();
        $totalProductos = DB::table('producto')->where('estado', 1)->count();
        $totalProcesos = DB::table('proceso_produccion')->where('estado', 1)->count();
        return view('especialista.dashboard', compact('totalFormulas', 'totalComposiciones', 'totalProductos', 'totalProcesos'));
    })->name('especialista.dashboard')->middleware('role:Especialista');

    // =========================================================
    // MÓDULO DE REPORTES
    // =========================================================
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/produccion', [ReporteController::class, 'produccion'])->name('produccion');
        Route::get('/inventario', [ReporteController::class, 'inventario'])->name('inventario');
    });

});