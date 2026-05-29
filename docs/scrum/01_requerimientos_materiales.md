# Historia de Usuario: Requerimientos de Materiales

**Estado actual:** No existe un módulo de "requerimiento". Los materiales se consumen directamente durante la ejecución de producción. El esquema de inventario ya soporta lote y fecha_vencimiento, y el consumo FIFO por lote ya está implementado en OrdenProcesoController@storeComponentes.
**Lo que falta:** Una capa de solicitud formal de materiales antes del consumo, que permita trazabilidad total desde el lote recibido → inventario → producción.

## Historia de Usuario
**Como** Supervisor de Producción / Almacenero
**Quiero** generar y gestionar requerimientos de materiales con asignación específica de lotes para cada orden de producción
**Para** asegurar la trazabilidad completa desde la compra hasta el producto terminado, controlando exactamente qué lotes se consumen en cada proceso y evitando consumos directos sin autorización

## Criterios de Aceptación
- **CRUD de Requerimientos:** Crear, editar, visualizar y anular requerimientos de materiales. Cada requerimiento debe estar vinculado a una orden_produccion (opcional) y tener estado (BORRADOR, PENDIENTE, APROBADO, RECHAZADO, ATENDIDO_PARCIAL, ATENDIDO_TOTAL, ANULADO)
- **Líneas de requerimiento:** Cada línea especifica: producto, cantidad solicitada, almacén de origen, y (opcionalmente) lote preferente
- **Flujo de aprobación:** El requerimiento pasa a PENDIENTE y solo un usuario con rol autorizado puede aprobarlo/rechazarlo. Al aprobar, cambia a APROBADO
- **Despacho con trazabilidad por lotes:** Al atender un requerimiento aprobado, el sistema muestra el stock disponible por lote (fecha_vencimiento, cantidad) y permite asignar cantidades específicas de cada lote. Los lotes más próximos a vencer se sugieren primero (FIFO).
- **Consumo automático de inventario:** Al confirmar el despacho, se generan los movimientos de SALIDA en movimientos_inventario y las entradas en kardex por cada combinación (producto + almacén + lote), reutilizando la lógica existente de consumo FIFO
- **Vinculación a producción:** Si el requerimiento está asociado a una orden_produccion, el sistema registra los componentes en componentes_orden_produccion_global automáticamente
- **Reporte de trazabilidad:** Permitir consultar: "¿Dado un lote de materia prima, en qué órdenes de producción fue utilizado?" y "¿Dada una orden de producción, qué lotes de materia prima se consumieron?"

## Notas Técnicas
- Crear modelo `RequerimientoMaterial` (tabla `requerimientos_materiales`) y `DetalleRequerimientoMaterial` (tabla `detalle_requerimientos_materiales`)
- Crear `RequerimientoMaterialController` con validación de stock por lote
- Aprovechar el esquema existente: `inventario.lote`, `movimientos_inventario.lote`, `kardex.lote`
- Reutilizar la lógica FIFO de `OrdenProcesoController@storeComponentes` (líneas 608-674) para el despacho por lotes
- La UI debe mostrar un selector de lotes similar al flujo de recepción de compras pero en modo "salida"
- Los estados del requerimiento pueden manejarse como string simple o ENUM en BD
