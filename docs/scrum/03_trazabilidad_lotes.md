# Historia de Usuario: Trazabilidad de Lotes

**Estado actual:** El sistema ya almacena el lote de ingreso en las compras (y `inventario`/`kardex`), así como el lote al realizar consumos en producción mediante FIFO. Los productos terminados también pueden generar su propio lote de salida.
**Lo que se busca:** Un módulo o reporte unificado que permita entrelazar estos datos (Compras → Inventario → Consumo en OP → Producto Terminado) para responder rápidamente a auditorías o reclamos de calidad.

## Historia de Usuario
**Como** Supervisor de Calidad / Gerente de Producción
**Quiero** consultar el historial y trayectoria de cualquier lote (ya sea de materia prima o de producto terminado)
**Para** identificar exactamente el origen de los insumos, en qué órdenes de producción se utilizaron y qué productos finales se generaron, garantizando el cumplimiento de normas de calidad y facilitando aislar problemas.

## Criterios de Aceptación
- **Búsqueda Bidireccional:** El usuario dispondrá de un buscador central donde ingresará un número de lote. El sistema detectará si el lote pertenece a un insumo (materia prima) o a un producto fabricado.
- **Trazabilidad Hacia Adelante (De Insumo a Producto Final):** Si se busca el lote de una materia prima, el sistema mostrará:
  1. Fecha de compra, proveedor y documento de recepción.
  2. Historial de despachos/consumos (a qué Órdenes de Producción se destinó y qué cantidad).
  3. Los productos finales obtenidos (y sus respectivos lotes de salida) derivados de esas OPs.
- **Trazabilidad Hacia Atrás (De Producto Final a Insumos):** Si se busca el lote de un producto terminado, el sistema mostrará:
  1. La Orden de Producción (OP) que lo generó y su fecha de término.
  2. El listado completo de los insumos (Materia Prima, Empaques) utilizados en esa OP.
  3. Los números de lote y proveedores exactos de esos insumos consumidos.
- **Interfaz Visual:** Los resultados deben mostrarse de manera escalonada o en formato de línea de tiempo/árbol para que sea fácil seguir el flujo del material.
- **Exportación:** Posibilidad de descargar el reporte de trazabilidad en formato PDF para adjuntarlo a expedientes de auditoría o respuestas a reclamos de clientes.

## Notas Técnicas
- **Consultas (Queries):** Se requiere construir consultas SQL o Eloquent avanzadas (probablemente usando subconsultas o CTEs) que vinculen: `compras` -> `kardex` -> `movimientos_inventario` -> `componentes_orden_produccion_global` -> `orden_produccion_global` -> Ingreso a `inventario` (producto terminado).
- **Controlador:** Crear `TrazabilidadController` con métodos que manejen la búsqueda recursiva.
- **Rendimiento:** Asegurar que las columnas `lote` estén indexadas en las tablas clave (`inventario`, `kardex`, `movimientos_inventario`, `componentes_orden_produccion_global`) para que las búsquedas sean rápidas, ya que las tablas de movimientos crecen exponencialmente.
