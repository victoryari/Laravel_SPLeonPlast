# Plan de Implementación - HU-01: Requerimientos de Materiales

**Fecha:** 2026-06-02
**Proyecto:** LeonPlast - Sistema de Control de Producción
**Historia de Usuario:** Como Supervisor de Producción / Almacenero, quiero generar y gestionar requerimientos de materiales con asignación específica de lotes para cada orden de producción, para asegurar la trazabilidad completa desde la compra hasta el producto terminado.

---

## 1. Resumen de la Solución

Se creará un nuevo módulo **Requerimientos de Materiales** que permite:

- **Creación** de solicitudes formales de materiales con origen y destino por línea
- **Flujo de aprobación** en 3 etapas: Borrador -> Pendiente -> Aprobado/Rechazado
- **Despacho como transferencia** entre almacenes (SALIDA origen + INGRESO destino)
- **Trazabilidad por lotes** con sugerencia FIFO en el despacho
- **Despacho parcial** por cantidades (múltiples sesiones hasta completar)

---

## 2. Roles y Permisos

| Acción | Rol(es) | Ruta |
|--------|---------|------|
| Ver listado | Admin, Supervisor, Especialista | `requerimientos_materiales.index` |
| Crear / Editar / Enviar | Supervisor, Especialista | `.create`, `.store`, `.edit`, `.update`, `.enviar` |
| Aprobar / Rechazar | Administrador | `.aprobar`, `.rechazar` |
| Atender (despachar) | Administrador, Supervisor | `.atender`, `.store_atender` |
| Anular | Administrador | `.anular` |
| Ver detalle | Admin, Supervisor, Especialista | `.show` |

---

## 3. Máquina de Estados

```
BORRADOR
    │  [Enviar: Sup/Esp]
    ▼
PENDIENTE
    │
    ├── [Aprobar: Admin] ──▶  APROBADO
    │                            │  [Atender: Sup/Admin]
    │                            ▼
    │                      ATENDIDO_PARCIAL
    │                            │  (hasta completar)
    │                            ▼
    │                      ATENDIDO_TOTAL
    │
    └── [Rechazar: Admin, obs. obligatoria] ──▶  RECHAZADO

Cualquier estado excepto ATENDIDO_TOTAL:
    └── [Anular: Admin] ──▶  ANULADO
```

---

## 4. Base de Datos

### 4.1 Migración: `requerimientos_materiales`

```php
Schema::create('requerimientos_materiales', function (Blueprint $table) {
    $table->bigIncrements('id_requerimiento');
    $table->string('codigo', 20)->unique();
    $table->unsignedBigInteger('idop')->nullable();
    $table->string('motivo', 500)->nullable();
    $table->enum('estado', [
        'BORRADOR', 'PENDIENTE', 'APROBADO', 'RECHAZADO',
        'ATENDIDO_PARCIAL', 'ATENDIDO_TOTAL', 'ANULADO'
    ])->default('BORRADOR');
    $table->unsignedBigInteger('usuario_creacion');
    $table->unsignedBigInteger('usuario_aprobacion')->nullable();
    $table->timestamp('fecha_creacion')->useCurrent();
    $table->timestamp('fecha_aprobacion')->nullable();
    $table->text('observaciones')->nullable();

    $table->foreign('idop')->references('idop')->on('orden_produccion_global');
    $table->foreign('usuario_creacion')->references('id')->on('usuarios');
    $table->foreign('usuario_aprobacion')->references('id')->on('usuarios');
});
```

### 4.2 Migración: `detalle_requerimientos_materiales`

```php
Schema::create('detalle_requerimientos_materiales', function (Blueprint $table) {
    $table->bigIncrements('id_detalle');
    $table->unsignedBigInteger('id_requerimiento');
    $table->string('codigo_producto', 20);
    $table->string('codigo_almacen_origen', 10);
    $table->string('codigo_almacen_destino', 10);
    $table->decimal('cantidad_solicitada', 12, 2);
    $table->decimal('cantidad_atendida', 12, 2)->default(0);
    $table->string('lote_preferente', 50)->nullable();
    $table->text('observaciones')->nullable();

    $table->foreign('id_requerimiento')->references('id_requerimiento')->on('requerimientos_materiales')->onDelete('cascade');
    $table->foreign('codigo_producto')->references('codigo')->on('producto');
    $table->foreign('codigo_almacen_origen')->references('codigo_almacen')->on('almacen');
    $table->foreign('codigo_almacen_destino')->references('codigo_almacen')->on('almacen');
});
```

### 4.3 Migración: `despacho_requerimiento_lotes`

```php
Schema::create('despacho_requerimiento_lotes', function (Blueprint $table) {
    $table->bigIncrements('id_despacho_lote');
    $table->unsignedBigInteger('id_detalle');
    $table->unsignedBigInteger('id_requerimiento');
    $table->string('lote', 50);
    $table->decimal('cantidad', 12, 2);
    $table->timestamp('fecha_despacho')->useCurrent();

    $table->foreign('id_detalle')->references('id_detalle')->on('detalle_requerimientos_materiales');
    $table->foreign('id_requerimiento')->references('id_requerimiento')->on('requerimientos_materiales');
});
```

---

## 5. Modelos

### 5.1 `RequerimientoMaterial`

```php
class RequerimientoMaterial extends Model
{
    protected $table = 'requerimientos_materiales';
    protected $primaryKey = 'id_requerimiento';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = ['idop', 'motivo', 'estado', 'usuario_creacion', 'observaciones'];

    public function detalles()
    {
        return $this->hasMany(DetalleRequerimientoMaterial::class, 'id_requerimiento');
    }

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccionGlobal::class, 'idop');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_creacion');
    }

    public function aprobador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_aprobacion');
    }
}
```

### 5.2 `DetalleRequerimientoMaterial`

```php
class DetalleRequerimientoMaterial extends Model
{
    protected $table = 'detalle_requerimientos_materiales';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_requerimiento', 'codigo_producto',
        'codigo_almacen_origen', 'codigo_almacen_destino',
        'cantidad_solicitada', 'cantidad_atendida',
        'lote_preferente', 'observaciones'
    ];

    public function requerimiento()
    {
        return $this->belongsTo(RequerimientoMaterial::class, 'id_requerimiento');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }

    public function almacenOrigen()
    {
        return $this->belongsTo(Almacen::class, 'codigo_almacen_origen', 'codigo_almacen');
    }

    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'codigo_almacen_destino', 'codigo_almacen');
    }
}
```

### 5.3 `DespachoRequerimientoLote`

```php
class DespachoRequerimientoLote extends Model
{
    protected $table = 'despacho_requerimiento_lotes';
    protected $primaryKey = 'id_despacho_lote';
    const CREATED_AT = 'fecha_despacho';
    const UPDATED_AT = null;

    protected $fillable = ['id_detalle', 'id_requerimiento', 'lote', 'cantidad'];

    public function detalle()
    {
        return $this->belongsTo(DetalleRequerimientoMaterial::class, 'id_detalle');
    }

    public function requerimiento()
    {
        return $this->belongsTo(RequerimientoMaterial::class, 'id_requerimiento');
    }
}
```

---

## 6. Controlador: `RequerimientoMaterialController`

### Métodos

| Método | HTTP | Ruta | Lógica |
|--------|------|------|--------|
| `index()` | GET | `requerimientos-materiales` | Lista paginada con filtros (estado, código, fechas, producto). Carga relaciones: detalles, creador. |
| `create()` | GET | `requerimientos-materiales/create` | Formulario con filas dinámicas: producto (Select2), almacén origen, almacén destino, cantidad, OP (opcional). |
| `store(Request)` | POST | `requerimientos-materiales` | Crea REQ + detalles en transacción. Estado = BORRADOR. Genera código REQ-XXXXXX auto-incremental. |
| `show($id)` | GET | `requerimientos-materiales/{id}` | Vista detalle con todas las líneas, botones según estado. |
| `edit($id)` | GET | `requerimientos-materiales/{id}/edit` | Solo si estado = BORRADOR. |
| `update(Request, $id)` | PUT/PATCH | `requerimientos-materiales/{id}` | Solo si BORRADOR. Reemplaza detalles. |
| `enviar($id)` | POST | `.../{id}/enviar` | Valida líneas > 0. BORRADOR -> PENDIENTE. |
| `aprobar($id)` | POST | `.../{id}/aprobar` | Admin. PENDIENTE -> APROBADO. Guarda usuario_aprobacion y fecha_aprobacion. |
| `rechazar(Request, $id)` | POST | `.../{id}/rechazar` | Admin. Requiere observación. PENDIENTE -> RECHAZADO. |
| `anular($id)` | POST | `.../{id}/anular` | Admin. Cambia a ANULADO. |
| `atender($id)` | GET | `.../{id}/atender` | Formulario de despacho: saldo pendiente + stock por lote en origen. |
| `storeAtender(Request, $id)` | POST | `.../{id}/store-atender` | Procesa despacho (ver sección 7). |

### Lógica de `storeAtender()` (core)

```
Por cada lote seleccionado en el formulario:

  1. Validar stock disponible en origen (con lockForUpdate)

  2. SALIDA del almacén origen:
     - KardexService::calcularCostos() -> INSERT kardex (SALIDA)
     - INSERT movimientos_inventario (SALIDA, con lote)
     - UPDATE inventario.origen SET stock_actual -= cantidad

  3. INGRESO al almacén destino (mismo costo unitario):
     - KardexService::calcularCostos() -> INSERT kardex (INGRESO)
     - INSERT movimientos_inventario (INGRESO, con lote)
     - UPDATE inventario.destino SET stock_actual += cantidad
     - INSERT inventario si no existe (destino + producto + lote)

  4. Registrar en despacho_requerimiento_lotes

  5. Actualizar detalle.cantidad_atendida += cantidad

  6. Si todas las líneas completas -> ATENDIDO_TOTAL
     Si no -> ATENDIDO_PARCIAL
```

---

## 7. Vistas

### 7.1 `index.blade.php`
- Filtros: estado, código, fechas, producto
- Tabla: Código, Fecha, OP, Productos, Estado (badge), Acciones
- Paginación

### 7.2 `create.blade.php`
- Filas dinámicas (JS): producto (Select2), almacén origen, almacén destino, cantidad, lote preferente
- Selector opcional de OP
- Campo motivo
- Botones: Guardar Borrador, Enviar

### 7.3 `edit.blade.php`
- Similar a create, precargado
- Solo si BORRADOR

### 7.4 `show.blade.php`
- Cabecera: código, fecha, estado, creador, OP, motivo
- Tabla de líneas: producto, origen -> destino, solicitado, atendido (barra progreso)
- Historial de despachos
- Botones contextuales según estado/rol

### 7.5 `atender.blade.php`
- Por línea con saldo pendiente:
  - Producto, origen -> destino, pendiente
  - Tabla de lotes: lote, fecha_venc, stock, input cantidad
  - Lotes ordenados por fecha_vencimiento ASC
  - Validación JS: suma por línea <= pendiente
- Botón: Confirmar Despacho

---

## 8. Rutas

```php
Route::middleware('role:Administrador,Supervisor,Especialista')
    ->prefix('admin')
    ->name('requerimientos_materiales.')
    ->group(function () {

    Route::get('requerimientos-materiales', [RequerimientoMaterialController::class, 'index'])->name('index');
    Route::get('requerimientos-materiales/create', [RequerimientoMaterialController::class, 'create'])->name('create');
    Route::post('requerimientos-materiales', [RequerimientoMaterialController::class, 'store'])->name('store');
    Route::get('requerimientos-materiales/{id}', [RequerimientoMaterialController::class, 'show'])->name('show');
    Route::get('requerimientos-materiales/{id}/edit', [RequerimientoMaterialController::class, 'edit'])->name('edit');
    Route::put('requerimientos-materiales/{id}', [RequerimientoMaterialController::class, 'update'])->name('update');

    Route::post('requerimientos-materiales/{id}/enviar', [RequerimientoMaterialController::class, 'enviar'])
        ->name('enviar')->middleware('role:Administrador,Supervisor,Especialista');
    Route::post('requerimientos-materiales/{id}/aprobar', [RequerimientoMaterialController::class, 'aprobar'])
        ->name('aprobar')->middleware('role:Administrador');
    Route::post('requerimientos-materiales/{id}/rechazar', [RequerimientoMaterialController::class, 'rechazar'])
        ->name('rechazar')->middleware('role:Administrador');
    Route::post('requerimientos-materiales/{id}/anular', [RequerimientoMaterialController::class, 'anular'])
        ->name('anular')->middleware('role:Administrador');
    Route::get('requerimientos-materiales/{id}/atender', [RequerimientoMaterialController::class, 'atender'])
        ->name('atender')->middleware('role:Administrador,Supervisor');
    Route::post('requerimientos-materiales/{id}/store-atender', [RequerimientoMaterialController::class, 'storeAtender'])
        ->name('store_atender')->middleware('role:Administrador,Supervisor');
});
```

---

## 9. Sidebar (Navegación)

En `resources/views/layouts/app.blade.php`, dentro del grupo **Producción**:

```html
<a href="{{ route('requerimientos_materiales.index') }}"
   class="sidebar-link {{ request()->routeIs('requerimientos_materiales*') ? 'active' : '' }}">
    <i class="fas fa-clipboard-list"></i>
    <span>Requerimientos</span>
</a>
```

Antes del link de "Órdenes de Producción".

---

## 10. Permisos (Módulos)

Registrar en `modulos`:

| Slug | Nombre | Grupo |
|------|--------|-------|
| `requerimientos_materiales.index` | Requerimientos - Listado | requerimientos_materiales |
| `requerimientos_materiales.create` | Requerimientos - Crear | requerimientos_materiales |
| `requerimientos_materiales.edit` | Requerimientos - Editar | requerimientos_materiales |
| `requerimientos_materiales.show` | Requerimientos - Ver | requerimientos_materiales |
| `requerimientos_materiales.enviar` | Requerimientos - Enviar | requerimientos_materiales |
| `requerimientos_materiales.aprobar` | Requerimientos - Aprobar | requerimientos_materiales |
| `requerimientos_materiales.atender` | Requerimientos - Atender | requerimientos_materiales |

Asignación por `rol_modulo`:

| Módulo | Admin | Supervisor | Especialista |
|--------|-------|------------|--------------|
| index, create, edit, show, enviar | ✓ | ✓ | ✓ |
| aprobar, rechazar, anular | ✓ | ✗ | ✗ |
| atender, store_atender | ✓ | ✓ | ✗ |

---

## 11. Orden de Implementación

| Paso | Archivos | Descripción |
|------|----------|-------------|
| 1 | Migraciones (3) | `requerimientos_materiales`, `detalle_requerimientos_materiales`, `despacho_requerimiento_lotes` |
| 2 | Modelos (3) | `RequerimientoMaterial`, `DetalleRequerimientoMaterial`, `DespachoRequerimientoLote` |
| 3 | Controller | `RequerimientoMaterialController` con todos los métodos |
| 4 | Vistas (5) | index, create, edit, show, atender |
| 5 | Rutas | Registrar en web.php |
| 6 | Sidebar | Agregar link en Producción |
| 7 | Permisos | Seed de módulos + rol_modulo |
| 8 | Pruebas | Flujo completo: crear -> enviar -> aprobar -> atender |

---

## 12. Notas Técnicas

- **Código auto-generado**: Formato REQ-XXXXXX usando `MAX(id_requerimiento) + 1` formateado.
- **Select2**: Reutilizar configuración existente de `compras/create.blade.php`.
- **Stock lock**: Usar `lockForUpdate()` en consultas de inventario dentro de la transacción.
- **Costo de ingreso**: El material transferido mantiene el mismo costo unitario del origen. KardexService recalcula el promedio ponderado en el destino.
- **FIFO visual**: Lotes ordenados por `fecha_vencimiento ASC`. Valores sugeridos = pendiente si hay stock.
- **HU-03 (Trazabilidad)**: Se beneficia directamente porque lote e idop quedan registrados en movimientos_inventario.
